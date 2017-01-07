<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Traits\AmazonFunctionsTrait;
use \Carbon\Carbon;
use App\Models\Product;

class Kernel extends ConsoleKernel
{

    use AmazonFunctionsTrait;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call( function () {

            // get products which need to be synced
            // TODO: get only products which are tracked
            $products = Product::with(['categories' => function($query){
                $query->wherePivot('track', 1);
            }])->get();

            $this->syncRanks($products);
            $this->syncPrices($products);

        })->dailyAt('21:55');


        // sync orders
        $schedule->call( function () {

            $this->syncOrders();

        })->hourly();


        // get _GET_AMAZON_FULFILLED_SHIPMENTS_DATA_ report
        $schedule->call( function () {

            $end_date = Carbon::now();
            $start_date = Carbon::now()->subDays(3);
            $this->requestReports('_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_', $start_date->toIso8601String(), $end_date->toIso8601String());

        })->twiceDaily(14, 20); //dailyAt('22:05');

        // check status of all reports
        $schedule->call( function () {

            $this->checkReports();

        })->twiceDaily(15, 21); //dailyAt('22:30');

        // download all reports
        $schedule->call( function () {

            $this->downloadReports();

        })->twiceDaily(16, 22); //dailyAt('22:40');

        // process all reports
        $schedule->call( function () {

            $this->processReports();

        })->twiceDaily(17, 23); //dailyAt('22:50');

        // check shipment tracking statuses
        $schedule->call( function () {

            $this->trackShipments();

        })->twiceDaily(18, 0); //dailyAt('23:00');

        // send scheduled emails
        $schedule->call( function () {

            $this->sendScheduledEmails();

        })->everyFiveMinutes();

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
