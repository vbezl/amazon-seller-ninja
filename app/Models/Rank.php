<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{

    protected $fillable = ['rank', 'product_id', 'category_id'];

    /**
     * The product that belong to the price.
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

}
