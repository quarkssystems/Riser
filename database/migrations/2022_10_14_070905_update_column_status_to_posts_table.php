<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnStatusToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            DB::statement("ALTER TABLE posts MODIFY COLUMN status ENUM('active', 'inactive', 'processing','failed') NOT NULL DEFAULT 'processing'");
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
            DB::statement("ALTER TABLE posts MODIFY COLUMN status ENUM('active', 'inactive', 'processing') NOT NULL DEFAULT 'processing'");
        });
    }
}
