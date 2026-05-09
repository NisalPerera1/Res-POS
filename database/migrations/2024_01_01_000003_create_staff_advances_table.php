<?php

// ─────────────────────────────────────────────────────────────
// 2024_01_01_000003_create_staff_advances_table.php
// ─────────────────────────────────────────────────────────────
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'deducted'])->default('pending');
            // Which month's payroll this will be deducted from
            $table->tinyInteger('deduct_month')->nullable()->unsigned();
            $table->year('deduct_year')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'date']);
            $table->index(['staff_id', 'deduct_month', 'deduct_year']);
        });

        // ─────────────────────────────────────────────────────────────
        // Attendance (optional but recommended for payroll accuracy)
        // ─────────────────────────────────────────────────────────────
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            // Computed: check_out - check_in in minutes
            $table->unsignedSmallInteger('worked_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'date']);
            $table->index(['staff_id', 'date']);
        });

        // ─────────────────────────────────────────────────────────────
        // Monthly service charge pool collected from table orders
        // ─────────────────────────────────────────────────────────────
        Schema::create('monthly_service_charges', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month')->unsigned();  // 1–12
            $table->year('year');
            // Total service charge collected from all orders this month
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['month', 'year']);
        });

        // ─────────────────────────────────────────────────────────────
        // Per-staff service charge distribution for a given month
        // ─────────────────────────────────────────────────────────────
        Schema::create('staff_service_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('monthly_service_charge_id')
                ->constrained('monthly_service_charges')
                ->cascadeOnDelete();
            $table->tinyInteger('month')->unsigned();
            $table->year('year');
            // Snapshot of pct at time of calculation (staff pct may change later)
            $table->decimal('service_charge_pct', 5, 2);
            $table->decimal('share_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['staff_id', 'month', 'year']);
        });

        // ─────────────────────────────────────────────────────────────
        // Payrolls
        // ─────────────────────────────────────────────────────────────
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->tinyInteger('month')->unsigned();
            $table->year('year');

            // --- Earnings ---
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0); // computed

            // --- Deductions ---
            $table->decimal('leave_deductions', 10, 2)->default(0);  // unpaid leave
            $table->decimal('advance_deductions', 10, 2)->default(0); // salary advances
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0); // computed

            // --- Net ---
            $table->decimal('net_pay', 10, 2)->default(0); // computed

            // --- Meta ---
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, bank transfer
            $table->text('notes')->nullable();

            // Snapshot of working days used for calculation
            $table->unsignedSmallInteger('working_days')->default(0);
            $table->unsignedSmallInteger('days_worked')->default(0);
            $table->unsignedSmallInteger('leaves_taken')->default(0);
            $table->unsignedSmallInteger('half_leaves_taken')->default(0);

            $table->timestamps();

            // One payroll per staff per month
            $table->unique(['staff_id', 'month', 'year']);
            $table->index(['staff_id', 'month', 'year']);
            $table->index(['status', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('staff_service_charges');
        Schema::dropIfExists('monthly_service_charges');
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('staff_advances');
    }
};
