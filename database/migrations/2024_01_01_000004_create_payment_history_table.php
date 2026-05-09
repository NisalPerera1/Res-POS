<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff');
            $table->string('employee_name');
            $table->enum('type', ['Salary', 'Bonus', 'Advance', 'Deduction']);
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['Bank Transfer', 'Cash', 'Check']);
            $table->string('reference')->unique();
            $table->enum('status', ['Completed', 'Pending', 'Failed'])->default('Completed');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['staff_id', 'payment_date']);
            $table->index('reference');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_history');
    }
};
