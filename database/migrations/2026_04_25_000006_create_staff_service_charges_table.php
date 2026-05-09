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
        Schema::create('staff_service_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            
            // Service charge calculation
            $table->decimal('total_service_charge_collected', 12, 2)->default(0);
            $table->decimal('staff_share_percentage', 5, 2)->default(0); // Individual percentage
            $table->decimal('staff_share_amount', 12, 2)->default(0);
            $table->decimal('equal_share_amount', 12, 2)->default(0); // Equal split amount
            $table->decimal('final_share_amount', 12, 2)->default(0); // Final amount after calculation
            
            // Calculation details
            $table->integer('active_staff_count')->default(0); // Total active staff that month
            $table->integer('days_worked')->default(0); // Days this staff worked
            $table->decimal('hours_worked', 8, 2)->default(0); // Hours this staff worked
            $table->enum('calculation_method', ['equal_split', 'percentage_based', 'hours_based', 'days_based'])->default('equal_split');
            
            // Status
            $table->enum('status', ['pending', 'calculated', 'approved', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate records
            $table->unique(['staff_id', 'year', 'month']);
            
            // Indexes
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
        Schema::dropIfExists('staff_service_charges');
    }
};
