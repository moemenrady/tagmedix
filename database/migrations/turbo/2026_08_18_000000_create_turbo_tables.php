<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->string('turbo_id')->nullable()->index();
            $table->string('remote_order_id')->nullable()->index();
            $table->string('tracking_number')->nullable()->index();
            $table->string('receiver')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('government')->nullable();
            $table->string('area')->nullable();
            $table->string('address')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('weight', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('order_summary')->nullable();
            $table->text('return_summary')->nullable();
            $table->decimal('return_amount', 12, 2)->default(0);
            $table->string('status')->nullable()->index();
            $table->string('status_text')->nullable();
            $table->string('internal_status')->nullable()->index();
            $table->string('delivery_type')->nullable()->index();
            $table->boolean('is_order')->default(false)->index();
            $table->boolean('is_fragile')->default(false)->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('endpoint');
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->unsignedInteger('status_code')->nullable();
            $table->float('execution_time')->nullable();
            $table->text('exception')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('shipment_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_status_histories');
        Schema::dropIfExists('shipment_logs');
        Schema::dropIfExists('shipments');
    }
};
