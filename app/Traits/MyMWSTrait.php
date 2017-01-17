<?php namespace App\Traits;

trait MyMWSTrait {

    public function setStore($user)
    {

        $store = [
            'store' => [
                'merchantId' => $user->amazon_seller_id,
                'MWSAuthToken' => $user->amazon_mws_token,
                'marketplaceId' => 'ATVPDKIKX0DER',
                'keyId' => 'AKIAJGPC6WPJ7SBXYECA',
                'secretKey' => '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',
                'amazonServiceUrl' => 'https://mws.amazonservices.com/',
            ],

            // Default service URL
            'AMAZON_SERVICE_URL' => 'https://mws.amazonservices.com/',

            'muteLog' => false
        ];

        $this->storeName = 'store1';
        $s = 'store';
        if (array_key_exists('merchantId', $store[$s])) {
            $this->options['SellerId'] = $store[$s]['merchantId'];
        } else {
            $this->log("Merchant ID is missing!", 'Warning');
        }
        if (array_key_exists('MWSAuthToken', $store[$s])) {
            $this->options['MWSAuthToken'] = $store[$s]['MWSAuthToken'];
        } else {
            $this->log("MWSAuthToken is missing!", 'Warning');
        }
        if (array_key_exists('keyId', $store[$s])) {
            $this->options['AWSAccessKeyId'] = $store[$s]['keyId'];
        } else {
            $this->log("Access Key ID is missing!", 'Warning');
        }
        if (!array_key_exists('secretKey', $store[$s])) {
            $this->log("Secret Key is missing!", 'Warning');
        }
        // Overwrite Amazon service url if specified
        if (array_key_exists('amazonServiceUrl', $store[$s])) {
            $AMAZON_SERVICE_URL = $store[$s]['amazonServiceUrl'];
            $this->urlbase = $AMAZON_SERVICE_URL;
        }

    }


}