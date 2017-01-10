<?php

namespace App\Observers;

use Log;
use App\Models\Feedback;
use App\Traits\AmazonFunctionsTrait;

class FeedbackObserver
{
    use AmazonFunctionsTrait;

    /**
     * Listen to the Feedback created event.
     *
     * @param  Feedback $feedback
     * @return void
     */
    public function created(Feedback $feedback)
    {
        //
        $order = $feedback->order()->first();
        Log::info('observer - feedback created: '.$order->amazon_order_id);

        if($feedback->rating > 3) {

            // positive feedback created - schedule emails for this event
            Log::info('feedback is POSITIVE: '.$feedback->rating.' stars');

            $this->scheduleEmails($order, 'positive_feedback');
        } else {

            // negative feedback created - schedule emails for this event
            Log::info('feedback is NEGATIVE'.$feedback->rating.' stars');

            $this->scheduleEmails($order, 'negative_feedback');

        }
    }


}