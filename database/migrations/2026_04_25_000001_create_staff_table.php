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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Link to authentication
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('join_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            
            // Job details
            $table->enum('role', ['admin', 'manager', 'cashier', 'waiter', 'kitchen', 'bartender', 'delivery']);
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern']);
            $table->enum('salary_type', ['monthly', 'daily', 'hourly']);
            $table->decimal('base_salary', 12, 2)->default(0); // Monthly salary
            $table->decimal('daily_wage', 8, 2)->default(0);
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->decimal('service_charge_pct', 5, 2)->default(0); // Service charge percentage
            
            // Banking details
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_branch')->nullable();
            
            // Status and metadata
            $table->boolean('is_active')->default(true);
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('profile_image')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('employee_id');
            $table->index('is_active');
            $table->index('role');
            $table->index(['join_date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
