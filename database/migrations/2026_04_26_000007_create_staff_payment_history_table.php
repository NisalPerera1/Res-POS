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
        Schema::create('staff_payment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_id')->nullable()->constrained('staff_payrolls')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('gross_pay', 12, 2);
            $table->decimal('total_deductions', 12, 2);
            $table->decimal('net_pay', 12, 2);
            $table->decimal('base_salary', 12, 2);
            $table->decimal('overtime_pay', 8, 2)->default(0);
            $table->decimal('service_charge_share', 8, 2)->default(0);
            $table->decimal('tips_collected', 8, 2)->default(0);
            $table->decimal('bonus', 8, 2)->default(0);
            $table->decimal('allowances', 8, 2)->default(0);
            $table->decimal('advance_deductions', 8, 2)->default(0);
            $table->decimal('leave_deductions', 8, 2)->default(0);
            $table->decimal('other_deductions', 8, 2)->default(0);
            $table->decimal('tax_deductions', 8, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'other'])->default('bank_transfer');
            $table->string('transaction_reference')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'year', 'month']);
            $table->index(['year', 'month']);
            $table->index('payment_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payment_history');
    }
};
