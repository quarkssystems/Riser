<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentPercentageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_percentages', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('role');
            $table->string('parent_role')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('hiddent_cut_percent', 5, 2)->default(0);
            $table->enum('status',['active','inactive'])->default('active');
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
        Schema::dropIfExists('payment_percentages');
    }
}
