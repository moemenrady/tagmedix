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
        // 1. Settings key-value support
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'key')) {
                    $table->string('key')->unique()->nullable();
                }
                if (!Schema::hasColumn('settings', 'value')) {
                    $table->text('value')->nullable();
                }
            });
        }

        // 2. Orders: is_urgent and delivery_date
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'is_urgent')) {
                    $table->boolean('is_urgent')->default(false)->after('status');
                }
                if (!Schema::hasColumn('orders', 'delivery_date')) {
                    $table->date('delivery_date')->nullable()->after('is_urgent');
                }
            });
        }

        // 3. Types: price
        if (Schema::hasTable('types')) {
            Schema::table('types', function (Blueprint $table) {
                if (!Schema::hasColumn('types', 'price')) {
                    $table->decimal('price', 10, 2)->default(0)->after('identifier');
                }
            });
        }

        // 4. Styles: price
        if (Schema::hasTable('styles')) {
            Schema::table('styles', function (Blueprint $table) {
                if (!Schema::hasColumn('styles', 'price')) {
                    $table->decimal('price', 10, 2)->default(0)->after('identifier');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('styles')) {
            Schema::table('styles', function (Blueprint $table) {
                if (Schema::hasColumn('styles', 'price')) {
                    $table->dropColumn('price');
                }
            });
        }

        if (Schema::hasTable('types')) {
            Schema::table('types', function (Blueprint $table) {
                if (Schema::hasColumn('types', 'price')) {
                    $table->dropColumn('price');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'delivery_date')) {
                    $table->dropColumn('delivery_date');
                }
                if (Schema::hasColumn('orders', 'is_urgent')) {
                    $table->dropColumn('is_urgent');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'value')) {
                    $table->dropColumn('value');
                }
                if (Schema::hasColumn('settings', 'key')) {
                    $table->dropColumn('key');
                }
            });
        }
    }
};
