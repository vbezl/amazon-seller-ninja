<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Product extends Model
{
	use CrudTrait;

     /*
	|--------------------------------------------------------------------------
	| GLOBAL VARIABLES
	|--------------------------------------------------------------------------
	*/

	protected $table = 'products';
	protected $primaryKey = 'id';
	// public $timestamps = false;
	// protected $guarded = ['id'];
    protected $fillable = ['asin', 'title', 'image_url'];
	// protected $hidden = [];
    // protected $dates = [];

	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/

    /**
     * The users that belong to the product.
     */
    public function users()
    {
        return $this->belongsToMany('App\User')
            ->whereNull('product_user.deleted_at')
            ->withTimestamps()
            ->withPivot(['in_user_seller_account','track']);
    }

    /**
     * The categories that belong to the product.
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Category')->withPivot(['track']);
    }

    /**
     * Get the prices for the product.
     */
    public function prices()
    {
        return $this->hasMany('App\Models\Price');
    }

    /**
     * Get the ranks for the product.
     */
    public function ranks()
    {
        return $this->hasMany('App\Models\Rank');
    }

    /**
     * Get the order items for the product.
     */
    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

    /**
     * Get the templates for the product.
     */
    public function templates()
    {
        return $this->hasMany('App\Models\Template');
    }

    /**
     * Get the emails for the product.
     */
    public function emails()
    {
        return $this->hasMany('App\Models\Email');
    }

    /*
     |--------------------------------------------------------------------------
     | SCOPES
     |--------------------------------------------------------------------------
     */

	/*
	|--------------------------------------------------------------------------
	| ACCESORS
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| MUTATORS
	|--------------------------------------------------------------------------
	*/
}
