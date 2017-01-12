<?php

namespace App\Observers;

use Log;
use App\Models\Feedback;
use App\Models\Template;
use App\Models\Email;
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

        // RULE: skip if feedback left
        // get templates using this rule, get scheduled emails for feedback order, check and delete if needed
        $user = $feedback->user()->first();
        $template_ids = $user->templates()
            ->where('skip_if_feedback_left', 1)
            ->get()
            ->pluck('id')
            ->toArray();

        if(sizeof($template_ids) > 0){
            Log::info('Templates found with skip_if_feedback_left=1... checking scheduled emails for current order');

            $emails_deleted = $order->emails()
                ->whereIn('template_id', $template_ids)
                ->where('status', 'scheduled')
                ->delete();

            Log::info('Scheduled emails deleted: '.$emails_deleted.' emails');
        }
    }


}