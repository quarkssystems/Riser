<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDateIdToMasterClassUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_class_users', function (Blueprint $table) {
            $table->foreignId('master_class_dates_id')->nullable()->after('promoter_id')->constrained('master_class_dates')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_class_users', function (Blueprint $table) {
            $table->dropForeign(['master_class_dates_id']);
            $table->dropColumn('master_class_dates_id');
        });
    }
}
