<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name', 250);
            $table->string('first_name', 250);
            $table->string('email', 250);
            $table->timestamps();

            $table->unique('email');

        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->string('amazon_order_id', 25);
            $table->string('seller_order_id', 100);
            $table->enum('fulfillment_channel', ['AFN', 'MFN']);
            $table->enum('order_status', ['Pending', 'Unshipped', 'PartiallyShipped', 'Shipped', 'Canceled', 'Unfulfillable', 'PendingAvailability']);
            $table->integer('number_of_items_shipped')->unsigned();
            $table->integer('number_of_items_unshipped')->unsigned();
            $table->double('order_total_amount', 8, 2);
            $table->string('ship_country_code', 5);
            $table->string('ship_state', 45);
            $table->string('ship_city', 100);
            $table->string('ship_zip', 45);
            $table->string('ship_full_name', 250);
            $table->string('ship_address1', 100);
            $table->string('ship_address2', 100);
            $table->string('ship_carrier', 45);
            $table->string('ship_tracking_number', 45);
            $table->dateTime('ship_last_tracked_at');
            $table->dateTime('ship_estimated_arrival_date');
            $table->dateTime('ship_delivered_date');
            $table->enum('ship_status', ['start','out_for_delivery','delivered','stop'])->nullable();
            $table->dateTime('purchase_date');
            $table->dateTime('last_update_date');
            $table->timestamps();

            $table->unique('amazon_order_id');
            $table->index('user_id');
            $table->index('customer_id');
            $table->index('last_update_date');

        });

        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quantity_ordered')->unsigned();
            $table->integer('quantity_shipped')->unsigned();
            $table->string('amazon_order_item_id', 100);
            $table->double('price', 8, 2);
            $table->double('discount', 8, 2);
            $table->double('tax', 8, 2);

            $table->timestamps();

            $table->unique('amazon_order_item_id');
            $table->index('order_id');
            $table->index('product_id');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('items');
    }
}
