<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{

    /**
     * The users that have this marketplace.
     */
    public function users()
    {
        return $this->hasMany('App\User');
    }

}
