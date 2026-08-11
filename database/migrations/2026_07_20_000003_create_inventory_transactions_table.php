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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            // الصنف
            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();
            // نوع الحركة
            $table->enum('type', [
                'purchase',        // شراء / إضافة للمخزون
                'issue',           // صرف من المخزن
                'adjustment_in',   // تسوية بالزيادة
                'adjustment_out',  // تسوية بالنقص
                'return_in',       // مرتجع للمخزن
                'return_out',      // مرتجع للمورد
            ]);
            // الكمية التي تحركت
            $table->decimal('quantity', 12, 2);
            // سعر الوحدة وقت الحركة
            $table->decimal('unit_cost', 12, 2)->default(0);
            // رصيد الصنف بعد الحركة
            $table->decimal('balance_after', 12, 2);
            // مصدر الحركة، مثل Purchase أو Production Order
            $table->string('reference_type')->nullable();
            // ID العملية المرتبطة
            $table->unsignedBigInteger('reference_id')->nullable();
            // ملاحظات
            $table->text('notes')->nullable();
            // المستخدم الذي نفذ الحركة
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            // لتحسين البحث والتقارير
            $table->index(['item_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
