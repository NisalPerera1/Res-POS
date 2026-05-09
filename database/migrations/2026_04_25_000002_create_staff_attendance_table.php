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
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday', 'leave'])->default('present');
            $table->decimal('tips_collected', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_overtime')->default(false);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->string('approved_by')->nullable(); // Staff ID who approved
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate attendance records
            $table->unique(['staff_id', 'attendance_date']);
            
            // Indexes for performance
            $table->index(['attendance_date', 'status']);
            $table->index('staff_id');
            $table->index(['attendance_date', 'staff_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
