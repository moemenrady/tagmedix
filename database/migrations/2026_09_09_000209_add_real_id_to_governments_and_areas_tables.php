<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('governments', function (Blueprint $table) {
            $table->unsignedBigInteger('real_id')
                ->nullable()
                ->after('id')
                ->index();
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->unsignedBigInteger('real_id')
                ->nullable()
                ->after('id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('real_id');
        });

        Schema::table('governments', function (Blueprint $table) {
            $table->dropColumn('real_id');
        });
    }
};
