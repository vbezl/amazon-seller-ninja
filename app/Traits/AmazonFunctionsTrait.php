<?php namespace App\Traits;

use \Illuminate\Http\Request;

use Peron\AmazonMws\AmazonInventoryList;
use Peron\AmazonMws\AmazonProductInfo;
use Peron\AmazonMws\AmazonProductList;
use Peron\AmazonMws\AmazonOrderList;
use Peron\AmazonMws\AmazonOrderItemList;
use Peron\AmazonMws\AmazonReportRequest;
use Peron\AmazonMws\AmazonReportRequestList;
use Peron\AmazonMws\AmazonReportList;
use Peron\AmazonMws\AmazonReport;

use App\Models\Product;
use App\Models\Category;
use App\Models\Rank;
use App\Models\Price;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Report;
use App\Models\Template;
use App\Models\Email;
use App\Models\Unsubscriber;

use \Carbon\Carbon;
use Log;

use \SimpleXMLElement;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

trait AmazonFunctionsTrait {

    protected function syncProducts(Request $request)
    {

        $obj = new AmazonInventoryList("store1"); //store name matches the array key in the config file
        $obj->setUseToken(); //tells the object to automatically use tokens right away
        $obj->setStartTime("-720 hours");
        if($obj->fetchInventoryList() !== false){
            $inventory = collect($obj->getSupply());
            $asins = $inventory->pluck('ASIN');

            $obj = new AmazonProductList('store1');
            $obj->setIdType('ASIN');
            $obj->setProductIds($asins->toArray());
            $obj->fetchProductList();
            $products = $obj->getProduct();

            $obj = new AmazonProductInfo('store1');
            $obj->setASINs($asins->toArray());
            $obj->fetchMyPrice();
            $products_prices = $obj->getProduct();
            foreach ($products_prices as $k => $v) {
                unset ($products_prices[$k]);
                $d = $v->getData();
                $products_prices[$d['Identifiers']['MarketplaceASIN']['ASIN']] = $v;
            }

            foreach($products as $product){
                $data = $product->getData();
                $newproduct = Product::firstOrNew([
                    'asin'      => $data['Identifiers']['MarketplaceASIN']['ASIN']
                ]);
                $newproduct->title      = $data['AttributeSets'][0]['Title'];
                $newproduct->image_url  = $data['AttributeSets'][0]['SmallImage']['URL'];
                $newproduct->save();

                // attaching product to user
                $newproduct->users()->syncWithoutDetaching([$request->user()->id => ['in_user_seller_account' => true, 'track' => false]]);

                // adding price stats
                if($products_prices[$newproduct->asin]){
                    $d = $products_prices[$newproduct->asin]->getData();

                    Price::create([
                        'product_id' => $newproduct->id,
                        'regular_price' => $d['Offers'][0]['RegularPrice']['Amount'],
                        'buying_price' => $d['Offers'][0]['BuyingPrice']['LandedPrice']['Amount']
                    ]);
                }

                // attaching product to categories
                $onetime = false;
                foreach($data['SalesRankings'] as $salesrank){
                    $track = false;
                    $amazon_category_id = $salesrank['SalesRank']['ProductCategoryId'];
                    $rank = $salesrank['SalesRank']['Rank'];

                    // creating category if doesn't exist
                    $category = Category::firstOrCreate(['amazon_category_id' => $amazon_category_id]);

                    // tracking first non-numeric category
                    if(!$track && !is_numeric($amazon_category_id) && !$onetime){
                        $track = true;
                        $onetime = true;
                    }

                    $newproduct->categories()->syncWithoutDetaching([$category->id => ['track' => $track]]);

                    if($track){
                        // adding rank stats
                        Rank::create(['product_id' => $newproduct->id, 'category_id' => $category->id, 'rank' => $rank]);
                    }

                }

            }

            echo "synced products, categories, ranks, prices";

        } else {
            echo 'There was a problem with the Amazon library.';
        }

    }

