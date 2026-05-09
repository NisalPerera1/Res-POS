<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff');
            $table->enum('type', ['Salary Advance', 'Emergency Advance', 'Personal Loan']);
            $table->decimal('amount', 10, 2);
            $table->decimal('remaining_balance', 10, 2);
            $table->decimal('monthly_deduction', 10, 2);
            $table->date('date');
            $table->enum('status', ['Active', 'Completed', 'Defaulted'])->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('advances');
    }
};
