<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_item_variants', function (Blueprint $table) {
            $table->id();

            // Parent inventory item
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            // Variant color
            $table->foreignId('color_id')
                ->constrained('colors')
                ->restrictOnDelete();

            // Stock Keeping Unit
            $table->string('sku')->unique();

            // Variant status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate color variants for the same item
            $table->unique(
                ['inventory_item_id', 'color_id'],
                'inventory_item_variants_item_color_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_variants');
    }
};