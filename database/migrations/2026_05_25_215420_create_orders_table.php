<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('order_number')->unique();

            $table->decimal('total_price', 10, 2)->default(0);

            // المبلغ المطلوب تحصيله عند التسليم
            $table->decimal('shipping_due_amount', 10, 2)->default(0);

            // المبلغ المطلوب استلامه عند الارتجاع
            $table->decimal('return_due_amount', 10, 2)->default(0);

            // وصف محتويات الشحنة
            $table->text('order_contents')->nullable();

            $table->enum('source', [
                'new',
                'from_stock',
                'replacement',
            ])->default('new');

            $table->enum('status', [
                'new',
                'confirmed',
                'cutting',
                'sewing',
                'ironing',
                'finishing',
                'ready-to-ship',
                'shipped',
                'delivered',
                'canceled',
            ])->default('new');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
