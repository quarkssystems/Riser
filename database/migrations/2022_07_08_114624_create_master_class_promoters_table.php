<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterClassPromotersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_class_promoters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('Promoter User Id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('master_class_id')->constrained('master_classes')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_class_promoters');
    }
}
