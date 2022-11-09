<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToMasterClassUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_class_users', function (Blueprint $table) {
            $table->unsignedBigInteger('promoter_id')->nullable()->after('master_class_id');
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
            $table->dropColumn('promoter_id');
        });
    }
}
