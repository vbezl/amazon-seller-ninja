<?php namespace App\MyMWS;

use Peron\AmazonMws\AmazonOrderList;
use Peron\AmazonMws\AmazonOrderCore;

use App\Traits\MyMWSTrait;

class MyAmazonOrderList extends AmazonOrderList
{

    use MyMWSTrait;

    public function __construct($user, $mock = false, $m = null, $config = null)
    {
        AmazonOrderCore::__construct($user, $mock, $m, $config);

        include($this->env);

        // TODO: get marketplaceId from user info
        $store = [
            'store' => [
                'marketplaceId' => 'ATVPDKIKX0DER',
            ],
        ];

        $s = 'store';

        if (isset($store[$s]) && array_key_exists('marketplaceId', $store[$s])) {
            $this->options['MarketplaceId.Id.1'] = $store[$s]['marketplaceId'];
        } else {
            $this->log("Marketplace ID is missing", 'Urgent');
        }

        if (isset($THROTTLE_LIMIT_ORDERLIST)) {
            $this->throttleLimit = $THROTTLE_LIMIT_ORDERLIST;
        }
        if (isset($THROTTLE_TIME_ORDERLIST)) {
            $this->throttleTime = $THROTTLE_TIME_ORDERLIST;
        }
        $this->throttleGroup = 'ListOrders';
    }

}
