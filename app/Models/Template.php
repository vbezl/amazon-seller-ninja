<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\CrudTrait;

class Template extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $fillable = ['title', 'subject', 'body', 'event', 'event_delay_minutes', 'status',
        'user_id', 'product_id', 'skip_if_feedback_left'];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * The user that owns this template
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The product that owns this template
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    /**
     * The emails that belong to this order
     */
    public function emails()
    {
        return $this->hasMany('App\Models\Email');
    }

}
