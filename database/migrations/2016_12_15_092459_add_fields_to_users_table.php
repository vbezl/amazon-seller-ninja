<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {

            $table->string('first_name', 45)->after('password')->nullable();
            $table->string('last_name', 45)->after('password')->nullable();
            $table->string('amazon_seller_id', 45)->after('password')->nullable();
            $table->string('amazon_mws_token', 250)->after('password')->nullable();
            $table->string('amazon_email_from', 250)->after('password')->nullable();

            $table->softDeletes();

            $table->index('deleted_at');
            $table->unique('amazon_seller_id');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
