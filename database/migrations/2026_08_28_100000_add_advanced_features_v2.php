<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cash Movements (Petty Cash In / Out for active shifts)
        if (!Schema::hasTable('cash_movements')) {
            Schema::create('cash_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_shift_id')->constrained('cash_shifts')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('type', ['CASH_IN', 'CASH_OUT'])->default('CASH_OUT');
                $table->decimal('amount', 14, 2);
                $table->string('reason', 255);
                $table->timestamps();
            });
        }

        // 2. Order updates for Redeem Points, Split Payment, and Kitchen Display System
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'points_redeemed')) {
                $table->integer('points_redeemed')->default(0)->after('points_earned');
            }
            if (!Schema::hasColumn('orders', 'points_discount')) {
                $table->decimal('points_discount', 14, 2)->default(0)->after('points_redeemed');
            }
            if (!Schema::hasColumn('orders', 'is_split_payment')) {
                $table->boolean('is_split_payment')->default(false)->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('is_split_payment');
            }
            if (!Schema::hasColumn('orders', 'kitchen_status')) {
                $table->enum('kitchen_status', ['PENDING', 'COOKING', 'READY', 'SERVED'])->default('PENDING')->after('order_type');
            }
            if (!Schema::hasColumn('orders', 'kitchen_updated_at')) {
                $table->timestamp('kitchen_updated_at')->nullable()->after('kitchen_status');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');

        Schema::table('orders', function (Blueprint $table) {
            $cols = ['points_redeemed', 'points_discount', 'is_split_payment', 'payment_details', 'kitchen_status', 'kitchen_updated_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
