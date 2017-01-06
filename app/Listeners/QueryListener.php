<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\File;

class QueryListener
{
    public function handle(QueryExecuted $sql)
    {
        foreach ($sql->bindings as $i => $binding) {
            if ($binding instanceof \DateTime) {
                $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
            } else {
                if (is_string($binding)) {
                    $sql->bindings[$i] = "'$binding'";
                }
            }
        }
        // Insert bindings into query
        $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);
        $query = vsprintf($query, $sql->bindings);
        // Save the query to file
        File::append(storage_path('logs' . DIRECTORY_SEPARATOR . 'sql_' . date('Y-m-d') . '.log'), '[' . date('Y-m-d H:i:s') . '] ' . $query . " (time {$sql->time}ms)" . PHP_EOL);
    }
}
