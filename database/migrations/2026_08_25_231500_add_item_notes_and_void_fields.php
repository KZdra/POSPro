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
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('notes', 500)->nullable()->after('subtotal');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('coupon_code', 50)->nullable()->after('discount_percent');
            $table->text('void_reason')->nullable()->after('notes');
            $table->foreignId('void_by')->nullable()->after('void_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable()->after('void_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['void_by']);
            $table->dropColumn(['coupon_code', 'void_reason', 'void_by', 'voided_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
