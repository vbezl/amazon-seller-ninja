<?php

namespace App\Observers;

use Log;
use App\Models\Order;
use App\Traits\AmazonFunctionsTrait;

class OrderObserver
{
    use AmazonFunctionsTrait;

    /**
     * Listen to the Order created event.
     *
     * @param  Order $order
     * @return void
     */
    public function created(Order $order)
    {
        //
        Log::info('order created: '.$order->amazon_order_id);
    }

    public function updating(Order $order)
    {
        //
//        Log::info('order updating: '.print_r($order, true).' ORIGINAL: '.print_r($order->getOriginal(), true). ' GET_DIRTY?: '.print_r($order->getDirty(), true));

    }

    public function updated(Order $order)
    {
        //
        Log::info('order updated: '.print_r($order, true).' ORIGINAL: '.print_r($order->getOriginal(), true). ' GET_DIRTY?: '.print_r($order->getDirty(), true));

        $original = $order->getOriginal();
        $dirty = $order->getDirty();

        if(!empty($dirty['order_status']) && $dirty['order_status'] == 'Shipped') {
            // order is shipped event!
            // register all emails connected to this even for this order!
            Log::info('order is shipped');

            $this->scheduleEmails($order, 'shipped');
        }

        if(!empty($dirty['ship_status']) && $dirty['ship_status'] == 'out_for_delivery') {
            // order is out for delivery event!
            // register all emails connected to this even for this order!
            Log::info('order is OUT FOR DELIVERY');

            $this->scheduleEmails($order, 'out_for_delivery');
        }

        if(!empty($dirty['ship_status']) && $dirty['ship_status'] == 'delivered') {
            // order is out for delivery event!
            // register all emails connected to this even for this order!
            Log::info('order is DELIVERED');

            $this->scheduleEmails($order, 'delivered');
        }

//        exit;
    }


}