<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();

            // Variant this physical unit belongs to
            $table->foreignId('inventory_item_variant_id')
                ->constrained('inventory_item_variants')
                ->cascadeOnDelete();

            // Physical unit identifier
            $table->string('unit_code')->unique();

            // Quantity contained in this unit
            $table->decimal('quantity', 12, 3);

            // Unit of measurement
            $table->enum('unit_type', [
                'meter',
                'kg',
                'piece',
                'quantity',
            ]);

            // Current unit status
            $table->enum('status', [
                'available',
                'issued',
                'consumed',
                'damaged',
                'reserved',
                'returned',
            ])->default('available');


            // Cost
            $table->decimal('unit_cost', 15, 4)->default(0);

            $table->decimal('total_cost', 15, 4)->default(0);

            // Receiving information
            $table->timestamp('received_at')->nullable();

       

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Useful indexes
            $table->index(
                ['inventory_item_variant_id', 'status'],
                'inventory_units_variant_status_index'
            );

            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_units');
    }
};