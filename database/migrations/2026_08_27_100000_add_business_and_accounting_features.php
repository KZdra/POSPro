<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add cost_price to order_items
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'cost_price')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('price');
            });
        }

        // 2. Add min_stock to products
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'min_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('min_stock')->default(5)->after('stock');
            });
        }

        // 3. Create customers table
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable()->unique();
                $table->string('email')->nullable();
                $table->integer('points')->default(0);
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        // 4. Add customer_id and points_earned to orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'customer_id')) {
                    $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'points_earned')) {
                    $table->integer('points_earned')->default(0)->after('discount');
                }
            });
        }

        // 5. Create cash_shifts table for Cashier Shift Management
        if (!Schema::hasTable('cash_shifts')) {
            Schema::create('cash_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('opening_cash', 15, 2)->default(0);
                $table->decimal('cash_sales', 15, 2)->default(0);
                $table->decimal('non_cash_sales', 15, 2)->default(0);
                $table->decimal('expected_cash', 15, 2)->default(0);
                $table->decimal('actual_cash', 15, 2)->nullable();
                $table->decimal('difference', 15, 2)->nullable();
                $table->string('status')->default('OPEN'); // 'OPEN' or 'CLOSED'
                $table->timestamp('opened_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'customer_id')) {
                    $table->dropForeign(['customer_id']);
                    $table->dropColumn('customer_id');
                }
                if (Schema::hasColumn('orders', 'points_earned')) {
                    $table->dropColumn('points_earned');
                }
            });
        }

        Schema::dropIfExists('customers');

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'min_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'cost_price')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('cost_price');
            });
        }
    }
};
