<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            // Internal item code
            $table->string('code')->unique();
           
            // Item name
            $table->string('name');
          
            // Item category
            $table->enum('category', [
                'fabric',
                'accessory',
                'finished_product',
            ]);
         
            // Measurement / quantity unit
            $table->enum('unit_type', [
                'meter',
                'kg',
                'piece',
                'quantity',
            ]);
         
            // How stock is tracked
            $table->enum('stock_tracking_type', [
                'bulk',
                'individual',
            ]);
         
            // Item status
            $table->boolean('is_active')->default(true);
          
            // Reorder level
            $table->decimal('minimum_stock', 12, 3)->default(0);
            
            // Additional notes
            $table->text('description')->nullable();
           
            // User who created the item
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
          
                $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};