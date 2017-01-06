<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 100);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('request_id', 100);
            $table->string('processing_status', 100)->nullable();
            $table->string('generated_report_id', 100)->nullable();
//            $table->mediumBlob('body')->nullable();
            $table->enum('status', ['requested', 'error', 'nodata', 'ready', 'downloaded', 'processed'])->default('requested');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE reports ADD body MEDIUMBLOB after generated_report_id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
