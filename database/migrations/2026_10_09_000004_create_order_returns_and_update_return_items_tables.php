<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('return_items');

        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->enum('return_type', ['full', 'partial'])->default('full');
            $table->string('reason'); // e.g. مقاس غير مناسب، عيب صناعة، رفض استلام، إلخ
            $table->enum('status', ['pending', 'received', 'inspected', 'completed', 'rejected'])->default('pending');
            $table->enum('qc_status', ['pending', 'good', 'damaged', 'partial_good'])->default('pending');
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('shipping_cost_deducted', 12, 2)->default(0);
            $table->enum('refund_status', ['pending', 'refunded', 'no_refund'])->default('pending');
            $table->string('action_taken')->nullable(); // e.g. استرجاع نقدي، استبدال، إعادة للمخزن
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained('order_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->enum('qc_result', ['uninspected', 'good', 'damaged'])->default('uninspected');
            $table->boolean('is_restocked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('order_returns');

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
