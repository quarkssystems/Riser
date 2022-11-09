<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPaymentPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_payouts', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_user_id')->nullable()->after('payout_amount');
            $table->string('parent_role')->nullable()->after('parent_user_id');
            $table->decimal('parent_percentage', 12, 2)->default(0)->after('parent_role');
            $table->decimal('parent_payout_amount', 12, 2)->default(0)->after('parent_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_payouts', function (Blueprint $table) {
            $table->dropColumn('parent_user_id');
            $table->dropColumn('parent_role');
            $table->dropColumn('parent_percentage');
            $table->dropColumn('parent_payout_amount');
        });
    }
}