    protected function syncPrices($products) {

        $db_products = $products->groupBy('asin')->toArray();
        $asins = array_keys($db_products);

        $obj = new AmazonProductInfo('store1');
        $obj->setASINs($asins);
        $obj->fetchMyPrice();
        $amazon_products = $obj->getProduct();

        foreach($amazon_products as $product){
            $data = $product->getData();
            $asin = $data['Identifiers']['MarketplaceASIN']['ASIN'];

            Price::create([
                'product_id' => $db_products[$asin][0]['id'],
                'regular_price' => $data['Offers'][0]['RegularPrice']['Amount'],
                'buying_price' => $data['Offers'][0]['BuyingPrice']['LandedPrice']['Amount']
            ]);

        }

    }

    protected function syncRanks($products) {

        $db_products = $products->groupBy('asin')->toArray();
        $asins = array_keys($db_products);

        $obj = new AmazonProductList('store1');
        $obj->setIdType('ASIN');
        $obj->setProductIds($asins);
        $obj->fetchProductList();
        $amazon_products = $obj->getProduct();

        foreach($amazon_products as $product){
            $data = $product->getData();
            $asin = $data['Identifiers']['MarketplaceASIN']['ASIN'];

            $track_categories = $db_products[$asin][0]['categories'];
            foreach ($track_categories as $i => $c) {
                unset($track_categories[$i]);
                $track_categories[$c['amazon_category_id']] = $c['id'];
            }
            $track_categories_keys = array_keys($track_categories);

            foreach($data['SalesRankings'] as $salesrank){

                $amazon_category_id = $salesrank['SalesRank']['ProductCategoryId'];
                $rank = $salesrank['SalesRank']['Rank'];

                if(in_array($amazon_category_id, $track_categories_keys)) {

                    // adding rank stats
                    Rank::create(['product_id' => $db_products[$asin][0]['id'], 'category_id' => $track_categories[$amazon_category_id], 'rank' => $rank]);

                }

            }

        }

    }


    protected function syncOrders() {

        // TODO: it should not depend on request user (as it will be called from CRON!) - it should work for all users

        $user = request()->user();
        $last_update_order = $user->orders()->orderBy('last_update_date', 'desc')->first();
        $last_update_date = $last_update_order ? $last_update_order->last_update_date : Carbon::now()->subDays(1);

        $obj = new AmazonOrderList('store1');
        $obj->setUseToken();
        $obj->setLimits('Modified', $last_update_date->toIso8601String());
        $obj->fetchOrders();

        $amazon_orders = $obj->getList();

        foreach ($amazon_orders as $amazon_order) {
            $data = $amazon_order->getData();

            $customer = false;
            if(!empty($data['BuyerName'])){
                $full_name = $data['BuyerName'];
                $parts = explode(" ", $full_name);
                $last_name = array_pop($parts);
                $first_name = !empty($parts) ? implode(" ", $parts) : $last_name;

                $customer = Customer::updateOrCreate(
                    ['email' => $data['BuyerEmail']],
                    ['full_name' => $full_name, 'first_name' => $first_name]
                );
            }

            $order_data = [
                'amazon_order_id' => $data['AmazonOrderId'],
            ];
            $order = Order::updateOrCreate(
                ['amazon_order_id' => $data['AmazonOrderId']],
                [
                    'user_id' => $user->id,
                    'customer_id' => $customer ? $customer->id : 0,
                    'seller_order_id' => $data['SellerOrderId'],
                    'fulfillment_channel' => $data['FulfillmentChannel'],
                    'order_status' => $data['OrderStatus'],
                    'number_of_items_shipped' => $data['NumberOfItemsShipped'],
                    'number_of_items_unshipped' => $data['NumberOfItemsUnshipped'],
                    'order_total_amount' => !empty($data['OrderTotal']) ? $data['OrderTotal']['Amount'] : 0,
                    'ship_country_code' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['CountryCode'] : '',
                    'ship_state' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['StateOrRegion'] : '',
                    'ship_city' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['City'] : '',
                    'ship_zip' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['PostalCode'] : '',
                    'ship_full_name' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['Name'] : '',
                    'ship_address1' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['AddressLine1'] : '',
                    'ship_address2' => !empty($data['ShippingAddress']) ? $data['ShippingAddress']['AddressLine2'] : '',
                    'purchase_date' => new Carbon($data['PurchaseDate']),
                    'last_update_date' => new Carbon($data['LastUpdateDate'])
                ]
            );

            // check if we already have items in this order and quantity is equal - then don't call amazon itemlist!
            $items_count = Item::selectRaw('SUM(quantity_ordered) as quantity_ordered')
                ->whereOrderId($order->id)->groupBy('order_id')->first();
            if(!$items_count || ($items_count->quantity_ordered != $data['NumberOfItemsShipped'] + $data['NumberOfItemsUnshipped'])){

                $obj = new AmazonOrderItemList('store1');
                $obj->setUseToken();
                $obj->setOrderId($data['AmazonOrderId']);
                $obj->fetchItems();

                $amazon_order_items = $obj->getItems();
                foreach ($amazon_order_items as $amazon_order_item) {

                    $product = Product::whereAsin($amazon_order_item['ASIN'])->first();
                    if(!$product){
                        $product = Product::create([
                            'asin' => $amazon_order_item['ASIN'],
                            'title' => $amazon_order_item['Title']
                        ]);
                    }

                    Item::updateOrCreate(
                        ['amazon_order_item_id' => $amazon_order_item['OrderItemId']],
                        [
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity_ordered' => $amazon_order_item['QuantityOrdered'],
                            'quantity_shipped' => $amazon_order_item['QuantityShipped'],
                            'price' => !empty($amazon_order_item['ItemPrice']) ? $amazon_order_item['ItemPrice']['Amount'] : 0,
                            'discount' => !empty($amazon_order_item['ItemPrice']) ? $amazon_order_item['PromotionDiscount']['Amount'] : 0,
                            'tax' => !empty($amazon_order_item['ItemPrice']) ? $amazon_order_item['ItemTax']['Amount'] : 0
                        ]
                    );


                }

            }

        }


    }


