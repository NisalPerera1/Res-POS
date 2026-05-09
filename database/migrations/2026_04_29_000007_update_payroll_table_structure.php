<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('payrolls');
        
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
        Schema::dropIfExists('payrolls');
    }
};
