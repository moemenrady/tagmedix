<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            // Basic
            $table->string('name');

            $table->string('sku')->unique();

            $table->text('description')->nullable();

            // Pricing
            $table->decimal('selling_price', 10, 2);

            $table->decimal('cost_price', 10, 2)
                ->default(0);

            // Product Type
            $table->enum('type', [
                'hoodie',
                'tshirt',
                'pants',
                'shirt',
                'custom'
            ])->default('custom');

            // Sleeve
            $table->enum('sleeve_type', [
                'full',
                'half',
                'none'
            ])->nullable();

            // Design
            $table->string('design_code')
                ->nullable();

            // Image
            $table->string('image')
                ->nullable();

            // Visibility
            $table->boolean('is_active')
                ->default(true);

            $table->boolean('allow_stock_sale')
                ->default(true);

            // Stats
            $table->integer('total_sales')
                ->default(0);

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
