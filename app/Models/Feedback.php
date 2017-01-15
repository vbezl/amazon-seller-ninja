<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Feedback extends Model
{
	use CrudTrait;

     /*
	|--------------------------------------------------------------------------
	| GLOBAL VARIABLES
	|--------------------------------------------------------------------------
	*/

	protected $table = 'feedbacks';
	protected $primaryKey = 'id';
	// public $timestamps = false;
	// protected $guarded = ['id'];
	protected $fillable = ['user_id', 'order_id', 'customer_id', 'rating', 'comments', 'arrived_on_time',
        'item_as_described', 'customer_service', 'published_at', 'status'];
	// protected $hidden = [];
    protected $dates = ['published_at'];

	/*
	|--------------------------------------------------------------------------
	| FUNCTIONS
	|--------------------------------------------------------------------------
	*/

    public function getPublishedDate()
    {
        return $this->published_at->toDateString();
    }

	/*
	|--------------------------------------------------------------------------
	| RELATIONS
	|--------------------------------------------------------------------------
	*/

    /**
     * The user that owns this feedback
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The order that owns this feedback
     */
    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    /**
     * The customer that owns this feedback
     */
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
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
