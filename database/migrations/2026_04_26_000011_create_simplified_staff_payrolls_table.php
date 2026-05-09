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
            $table->decimal('base_salary', 12, 2);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('leave_deductions', 8, 2)->default(0);
            $table->decimal('bonus', 8, 2)->default(0);
            $table->decimal('other_deductions', 8, 2)->default(0);
            $table->decimal('gross_pay', 12, 2);
            $table->decimal('total_deductions', 8, 2);
            $table->decimal('net_pay', 12, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->timestamps();
            
            $table->unique(['staff_id', 'year', 'month']);
            $table->index(['year', 'month']);
            $table->index('status');
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
