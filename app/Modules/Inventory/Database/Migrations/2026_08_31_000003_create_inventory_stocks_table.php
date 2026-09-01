<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            $table->foreignId('inventory_item_variant_id')
                ->nullable()
                ->constrained('inventory_item_variants')
                ->restrictOnDelete();

          
            $table->decimal('quantity', 12, 3)->default(0);

            $table->enum('unit_type', [
                'meter',
                'kg',
                'piece',
                'quantity',
            ]);

            $table->decimal('unit_cost', 15, 4)->default(0);

            $table->decimal('total_cost', 15, 4)->default(0);

            $table->timestamps();

            $table->unique(
                [
                    'inventory_item_id',
                    'inventory_item_variant_id',
                ],
                'inventory_stock_unique'
            );

            $table->index('inventory_item_id');
            $table->index('inventory_item_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};