    // request reports for all users
    protected function requestReports($report_type, $start_date, $end_date)
    {

        $obj = new AmazonReportRequest('store1');
        $obj->setReportType($report_type);
        $obj->setTimeLimits($start_date, $end_date);
        $obj->requestReport();

        $request_id = $obj->getReportRequestId();
        $processing_status = $obj->getStatus();

        Report::create([
            'type' => $report_type,
            'start_date' => new Carbon($start_date),
            'end_date' => new Carbon($end_date),
            'request_id' => $request_id,
            'processing_status' => $processing_status
        ]);

        Log::info('report created: request_id='.$request_id);

    }

    // check reports for all users
    protected function checkReports()
    {

        //Report::where('id', 5)->update(['processing_status' => 'DDD2']);exit;
        //$order = Order::find(58);
        //$order->ship_address1 = '5422 Vista Dr.8';
        //$order->save();
        //Order::where('id', 58)->update(['ship_address1' => '5422 Vista Dr.7']);

        $request_ids = Report::where('status', 'requested')
            ->pluck('request_id')
            ->toArray();

        if (!empty($request_ids)) {

            Log::info('requesting statuses for reports: request_ids='.implode(', ', $request_ids));

            $obj = new AmazonReportRequestList('store1');
            $obj->setRequestIds($request_ids);
            $obj->fetchRequestList();
            $reports = $obj->getList();

            foreach($reports as $report){

                $db_report = Report::where('request_id', $report['ReportRequestId'])->first();
                $db_report->processing_status = $report['ReportProcessingStatus'];
                $db_report->generated_report_id = $report['GeneratedReportId'];
                $db_report->save();

            }
        } else {
            Log::info('requesting statuses for reports: no reports found - nothing to request from Amazon');
        }


    }

    // download reports for all users
    protected function downloadReports()
    {

        $report_ids = Report::where('status', 'ready')
            ->pluck('generated_report_id')
            ->toArray();

        if(!empty($report_ids)){

            Log::info('downloading reports: generated_report_ids='.implode(', ', $report_ids));

            foreach($report_ids as $report_id) {

                $obj = new AmazonReport('store1');
                $obj->setReportId($report_id);
                $report = $obj->fetchReport();

                $db_report = Report::where('generated_report_id', $report_id)->first();
                $db_report->body = $report;
                $db_report->save();

            }
        } else {
            Log::info('downloading reports: no reports found - nothing to request from Amazon');
        }

    }

