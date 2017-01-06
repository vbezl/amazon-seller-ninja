<?php

namespace App\Observers;

use Log;
use App\Models\Template;

class TemplateObserver
{

    public function creating(Template $template)
    {
        //
        $user = request()->user();
        Log::info('creating new template - setting current user_id: '.$user->id);
        $template->user_id = $user->id;

    }

}