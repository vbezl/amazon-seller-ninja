<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;
use App\Traits\AmazonFunctionsTrait;

use App\Models\Product;
use App\Models\Order;
use App\Models\Template;
use App\Models\Email;
use App\Models\Marketplace;

use \Carbon\Carbon;
use Validator;

// for parsing feedbacks info:
//use Illuminate\Support\Facades\Storage;
//use Symfony\Component\DomCrawler\Crawler;
//use App\Models\Feedback;
//use Log;


class ConnectController extends Controller
{
    use AmazonFunctionsTrait;

    /**
     * Show the connect form
     *
     */
    public function showConnect(Request $request)
    {

// testing cron job! (see Kernel.php for current cron)
//        $products = Product::with(['categories' => function($query){
//            $query->wherePivot('track', 1);
//        }])->get();
//        $this->syncPricesRanks($products);

        // testing cron job for sync orders
//        $this->syncOrders();

        // testing cron job for requests reports
//        $end_date = Carbon::now();
//        $start_date = Carbon::now()->subDays(7);
//        $this->requestReports('_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_', $start_date->toIso8601String(), $end_date->toIso8601String());

        //testing cron job to checking reports statuses
//        $this->checkReports();

        // testing cron job to download all reports
//        $this->downloadReports();

        // testing cron job to process all reports
//        $this->processReports();

        // testing cron job to track all shipments
//        $this->trackShipments();

        // testing scheduling emails
//        $order = Order::find(122);
//        $this->scheduleEmails($order, 'delivered');

        // testing sending test email
//        $template = Template::find(2);
//        $this->scheduleTestEmail($template);

        // testing cron job to send all emails
//        $this->sendScheduledEmails();

/*        $contents = Storage::get('last_result.html');
        $crawler = new Crawler($contents);
        Log::info("Got new feedbacks HTML - processing...");
        $crawler = $crawler->filter('table')->each(function (Crawler $node, $i) {
            $text = $node->text();
            if(strpos($text, 'Arrived on Time') !== false){
                Log::info("Found 'Arrived on Time' table");
                $feedbacks = $node->filter('table')->last()->filter('tr')->each(function (Crawler $node, $i) {
                    if($i > 0){
                        $feedback = $node->filter('td')->each(function (Crawler $node, $i) {
                            return trim($node->text());
                        });
                        $rating = null;
                        if(preg_match("/\d+/", $feedback[1], $matches)){
                            $rating = $matches[0];
                        }
                        $data = [
                            'published_at' => new Carbon($feedback[0]),
                            'rating' => $rating,
                            'comments' => $feedback[2],
                            'arrived_on_time' => $feedback[3] == 'Yes' ? 1 : ($feedback[3] != '-' ? 0 : null),
                            'item_as_described' => $feedback[4] == 'Yes' ? 1 : ($feedback[4] != '-' ? 0 : null),
                            'customer_service' => $feedback[5] == 'Yes' ? 1 : ($feedback[5] != '-' ? 0 : null),
                            'order_id' => $feedback[6]
                        ];
                        Log::info("Feedback found for order: {$feedback[6]}");

                        $order = Order::where('amazon_order_id', $data['order_id'])->first();
                        if(count($order) && !$order->feedbacks()->first()){
                            $data['user_id'] = $order->user_id;
                            $data['customer_id'] = $order->customer_id;
                            $data['order_id'] = $order->id;
                            $data['status'] = 'new';

                            Feedback::create($data);

                            Log::info("Added feedback: ". print_r($data, true));
                        }elseif(!count($order)){
                            Log::info("Ignoring feedback - order {$data['order_id']} not found");
                        }else{
                            Log::info("Ignoring feedback - feedback exists for this order {$data['order_id']}");
                        }

                    }
                });

            }
        });

        exit;
*/

        return view('panel.connect', ['user' => $request->user(), 'marketplaces' => Marketplace::pluck('amazon_marketplace_name', 'id')]);
    }

    public function storeConnect(Request $request)
    {

        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'amazon_seller_id' => "required|unique:users,amazon_seller_id,{$user->amazon_seller_id},amazon_seller_id|min:12|max:16",
            'amazon_mws_token' => "required",
            'amazon_email_from' => "required|email",
        ]);

        $validator->after(function ($validator) use ($request, $user) {

            $user->marketplace_id = $request->input('marketplace_id');
            $user->amazon_seller_id = $request->input('amazon_seller_id');
            $user->amazon_mws_token = $request->input('amazon_mws_token');
            $user->amazon_email_from = $request->input('amazon_email_from');
            $user->save();

            if(!$this->syncProducts($request)){
                $validator->errors()->add('amazon_seller_id', 'MWS credentials not valid!');
            }
        });

        if ($validator->fails()) {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        return view('panel.connect', ['user' => $request->user(), 'marketplaces' => Marketplace::pluck('amazon_marketplace_name', 'id')]);

    }


}