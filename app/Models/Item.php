<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $fillable = ['order_id', 'product_id', 'quantity_ordered', 'quantity_shipped', 'amazon_order_item_id',
        'price', 'discount', 'tax'];

    /**
     * The order that owns this item
     */
    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    /**
     * The product that owns this item
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

}
