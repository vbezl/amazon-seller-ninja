<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\Report;
use App\Observers\ReportObserver;
use App\Models\Template;
use App\Observers\TemplateObserver;
use App\Models\Unsubscriber;
use App\Observers\UnsubscriberObserver;
use App\Models\Feedback;
use App\Observers\FeedbackObserver;
use App\Models\Product;
use App\Observers\ProductObserver;

use App\Traits\AmazonFunctionsTrait;

class AppServiceProvider extends ServiceProvider
{

    use AmazonFunctionsTrait;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Order::observe(OrderObserver::class);
        Report::observe(ReportObserver::class);
        Template::observe(TemplateObserver::class);
        Unsubscriber::observe(UnsubscriberObserver::class);
        Feedback::observe(FeedbackObserver::class);
        Product::observe(ProductObserver::class);

        // adding custom validation rule - if ASIN is in amazon?
        Validator::extend('asin_amazon', function ($attribute, $value, $parameters, $validator) {
            return $this->checkProductAsin($value);
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        if ($this->app->environment() == 'local') {
            // Jeffrey Way's generators
            $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
            // Backpack generators
            $this->app->register('Backpack\Generators\GeneratorsServiceProvider');
        }
    }
}
