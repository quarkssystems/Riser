<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('payment_gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('master_class_id')->nullable();
            $table->unsignedBigInteger('call_booking_id')->nullable();
            $table->decimal('sub_total', 12, 2)->nullable();
            $table->decimal('tax', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->string('discount_code', 50)->nullable();
            $table->decimal('total', 18, 2)->nullable();
            $table->enum('payment_type', ['regular','affiliate'])->default('regular');
            $table->unsignedBigInteger('affiliate_user_id')->nullable();
            $table->enum('status', ['in-progress','cancelled','failed','completed'])->default('in-progress');
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
        Schema::dropIfExists('payment_transactions');
    }
}
