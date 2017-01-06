<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{

    protected $fillable = ['type', 'start_date', 'end_date', 'request_id', 'processing_status'];

    protected $dates = [
        'created_at',
        'updated_at',
        'start_date',
        'end_date'
    ];

}
