<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_finished_products', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Inventory Relations
            |--------------------------------------------------------------------------
            */

            // الصنف الأساسي
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            // الـ Variant الأساسي - اللون
            $table->foreignId('inventory_item_variant_id')
                ->constrained('inventory_item_variants')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Finished Product Identity
            |--------------------------------------------------------------------------
            */

            // كود داخلي مميز للمنتج الجاهز
            $table->string('product_code')->unique();


            /*
            |--------------------------------------------------------------------------
            | Product Configuration
            |--------------------------------------------------------------------------
            */

            $table->foreignId('pant_type_id')
                ->nullable()
                ->constrained('pant_types')
                ->nullOnDelete();

            $table->foreignId('type_id')
                ->nullable()
                ->constrained('types')
                ->nullOnDelete();

            $table->foreignId('style_id')
                ->nullable()
                ->constrained('styles')
                ->nullOnDelete();

            $table->foreignId('color_id')
                ->constrained('colors')
                ->restrictOnDelete();

            $table->foreignId('size_id')
                ->nullable()
                ->constrained('sizes')
                ->nullOnDelete();

            $table->foreignId('sleeve_type_id')
                ->nullable()
                ->constrained('sleeve_types')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Stock / Cost
            |--------------------------------------------------------------------------
            */

            // عدد القطع الموجودة من هذا المنتج
            $table->decimal('quantity', 12, 3)
                ->default(0);

            // تكلفة القطعة الواحدة
            $table->decimal('unit_cost', 14, 2)
                ->default(0);

            // إجمالي تكلفة الكمية
            $table->decimal('total_cost', 16, 2)
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Extra Information
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'inventory_item_id',
                    'inventory_item_variant_id',
                ],
                'ifp_item_variant_idx'
            );

            $table->index('pant_type_id');
            $table->index('type_id');
            $table->index('style_id');
            $table->index('color_id');
            $table->index('size_id');
            $table->index('sleeve_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_finished_products');
    }
};
