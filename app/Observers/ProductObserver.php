<?php

namespace App\Observers;

use Log;
use App\Models\Product;
use App\Traits\AmazonFunctionsTrait;

class ProductObserver
{
    use AmazonFunctionsTrait;

    public function created(Product $product)
    {
        //
        $user = request()->user();
        Log::info('observer - new product created: '.$product->asin);

        Log::info('observer - sync product info: '.$product->asin);
        $this->syncProductInfo($product);

    }

}