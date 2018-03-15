<?php

use Illuminate\Database\Seeder;

class MarketplacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'CA',
            'amazon_marketplace_id'     => 'A2EUQ1WTGCTBG2',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'MX',
            'amazon_marketplace_id'     => 'A1AM78C64UM0Y8',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'US',
            'amazon_marketplace_id'     => 'ATVPDKIKX0DER',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'DE',
            'amazon_marketplace_id'     => 'A1PA6795UKMFR9',
            'amazon_mws_endpoint'       => 'https://mws-eu.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'ES',
            'amazon_marketplace_id'     => 'A1RKKUPIHCS9HS',
            'amazon_mws_endpoint'       => 'https://mws-eu.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'FR',
            'amazon_marketplace_id'     => 'A13V1IB3VIYZZH',
            'amazon_mws_endpoint'       => 'https://mws-eu.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'IT',
            'amazon_marketplace_id'     => 'APJ6JRA9NG5V4',
            'amazon_mws_endpoint'       => 'https://mws-eu.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'UK',
            'amazon_marketplace_id'     => 'A1F83G8C2ARO7P',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.com/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'IN',
            'amazon_marketplace_id'     => 'A21TJRUUN4KGV',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.in/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'JP',
            'amazon_marketplace_id'     => 'A1VC38T7YXB528',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.jp/'
        ]);
        DB::table('marketplaces')->insert([
            'amazon_marketplace_name'   => 'CN',
            'amazon_marketplace_id'     => 'AAHKV2X7AFYLW',
            'amazon_mws_endpoint'       => 'https://mws.amazonservices.com.cn/'
        ]);

    }
}
