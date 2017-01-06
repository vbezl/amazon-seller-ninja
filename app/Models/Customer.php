<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $fillable = ['full_name', 'first_name', 'email'];

    /**
     * The orders that belong to the customer.
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

}
