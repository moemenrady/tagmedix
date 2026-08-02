<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('phone_1');
            $table->string('phone_2')->nullable();
            $table->string('governorate');
            $table->text('full_address');
            $table->string('instagram_profile')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('source', [
                'new',
                'from_stock',
                'replacement'
            ])->default('new');
            $table->enum('status', ['new','confirmed','cutting','sewing','finishing','ready-to-ship','shipped','delivered','canceled'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
