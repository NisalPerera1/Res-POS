<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff');
            $table->string('employee_name');
            $table->string('role');
            $table->enum('status', ['Pending', 'Paid', 'Processed'])->default('Pending');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('advance_deduction', 10, 2)->default(0);
            $table->decimal('leave_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->enum('method', ['Bank Transfer', 'Cash', 'Check'])->default('Bank Transfer');
            $table->string('payment_reference')->nullable();
            $table->date('payroll_date');
            $table->string('month_year'); // Format: 2024-01
            $table->timestamps();
            
            $table->index(['staff_id', 'month_year']);
            $table->index('payroll_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll');
    }
};
