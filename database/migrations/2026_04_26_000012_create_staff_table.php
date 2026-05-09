<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // Auto-generated: TDZ-001
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('nic')->nullable()->unique(); // National ID Card
            $table->string('address')->nullable();
            $table->date('joined_date');
            $table->enum('role', [
                'admin', 'manager', 'cashier',
                'waiter', 'kitchen', 'bartender', 'delivery'
            ]);
            $table->enum('salary_type', ['monthly', 'daily', 'hourly']);
            $table->decimal('base_salary', 10, 2)->default(0);
            // Service charge percentage assigned to this staff member.
            // The sum of all active staff percentages should equal 100.
            $table->decimal('service_charge_pct', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
