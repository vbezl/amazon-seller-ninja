<?php

namespace App\Observers;

use Log;
use App\Models\Report;

class ReportObserver
{

    public function updating(Report $report)
    {
        //
        Log::info('report updating: '.$report->id);
        $dirty = $report->getDirty();
        if(!empty($dirty['processing_status'])){

            Log::info('processing_status changed: '.$dirty['processing_status']);
            if($dirty['processing_status'] == '_DONE_'){
                $report->status = 'ready';

            }elseif($dirty['processing_status'] == '_DONE_NO_DATA_'){
                $report->status = 'nodata';

            }else {
                $report->status = 'error';
            }

        }

        if(isset($dirty['body'])){
            Log::info('report body changed');
            $report->status = 'downloaded';
        }

    }

}