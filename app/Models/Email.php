<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendScheduledEmail;
use Backpack\CRUD\CrudTrait;
use Carbon\Carbon;
use Log;

class Email extends Model
{
    use CrudTrait;

    protected $fillable = ['email_from', 'email_to', 'subject', 'body', 'status', 'scheduled_at',
        'template_id', 'order_id', 'user_id', 'product_id'];

    protected $dates = [
        'scheduled_at',
        'sent_at'
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
     * The template that owns this template
     */
    public function template()
    {
        return $this->belongsTo('App\Models\Template');
    }

    /**
     * The order that owns this template
     */
    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    public function send()
    {
        // send email
        Log::info('sending email id: '.$this->id);

        Mail::send(new SendScheduledEmail($this));

        // marking this email as sent
        $this->status = 'sent';
        $this->sent_at = Carbon::now();
        $this->save();
    }

}
