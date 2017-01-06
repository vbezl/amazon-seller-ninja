<?php

namespace App\Observers;

use Log;
use App\Models\Unsubscriber;

class UnsubscriberObserver
{

    public function creating(Unsubscriber $unsubscriber)
    {
        //
        $user = request()->user();
        Log::info('creating new unsubscriber - setting current user_id: '.$user->id);
        $unsubscriber->user_id = $user->id;

    }

}