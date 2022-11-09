<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationInMasterClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_classes', function (Blueprint $table) {
            $table->integer('duration')->default(30)->after('meeting_link');
            $table->string('meeting_id')->nullable()->after('meeting_link');
            $table->text('refresh_token')->nullable()->after('meeting_link');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_classes', function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('meeting_id');
            $table->dropColumn('refresh_token');
        });
    }
}
