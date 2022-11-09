<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToCallBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_bookings', function (Blueprint $table) {
            $table->enum('notification_sent',['yes','no'])->default('no')->after('payment_settled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_bookings', function (Blueprint $table) {
            $table->dropColumn('notification_sent');
        });
    }
}