    protected function processReports()
    {

        $reports = Report::where('status', 'downloaded')->get();
        if(!empty($reports)) {

            Log::info('processing reports: '.$reports->count(). ' found');
            foreach ($reports as $report) {
                $body_lines = explode("\n", $report->body);

                Log::info('report body lines: report_id='.$report->id. ' lines: '. sizeof($body_lines));

                if(sizeof($body_lines) > 1){
                    unset($body_lines[0]);
                    foreach ($body_lines as $line) {
                        $line_data = explode("\t", $line);

                        if($report->type == '_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_'){
                            $this->processGAFSD($line_data);
                        }
                    }

                }

                $report->status = 'processed';
                $report->save();
            }

        }

    }


    protected function processGAFSD ($data)
    {
        /*
         * _GET_AMAZON_FULFILLED_SHIPMENTS_DATA_

amazon-order-id
merchant-order-id
shipment-id
shipment-item-id
amazon-order-item-id
merchant-order-item-id
purchase-date
payments-date
shipment-date
reporting-date
buyer-email
buyer-name
buyer-phone-number
sku
product-name
quantity-shipped
currency
item-price
item-tax
shipping-price
shipping-tax
gift-wrap-price
gift-wrap-tax
ship-service-level
recipient-name
ship-address-1
ship-address-2
ship-address-3
ship-city
ship-state
ship-postal-code
ship-country
ship-phone-number
bill-address-1
bill-address-2
bill-address-3
bill-city
bill-state
bill-postal-code
bill-country
item-promotion-discount
ship-promotion-discount
carrier
tracking-number
estimated-arrival-date
fulfillment-center-id
fulfillment-channel
sales-channel
         */

        $order = Order::where('amazon_order_id', $data[0])->first();
        if($order) {
            Log::info('processing amazon order id: '.$data[0]);

            $order->ship_carrier = $data[42];
            $order->ship_tracking_number = $data[43];
            $order->ship_estimated_arrival_date = new Carbon($data[44]);
            if(!empty($order->ship_tracking_number) && empty($order->ship_status)) {
                $order->ship_status = 'start';
            }
            $order->save();

        } else {
            Log::info('NOT FOUND amazon order id not found in our DB: '.$data[0]);
        }

    }


    protected function trackShipments()
    {

        $orders = Order::where('ship_status', '!=','delivered')
            ->where(function ($query) {
                $query->whereNull('ship_last_tracked_at')
                    ->orWhere('ship_last_tracked_at', '<', Carbon::now()->subHour());
            })
            ->get();

        $tracking_numbers = $orders->groupBy('ship_carrier')->toArray();//->pluck('ship_carrier', 'ship_tracking_number')->toArray();

        $statuses = [];
        if(!empty($tracking_numbers['USPS'])){
            $statuses += $this->trackUSPS($tracking_numbers['USPS']);
            unset($tracking_numbers['USPS']);
        }

        if(!empty($tracking_numbers['UPS'])){
            $statuses += $this->trackUPS($tracking_numbers['UPS']);
            unset($tracking_numbers['UPS']);
        }

        if(!empty($tracking_numbers['FEDEX'])){
            $statuses += $this->trackFEDEX($tracking_numbers['FEDEX']);
            unset($tracking_numbers['FEDEX']);
        }

        // left only untrackable carriers like DYNAMEX, AMZN_US, ONTRAC etc.
        // looking at estimated arrival time - if more than 1 day left after ETA - we assume it was delivered
        foreach($tracking_numbers as $carrier => $numbers){
            Log::info("analyzing ship_estimated_arrival_date for carrier: $carrier - ".sizeof($numbers)." numbers");

            foreach($numbers as $t){

                $ship_estimated_arrival_date = new Carbon($t['ship_estimated_arrival_date']);
                $last_update_date = new Carbon($t['last_update_date']);
                // if more than 1 day left after ETA - assume delivered
                // or if ETA null and more than 7 days left after last_update_date - assume delivered
                if($ship_estimated_arrival_date < Carbon::now()->subDay()
                    || $last_update_date < Carbon::now()->subDays(7)){

                    Log::info("assume delivered: $carrier {$t['ship_tracking_number']}, eta={$t['ship_estimated_arrival_date']}, last_update={$t['last_update_date']}");
                    $statuses[$t['ship_tracking_number']] = 'delivered';
                }

                //

            }

        }


        foreach($orders as $order){
            if(!empty($statuses[$order->ship_tracking_number])) {
                $order->ship_status = $statuses[$order->ship_tracking_number];
                if($statuses == 'delivered') {
                    $order->ship_delivered_date = Carbon::now();
                }
                $order->save();
            }
        }

    }

