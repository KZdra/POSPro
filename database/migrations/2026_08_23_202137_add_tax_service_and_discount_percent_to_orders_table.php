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
            $table->decimal('discount_percent', 5, 2)->default(0)->after('discount');
            $table->decimal('service', 15, 2)->default(0)->after('tax');
            $table->decimal('service_percent', 5, 2)->default(0)->after('service');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('service_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'service', 'service_percent', 'tax_percent']);
        });
    }
};
