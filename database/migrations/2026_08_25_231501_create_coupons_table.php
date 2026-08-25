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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('title', 100);
            $table->enum('discount_type', ['PERCENT', 'FIXED'])->default('PERCENT'); // PERCENT (% off) or FIXED (Rp off)
            $table->decimal('discount_value', 15, 2);
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable(); // Nullable for unlimited cap on % discount
            $table->integer('usage_limit')->nullable(); // Nullable for unlimited total usage
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
