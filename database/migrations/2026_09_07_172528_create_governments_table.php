<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // جدول المحافظات: create_governments_table.php
        Schema::create('governments', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // بدون Auto Increment
            $table->string('name');
            $table->timestamps();
        });

        // جدول المناطق: create_areas_table.php
        Schema::create('areas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // بدون Auto Increment
            $table->unsignedBigInteger('government_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('government_id')->references('id')->on('governments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governments');
    }
};
