<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff');
            $table->string('employee_name');
            $table->enum('leave_category', ['Casual', 'Sick', 'Annual', 'Maternity', 'Paternity']);
            $table->enum('paid_type', ['Paid', 'Unpaid', 'Half Paid']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days');
            $table->text('reason');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['staff_id', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
