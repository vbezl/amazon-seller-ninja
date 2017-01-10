<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('order_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->tinyInteger('rating');
            $table->string('comments', 250);
            $table->boolean('arrived_on_time')->nullable();
            $table->boolean('item_as_described')->nullable();
            $table->boolean('customer_service')->nullable();
            $table->enum('status', ['new', 'waiting', 'answered', 'ticket', 'resolved', 'nofix'])->default('new');
            $table->dateTime('published_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
}
