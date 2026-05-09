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
        Schema::create('staff_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            
            // Attendance summary
            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('days_leave')->default(0);
            $table->integer('days_holiday')->default(0);
            $table->decimal('total_hours_worked', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            
            // Salary components
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('service_charge_share', 12, 2)->default(0);
            $table->decimal('tips_collected', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            
            // Deductions
            $table->decimal('advance_deductions', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('tax_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            
            // Totals
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            
            // Status and metadata
            $table->enum('status', ['draft', 'review', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // cash, bank_transfer, check
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate payroll records
            $table->unique(['staff_id', 'year', 'month']);
            
            // Indexes for performance
            $table->index(['year', 'month']);
            $table->index('status');
            $table->index(['staff_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payrolls');
    }
};
