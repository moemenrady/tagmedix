<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('action');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('device')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
            $table->index('order_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_histories');
    }
};
