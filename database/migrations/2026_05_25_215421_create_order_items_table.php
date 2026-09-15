<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();
            // العلاقات الجديدة
            $table->foreignId('style_id')
                ->nullable()
                ->constrained('styles')
                ->nullOnDelete();
            $table->foreignId('type_id')
                ->nullable()
                ->constrained('types')
                ->nullOnDelete();
            $table->foreignId('pant_type_id')
                ->nullable()
                ->constrained('pant_types')
                ->nullOnDelete();
            $table->foreignId('sleeve_type_id')
                ->nullable()
                ->constrained('sleeve_types')
                ->nullOnDelete();
            $table->foreignId('color_id')
                ->nullable()
                ->constrained('colors')
                ->nullOnDelete();
            $table->foreignId('size_id')
                ->nullable()
                ->constrained('sizes')
                ->nullOnDelete();

            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->boolean('is_shipped_paid')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
