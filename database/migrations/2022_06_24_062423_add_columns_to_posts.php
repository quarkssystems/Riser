<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('media_url')->nullable()->change();
            $table->string('library_id')->nullable()->after('media_type');
            $table->string('video_id')->nullable()->after('library_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('media_url')->nullable(false)->change();
            $table->dropColumn('library_id');
            $table->dropColumn('video_id');
        });
    }
}
