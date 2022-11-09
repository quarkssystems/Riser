<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefreshTokenInCallBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_bookings', function (Blueprint $table) {
            $table->string('meeting_id')->nullable()->after('status');
            $table->text('meeting_link')->nullable()->after('status');
            $table->text('refresh_token')->nullable()->after('status');
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
            $table->dropColumn('meeting_id');
            $table->dropColumn('meeting_link');
            $table->dropColumn('refresh_token');
        });
    }
}
