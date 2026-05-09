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
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sick', 'annual', 'maternity', 'paternity', 'unpaid', 'emergency', 'other']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_count');
            $table->enum('duration_type', ['full_day', 'half_day'])->default('full_day');
            $table->text('reason')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Leave balance tracking
            $table->decimal('annual_balance_before', 5, 2)->default(0);
            $table->decimal('annual_balance_after', 5, 2)->default(0);
            $table->boolean('paid_leave')->default(true);
            
            // Attachments
            $table->string('attachment')->nullable(); // Medical certificate, etc.
            
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_leaves');
    }
};
