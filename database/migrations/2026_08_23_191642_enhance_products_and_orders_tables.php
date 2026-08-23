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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('sku')->nullable()->after('name');
            $table->string('barcode')->nullable()->after('sku');
            $table->decimal('cost_price', 15, 2)->default(0)->after('price');
            $table->boolean('is_active')->default(true)->after('image');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('customer_name')->default('Walk-in Customer')->after('order_id');
            $table->decimal('cash_received', 15, 2)->default(0)->after('grand_total');
            $table->decimal('cash_change', 15, 2)->default(0)->after('cash_received');
            $table->decimal('discount', 15, 2)->default(0)->after('cash_change');
            $table->decimal('tax', 15, 2)->default(0)->after('discount');
            $table->text('notes')->nullable()->after('tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'sku', 'barcode', 'cost_price', 'is_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'customer_name', 'cash_received', 'cash_change', 'discount', 'tax', 'notes']);
        });
    }
};
