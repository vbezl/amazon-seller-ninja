<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = ['user_id', 'customer_id', 'amazon_order_id', 'seller_order_id', 'fulfillment_channel', 'order_status',
        'is_business_order', 'is_prime', 'number_of_items_shipped', 'number_of_items_unshipped',
        'order_total_amount', 'ship_country_code', 'ship_state', 'ship_city', 'ship_zip',
        'ship_full_name', 'ship_address1', 'ship_address2', 'purchase_date', 'last_update_date'];

    protected $dates = [
        'created_at',
        'updated_at',
        'purchase_date',
        'last_update_date',
        'ship_last_tracked_at',
        'ship_estimated_arrival_date',
        'ship_delivered_date'
    ];

    /**
     * The user that owns this order
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The customer that owns this order
     */
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    /**
     * The items that belong to this order
     */
    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

    /**
     * The emails that belong to this order
     */
    public function emails()
    {
        return $this->hasMany('App\Models\Email');
    }

}
