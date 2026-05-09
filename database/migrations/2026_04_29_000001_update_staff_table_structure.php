<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('staff');
        
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
    }

    public function down()
    {
        Schema::dropIfExists('staff');
    }
};
