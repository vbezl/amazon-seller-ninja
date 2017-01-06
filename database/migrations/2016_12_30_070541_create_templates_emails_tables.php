<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesEmailsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('title', 250);
            $table->string('subject', 250);
            $table->mediumText('body');
            $table->enum('event', ['shipped', 'out_for_delivery', 'delivered']);
            $table->integer('event_delay_minutes')->unsigned();
            $table->enum('status', ['active', 'inactive', 'test']);
            $table->timestamps();
            $table->softDeletes();

            $table->index('deleted_at');
            $table->index('user_id');
            $table->index('product_id');
            $table->unique(['user_id', 'title']);
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('order_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('email_from', 250);
            $table->string('email_to', 250);
            $table->string('subject', 250);
            $table->mediumText('body');
            $table->enum('status', ['scheduled', 'sent']);
            $table->dateTime('scheduled_at');
            $table->dateTime('sent_at');
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('template_id');
            $table->index('user_id');
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
        Schema::dropIfExists('templates');
        Schema::dropIfExists('emails');
    }
}
