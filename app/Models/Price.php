<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{

    protected $fillable = ['product_id', 'regular_price', 'buying_price'];

    /**
     * The product that belong to the price.
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

}
