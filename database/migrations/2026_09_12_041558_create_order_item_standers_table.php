<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_stander', function (Blueprint $table) {
            $table->id();

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
                ->nullable()
                ->constrained('colors')
                ->nullOnDelete();

            $table->foreignId('size_id')
                ->nullable()
                ->constrained('sizes')
                ->nullOnDelete();

            $table->foreignId('sleeve_type_id')
                ->nullable()
                ->constrained('sleeve_types')
                ->nullOnDelete();

            $table->decimal('price', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('pant_type_id');
            $table->index('type_id');
            $table->index('style_id');
            $table->index('color_id');
            $table->index('size_id');
            $table->index('sleeve_type_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_stander');
    }
};