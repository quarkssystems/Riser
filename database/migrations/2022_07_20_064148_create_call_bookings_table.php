<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('call_package_id')->constrained('call_packages')->onUpdate('cascade');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('booking_message')->nullable();
            $table->decimal('booking_amount', 12, 2)->default(0);
            $table->unsignedBigInteger('trasaction_id')->comment('reference id from payment_transactions table')->nullable();
            $table->enum('status', ['requested','approved','rejected','booked','attended','missed'])->default('requested');
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
        Schema::dropIfExists('call_bookings');
    }
}
