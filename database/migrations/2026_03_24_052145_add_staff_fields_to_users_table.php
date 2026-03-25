<?php
// Run: php artisan make:migration add_staff_fields_to_users_table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Extend users table ─────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique()->nullable()->after('name');
            $table->string('pin')->nullable()->after('password'); // Add PIN field
            $table->string('phone')->nullable()->after('avatar');
            $table->string('email')->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('address');
            $table->date('join_date')->nullable()->after('date_of_birth');
            $table->enum('salary_type', ['monthly', 'daily', 'hourly'])->default('monthly')->after('join_date');
            $table->decimal('base_salary', 10, 2)->default(0)->after('salary_type');
            $table->decimal('hourly_rate', 8, 2)->default(0)->after('base_salary');
            $table->decimal('service_charge_pct', 5, 2)->default(0)->after('hourly_rate'); // their % share
            $table->string('bank_name')->nullable()->after('service_charge_pct');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->text('notes')->nullable()->after('bank_account');
            $table->boolean('is_active')->default(true)->after('notes'); // Add active status
            $table->boolean('is_clocked_in')->default(false)->after('is_active'); // Add clock status
            $table->string('profile_image')->nullable()->after('is_clocked_in'); // Add profile image
            $table->string('emergency_contact')->nullable()->after('profile_image'); // Add emergency contact
            // Extend role enum
            $table->enum('role', ['admin','manager','cashier','waiter','kitchen','bartender','delivery'])
                  ->default('cashier')->change();
        });

        // ── Shifts table ───────────────────────────────
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('shift_date');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->decimal('hours_worked', 6, 2)->nullable();
            $table->decimal('tips_collected', 10, 2)->default(0);
            $table->decimal('service_charge_earned', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'completed', 'absent'])->default('active');
            $table->timestamps();
        });

        // ── Payroll table ──────────────────────────────
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month');   // 1–12
            $table->integer('days_worked')->default(0);
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('service_charge_share', 10, 2)->default(0);
            $table->decimal('tips', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'year', 'month']);
        });

        // ── Leave requests table ───────────────────────
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['sick', 'annual', 'unpaid', 'other'])->default('annual');
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('days')->default(1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('shifts');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id', 'pin', 'phone', 'email', 'address', 'date_of_birth',
                'join_date', 'salary_type', 'base_salary', 'hourly_rate',
                'service_charge_pct', 'bank_name', 'bank_account', 'notes',
                'is_active', 'is_clocked_in', 'profile_image', 'emergency_contact',
            ]);
        });
    }
};