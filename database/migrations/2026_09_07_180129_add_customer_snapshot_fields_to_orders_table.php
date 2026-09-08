<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_phone_1')->nullable()->after('customer_name');
            $table->string('customer_phone_2')->nullable()->after('customer_phone_1');
            $table->string('customer_instagram')->nullable()->after('customer_phone_2');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_phone_1',
                'customer_phone_2',
                'customer_instagram',
            ]);
        });
    }
};