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
        Schema::create('staff_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['salary_advance', 'emergency_loan', 'personal_loan', 'other']);
            $table->text('reason')->nullable();
            $table->date('request_date');
            $table->date('approved_date')->nullable();
            $table->date('disbursement_date')->nullable();
            
            // Repayment terms
            $table->enum('repayment_method', ['salary_deduction', 'cash', 'bank_transfer', 'installment'])->default('salary_deduction');
            $table->integer('installment_months')->default(1);
            $table->decimal('monthly_installment', 10, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0); // Annual interest rate
            $table->decimal('total_interest', 10, 2)->default(0);
            $table->decimal('total_repayable', 12, 2);
            
            // Status tracking
            $table->enum('status', ['pending', 'approved', 'rejected', 'disbursed', 'partially_paid', 'fully_paid'])->default('pending');
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('disbursed_by')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            
            // Repayment tracking
            $table->decimal('amount_repaid', 12, 2)->default(0);
            $table->decimal('balance_remaining', 12, 2);
            $table->date('next_payment_date')->nullable();
            
            // Attachments
            $table->string('attachment')->nullable(); // Supporting documents
            
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'status']);
            $table->index(['status', 'request_date']);
            $table->index(['disbursement_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_advances');
    }
};
