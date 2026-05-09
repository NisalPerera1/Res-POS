<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop tables in reverse dependency order
        Schema::dropIfExists('staff_service_charges');
        Schema::dropIfExists('monthly_service_charges');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('staff_advances');
        Schema::dropIfExists('staff_leaves');
        Schema::dropIfExists('staff');
        
        // Recreate staff table
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('nic')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_date');
            $table->string('role');
            $table->enum('salary_type', ['monthly', 'daily'])->default('monthly');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('service_charge_pct', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['role']);
            $table->index(['employee_id']);
        });
        
        // Recreate staff_leaves table
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['full_day', 'half_day'])->default('full_day');
            $table->enum('half_day_period', ['morning', 'afternoon'])->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            
            $table->index(['staff_id', 'date']);
            $table->index(['type']);
        });
        
        // Recreate staff_advances table
        Schema::create('staff_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->tinyInteger('deduct_month')->nullable();
            $table->year('deduct_year')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['staff_id', 'status']);
            $table->index(['deduct_month', 'deduct_year']);
        });
        
        // Recreate staff_attendance table
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('worked_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['staff_id', 'date']);
            $table->index(['date']);
            $table->index(['status']);
        });
        
        // Recreate monthly_service_charges table
        Schema::create('monthly_service_charges', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
            
            $table->unique(['month', 'year']);
            $table->index(['month', 'year']);
        });
        
        // Recreate staff_service_charges table
        Schema::create('staff_service_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('monthly_service_charge_id')->constrained('monthly_service_charges')->onDelete('cascade');
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('service_charge_pct', 5, 2);
            $table->decimal('share_amount', 10, 2);
            $table->timestamps();
            
            $table->unique(['staff_id', 'monthly_service_charge_id']);
            $table->index(['staff_id', 'month', 'year']);
        });
        
        // Recreate payrolls table
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2);
            $table->decimal('leave_deductions', 10, 2)->default(0);
            $table->decimal('advance_deductions', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_pay', 10, 2);
            $table->enum('status', ['draft', 'pending', 'approved', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->integer('working_days')->default(30);
            $table->integer('days_worked')->default(0);
            $table->integer('leaves_taken')->default(0);
            $table->integer('half_leaves_taken')->default(0);
            $table->timestamps();
            
            $table->unique(['staff_id', 'month', 'year']);
            $table->index(['month', 'year']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_service_charges');
        Schema::dropIfExists('monthly_service_charges');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('staff_advances');
        Schema::dropIfExists('staff_leaves');
        Schema::dropIfExists('staff');
    }
};
