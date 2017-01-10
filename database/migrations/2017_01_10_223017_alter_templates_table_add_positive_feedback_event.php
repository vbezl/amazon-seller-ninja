<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTemplatesTableAddPositiveFeedbackEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("ALTER TABLE templates MODIFY COLUMN event ENUM('shipped', 'out_for_delivery', 'delivered', 'positive_feedback', 'negative_feedback')");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement("ALTER TABLE templates MODIFY COLUMN event ENUM('shipped', 'out_for_delivery', 'delivered')");
    }
}
