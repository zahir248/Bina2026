<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Store encrypted client_secret so we can reuse the same PaymentIntent for repay.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('stripe_client_secret_encrypted')->nullable()->after('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stripe_client_secret_encrypted');
        });
    }
};
