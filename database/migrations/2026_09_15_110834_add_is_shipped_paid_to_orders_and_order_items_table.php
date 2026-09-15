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
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'is_shipped_paid')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_shipped_paid')->default(false)->after('total_price');
            });
        }

        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'is_shipped_paid')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->boolean('is_shipped_paid')->default(false)->after('total');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'is_shipped_paid')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('is_shipped_paid');
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'is_shipped_paid')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('is_shipped_paid');
            });
        }
    }
};
