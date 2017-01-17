<?php namespace App\MyMWS;

use Peron\AmazonMws\AmazonProductList;
use Peron\AmazonMws\AmazonCore;

use App\Traits\MyMWSTrait;

class MyAmazonProductList extends AmazonProductList
{

    use MyMWSTrait;

    public function __construct($user, $mock = false, $m = null, $config = null)
    {

        AmazonCore::__construct($user, $mock, $m);

        include($this->env);

        if (isset($AMAZON_VERSION_PRODUCTS)) {
            $this->urlbranch = 'Products/' . $AMAZON_VERSION_PRODUCTS;
            $this->options['Version'] = $AMAZON_VERSION_PRODUCTS;
        }

        // TODO: get marketplaceId from user info
        $store = [
            'store' => [
                'marketplaceId' => 'ATVPDKIKX0DER',
            ],
        ];

        $s = 'store';

        if (isset($store[$s]) && array_key_exists('marketplaceId', $store[$s])) {
            $this->options['MarketplaceId'] = $store[$s]['marketplaceId'];
        } else {
            $this->log("Marketplace ID is missing", 'Urgent');
        }

        if (isset($THROTTLE_LIMIT_PRODUCT)) {
            $this->throttleLimit = $THROTTLE_LIMIT_PRODUCT;
        }

        $this->options['Action'] = 'GetMatchingProductForId';

        if (isset($THROTTLE_TIME_PRODUCTLIST)) {
            $this->throttleTime = $THROTTLE_TIME_PRODUCTLIST;
        }
        $this->throttleGroup = 'GetMatchingProductForId';
    }

}
