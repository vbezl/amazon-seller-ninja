<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 250)->nullable();
            $table->string('amazon_category_id', 100);
            $table->timestamps();

            $table->unique('amazon_category_id');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('asin', 45);
            $table->string('title', 250);
            $table->string('image_url', 250)->nullable();
            $table->timestamps();

            $table->unique('asin');
        });

        Schema::create('product_user', function (Blueprint $table) {
            $table->integer('product_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('in_user_seller_account');
            $table->boolean('track');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'product_id']);
            $table->index('deleted_at');
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->boolean('track');

            $table->unique(['product_id', 'category_id']);
        });

        Schema::create('prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->double('regular_price', 8, 2);
            $table->double('buying_price', 8, 2);
            $table->timestamps();

            $table->index('product_id');
            $table->index('created_at');
        });

        Schema::create('ranks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('rank')->unsigned();
            $table->timestamps();

            $table->index(['product_id', 'category_id']);
            $table->index('created_at');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_user');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('ranks');
    }
}
