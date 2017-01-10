<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Order;
use App\Models\Feedback;
use Log;
use \Carbon\Carbon;

class ProcessFeedbacksController extends Controller
{

    /**
     * process feedbacks from casperjs (html of page: https://sellercentral.amazon.com/gp/feedback-manager/view-all-feedback.html/ref=fb_fbmgr_vwallfb?ie=UTF8&dateRange=&descendingOrder=1&sortType=Date)
     *
     */
    public function index(Request $request)
    {
        Log::info("got feedbacks for user_id=".$request->input('user_id'));
        Log::info("html=".$request->input('html'));

        $contents = $request->input('html');
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

    }


}