<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('notes');
            $table->string('settlement_type')->default('AUTOMATIC_WEBHOOK')->after('payment_proof'); // AUTOMATIC_WEBHOOK or MANUAL_CASHIER
            $table->foreignId('settled_by')->nullable()->after('settlement_type')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['settled_by']);
            $table->dropColumn(['payment_proof', 'settlement_type', 'settled_by']);
        });
    }
};
