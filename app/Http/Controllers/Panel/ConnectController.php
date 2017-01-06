<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;
use App\Traits\AmazonFunctionsTrait;

use App\Models\Product;
use App\Models\Order;
use App\Models\Template;
use App\Models\Email;

use \Carbon\Carbon;


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
//        $this->syncRanks($products);
//        $this->syncPrices($products);

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


        return view('panel.connect', ['user' => $request->user()]);
    }

    public function storeConnect(Request $request)
    {

        $user = $request->user();

        $this->validate($request, [
            'amazon_seller_id' => "required|unique:users,amazon_seller_id,{$user->amazon_seller_id},amazon_seller_id|min:12|max:16",
            'amazon_mws_token' => "required",
            'amazon_email_from' => "required|email",
        ]);

        $user->amazon_seller_id = $request->input('amazon_seller_id');
        $user->amazon_mws_token = $request->input('amazon_mws_token');
        $user->amazon_email_from = $request->input('amazon_email_from');
        $user->save();

        $this->syncProducts($request);

        return view('panel.connect', ['user' => $request->user()]);
    }


}