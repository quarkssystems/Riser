<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterClassDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_class_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_class_id')->constrained('master_classes')->onUpdate('cascade')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->integer('duration')->default(30);
            $table->enum('notification_sent',['yes','no'])->default('no');
            $table->boolean('is_master_class_started')->default(false);
            $table->boolean('is_master_class_ended')->default(false);
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_class_dates');
    }
}
