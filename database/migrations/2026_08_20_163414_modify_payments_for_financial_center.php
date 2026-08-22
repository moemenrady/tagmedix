<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('type', [
                'order_advance',
                'company_supply',
            ])->default('order_advance')->after('order_id');

            $table->foreignId('order_id')->nullable()->change();

            // فهارس لتسريع البحث والفلترة
            $table->index(['type', 'payment_date']);
            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['type', 'payment_date']);
            $table->dropIndex(['order_id', 'type']);
            $table->dropColumn('type');
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};