<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = ['amazon_category_id', 'title'];

    /**
     * The products that belong to the category.
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\Product')->withPivot(['track']);
    }

}
