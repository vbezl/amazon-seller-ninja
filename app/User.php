<?php

namespace App;

use Backpack\Base\app\Notifications\ResetPasswordNotification as ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * The products that belong to the user.
     */
    public function products()
    {
        return $this->belongsToMany('App\Models\Product')
            ->whereNull('product_user.deleted_at')
            ->withTimestamps()
            ->withPivot(['in_user_seller_account','track']);
    }

    /**
     * The orders that belong to the user.
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    /**
     * The templates that belong to the user.
     */
    public function templates()
    {
        return $this->hasMany('App\Models\Template');
    }

    /**
     * The emails that belong to the user.
     */
    public function emails()
    {
        return $this->hasMany('App\Models\Email');
    }

    /**
     * The unsubscribers that belong to the user.
     */
    public function unsubscribers()
    {
        return $this->hasMany('App\Models\Unsubscriber');
    }

}
