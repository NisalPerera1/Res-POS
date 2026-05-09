<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('total_leave_deductions', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('service_charge_share', 10, 2)->default(0);
            $table->decimal('final_salary', 10, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
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
