<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Inventory hierarchy
            |--------------------------------------------------------------------------
            */

            // Main inventory item
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            // Specific variant / color
            $table->foreignId('inventory_item_variant_id')
                ->nullable()
                ->constrained('inventory_item_variants')
                ->restrictOnDelete();

            // Physical unit / roll / batch
            $table->foreignId('inventory_unit_id')
                ->nullable()
                ->constrained('inventory_units')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [
                'receipt',
                'issue',
                'adjustment_in',
                'adjustment_out',
                'return',
                'transfer_in',
                'transfer_out',
                'damage',
            ]);

            $table->decimal('quantity', 12, 3);

            $table->enum('unit_type', [
                'meter',
                'kg',
                'piece',
                'quantity',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $table->decimal('unit_cost', 15, 4)->default(0);

            $table->decimal('total_cost', 15, 4)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Polymorphic Reference
            |--------------------------------------------------------------------------
            */

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            */

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('transaction_date');

            $table->timestamp('created_at')->useCurrent();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['inventory_item_id', 'transaction_date'],
                'inventory_transactions_item_date_index'
            );

            $table->index(
                ['inventory_item_variant_id', 'transaction_date'],
                'inventory_transactions_variant_date_index'
            );

            $table->index(
                ['inventory_unit_id', 'transaction_date'],
                'inventory_transactions_unit_date_index'
            );

            $table->index(
                ['reference_type', 'reference_id'],
                'inventory_transactions_reference_index'
            );

            $table->index('type');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};