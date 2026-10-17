<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();
            // Supervisor
            $table->foreignId('assigned_to')
                ->constrained('users')
                ->cascadeOnDelete();
            // Admin
            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('instruction')->nullable();
            $table->date('required_date')->nullable();
            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'urgent'
            ])->default('medium');
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_tasks');
    }
};