<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payrolls');

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->enum('salary_type', ['monthly', 'piece_rate'])->default('monthly');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('piece_rate_earnings', 12, 2)->default(0);
            $table->unsignedInteger('completed_pieces_count')->default(0);
            $table->decimal('bonuses_amount', 12, 2)->default(0);
            $table->decimal('deductions_amount', 12, 2)->default(0);
            $table->decimal('advances_deducted', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'period_month', 'period_year']);
        });

        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['advance', 'bonus', 'deduction'])->default('advance'); // advance=سلفة, bonus=مكافأة, deduction=خصم
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->boolean('is_settled')->default(false);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
        Schema::dropIfExists('payrolls');

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