    // returns only DELIVERED and OUT FOR DELIVERY shipments
    protected function trackUSPS($tracking_numbers)
    {

        $url = "http://production.shippingapis.com/shippingAPI.dll";
        $service = "TrackV2";

        $return = [];
        // can request up to 10 tracking numbers at a time
        $iterations = ceil(sizeof($tracking_numbers) / 10);
        for($i=0; $i<$iterations; $i++){
            $offset = $i*10;
            $current_numbers = array_slice($tracking_numbers, $offset, 10);

            $tracking_str = "";
            foreach($current_numbers as $t) {
                $tracking_str .= "<TrackID ID=\"".$t['ship_tracking_number']."\"></TrackID>";

                // writing ship_last_tracked_at
                $order = Order::find($t['id']);
                $order->ship_last_tracked_at = Carbon::now();
                $order->save();
            }

            $xml = rawurlencode("<TrackRequest USERID='881AMZAP0102'>
            $tracking_str
            </TrackRequest>");

            $request = $url . "?API=" . $service . "&XML=" . $xml;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            $response = new SimpleXMLElement($result);

            foreach($response->TrackInfo as $t){
                Log::info("shipment status: USPS {$t['ID']} = {$t->TrackSummary}");
                $s = strpos($t->TrackSummary, 'Out for Delivery') !== false ? 'out_for_delivery' : (strpos($t->TrackSummary, 'was delivered') !== false ? 'delivered' : '');
                Log::info("shipment status calculated: USPS {$t['ID']} = $s");
                if(!empty($s)){
                    $return[(string)$t['ID']] = $s;
                } else {
                    Log::info("ignoring this shipment");
                }
            }

        }

        return $return;

    }

    // returns only DELIVERED and OUT FOR DELIVERY shipments
    protected function trackUPS($tracking_numbers)
    {

        $return = [];
        // can request up to 25 tracking numbers at a time
        $iterations = ceil(sizeof($tracking_numbers) / 25);
        for($i=0; $i<$iterations; $i++){
            $offset = $i*25;
            $current_numbers = array_slice($tracking_numbers, $offset, 25);

            $tracking_str = '';
            $ind = 1;
            foreach($current_numbers as $t){
                $tracking_str .= "InquiryNumber$ind=".$t['ship_tracking_number']."&";

                // writing ship_last_tracked_at
                $order = Order::find($t['id']);
                $order->ship_last_tracked_at = Carbon::now();
                $order->save();

                $ind++;
            }

            $url = "https://wwwapps.ups.com/WebTracking/processRequest?$tracking_str"."track.x=0&track.y=0";

            $client = new Client();
            $res = $client->request('GET', $url);
            $html = (string) $res->getBody();

            $crawler = new Crawler($html);
            for($ind=1; $ind <= sizeof($current_numbers); $ind++){
                if(sizeof($current_numbers) > 1){
                    $filter = trim($crawler->filter("#tt_spStatus$ind")->first()->text());
                } else {
                    $filter = trim($crawler->filter('#tt_spStatus')->first()->text());
                }

                $current_tracking_number = $current_numbers[$ind-1]['ship_tracking_number'];

                if(!empty($filter)){

                    Log::info("shipment status: UPS $current_tracking_number = $filter");
                    $s = strpos($filter, 'for Delivery Today') !== false ? 'out_for_delivery' : (strpos($filter, 'Delivered') !== false ? 'delivered' : '');
                    Log::info("shipment status calculated: UPS $current_tracking_number = $s");
                    if(!empty($s)){
                        $return[$current_tracking_number] = $s;
                    } else {
                        Log::info("ignoring this shipment");
                    }

                } else {
                    Log::error("NOT FOUND: #tt_spStatus$ind in $url");
                    Log::info("HTML: $html");
                }

            }

        }

        return $return;

    }

    // returns only DELIVERED and OUT FOR DELIVERY shipments
    protected function trackFEDEX($tracking_numbers)
    {

        $url = "http://www.fedex.com/trackingCal/track";

        $return = [];
        // can request up to 30 tracking numbers at a time
        $iterations = ceil(sizeof($tracking_numbers) / 30);
        for($i=0; $i<$iterations; $i++){
            $offset = $i*30;
            $current_numbers = array_slice($tracking_numbers, $offset, 30);

            $tracking_arr = [];
            foreach($current_numbers as $t) {
                $tracking_arr[] = [
                    'trackNumberInfo' => [
                        'trackingNumber' => $t['ship_tracking_number'],
                        'trackingQualifier' => '',
                        'trackingCarrier' => ''
                    ]
                ];

                // writing ship_last_tracked_at
                $order = Order::find($t['id']);
                $order->ship_last_tracked_at = Carbon::now();
                $order->save();
            }

            $data_field = array(
                'TrackPackagesRequest' => array(
                    'appType' => 'WTRK',
                    'uniqueKey' => '',
                    'processingParameters' => (Object)array(), // Will be stringified as {}
                    'trackingInfoList' => $tracking_arr
                )
            );

            $data = array(
                "action" => "trackpackages",
                'format' => 'json',
                'locale' => 'en_US',
                'version' => '1',
                "data" => json_encode($data_field) // Insert the String
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            // http_build_query will convert your parameters to param1=val1&param2=val2...
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            $result_arr = json_decode($result, true);
            if($result_arr['TrackPackagesResponse']['successful']){

                if(!empty($result_arr['TrackPackagesResponse']['packageList'])){
                    foreach($result_arr['TrackPackagesResponse']['packageList'] as $t){
                        Log::info("shipment status: FEDEX {$t['trackingNbr']} = {$t['keyStatus']}, CD = {$t['keyStatusCD']}");
                        $s = $t['keyStatusCD'] == 'OD' ? 'out_for_delivery' : ($t['keyStatusCD'] == 'DL' ? 'delivered' : '');
                        Log::info("shipment status calculated: FEDEX {$t['trackingNbr']} = $s");
                        if(!empty($s)){
                            $return[(string)$t['trackingNbr']] = $s;
                        } else {
                            Log::info("ignoring this shipment");
                        }
                    }
                }

            } else {

                Log::error("FEDEX REQUEST ERROR: $result");

            }

        }

        return $return;

    }



    protected function scheduleEmails(Order $order, $event)
    {

        // get product for the order
        $item = $order->items()->first();
        $product = $item->product()->first();
        $user = $order->user()->first();
        $customer = $order->customer()->first();

        // check if customer email is in black list of unsubscribers
        if ($user->unsubscribers()->where('email', '=', $customer->email)->exists()) {

            Log::info("Customer is in black list of unsubscribers: order = {$order->amazon_order_id}, email = {$customer->email}");
            return false; // customer email is in black list

        }

        $data = [
            'order' => $order,
            'product' => $product,
            'user' => $user,
            'item' => $item,
            'customer' => $customer
        ];

        // get templates for the product (or null for all products) and this event
        $templates = Template::where('user_id', $user->id)
            ->where('event', $event)
            ->where(function ($query) use ($product) {
            $query->where('product_id', $product->id)
                ->orWhereNull('product_id');
        })->get();

        // parse template, set variables
        foreach($templates as $template){
            $subject = $this->parseText($template->subject, $data);
            $body = $this->parseText($template->body, $data);

            $email_from = $user->amazon_email_from;
            $email_to = ($template->status == 'test') ? $user->email : $customer->email;

            $scheduled_at = Carbon::now()->addMinutes($template->event_delay_minutes);

            // schedule email
            Email::create([
                'template_id' => $template->id,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'email_from' => $email_from,
                'email_to' => $email_to,
                'subject' => $subject,
                'body' => $body,
                'status' => 'scheduled',
                'scheduled_at' => $scheduled_at
            ]);

        }

    }

    protected function parseText($string, $data)
    {

        if(preg_match_all("/(\[\[[^\]\]]*\]\])/", $string, $variables)){

            foreach($variables[0] as $var){
                $var = str_replace(['[[',']]'], '', $var);
                $anchor = '';

                if(strpos($var, 'link') !== false){
                    $arr = explode(':', $var);
                    $var = $arr[0];
                    unset($arr[0]);
                    $anchor = implode(':', $arr);
                }

                switch ($var) {
                    case 'buyer-name':
                        $values[] = $data['customer']->full_name;
                        break;
                    case 'first-name':
                        $values[] = $data['customer']->first_name;
                        break;
                    case 'feedback-link':
                        $values[] = empty($anchor) ? ("https://www.amazon.com/gp/feedback/leave-customer-feedback.html/?order=".$data['order']->amazon_order_id."&pageSize=1")
                                        : ("<a href=\"https://www.amazon.com/gp/feedback/leave-customer-feedback.html/?order=".$data['order']->amazon_order_id."&pageSize=1\">$anchor</a>");
                        break;
                    case 'contact-link':
                        $values[] = empty($anchor) ? ("https://www.amazon.com/gp/help/contact/contact.html?marketplaceID=ATVPDKIKX0DER&orderID=".$data['order']->amazon_order_id."&sellerID=".$data['user']->amazon_seller_id)
                            : ("<a href=\"https://www.amazon.com/gp/help/contact/contact.html?marketplaceID=ATVPDKIKX0DER&orderID=".$data['order']->amazon_order_id."&sellerID=".$data['user']->amazon_seller_id."\">$anchor</a>");
                        break;
                    case 'product-link':
                        $values[] = empty($anchor) ? ("http://www.amazon.com/dp/".$data['product']->asin)
                            : ("<a href=\"http://www.amazon.com/dp/".$data['product']->asin."\">$anchor</a>");
                        break;
                    default:
                        $values[] = '';
                }


            }

            $string = str_replace($variables[0], $values, $string);

        }

        return $string;


    }

    protected function scheduleTestEmail(Template $template)
    {

        $order = new Order();
        $order->amazon_order_id = 'TEST-ORDER-ID';

        $item = new Item();

        $product = new Product();
        $product->asin = 'TEST-ASIN';

        $user = request()->user();

        $customer = new Customer();
        $customer->full_name = "Test Buyer Full Name";
        $customer->first_name = "Test Buyer First Name";

        $data = [
            'order' => $order,
            'product' => $product,
            'user' => $user,
            'item' => $item,
            'customer' => $customer
        ];

        $subject = $this->parseText($template->subject, $data);
        $body = $this->parseText($template->body, $data);

        $email_from = $user->email;
        $email_to = $user->email;

        $scheduled_at = Carbon::now();

        // schedule email
        $email = Email::create([
            'template_id' => $template->id,
            'order_id' => 0, // means it is test email
            'user_id' => $user->id,
            'product_id' => 0,
            'email_from' => $email_from,
            'email_to' => $email_to,
            'subject' => $subject,
            'body' => $body,
            'status' => 'scheduled',
            'scheduled_at' => $scheduled_at
        ]);

        $email->send();

    }

    protected function sendScheduledEmails()
    {

        Log::info("Sending emails.");

        $emails = Email::whereStatus('scheduled')
            ->where('scheduled_at', '<', Carbon::now())
            ->get();

        foreach($emails as $email){
            $email->send();
        }

    }

}