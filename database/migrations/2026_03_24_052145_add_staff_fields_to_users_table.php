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
            $table->boolean('is_clocked_in')->default(false)->after('notes'); // Add clock status
            $table->string('profile_image')->nullable()->after('is_clocked_in'); // Add profile image
            $table->string('emergency_contact')->nullable()->after('profile_image'); // Add emergency contact
            // Extend role enum
            $table->enum('role', ['admin','manager','cashier','waiter','kitchen','bartender','delivery'])
                  ->default('cashier')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id', 'phone', 'email', 'address', 'date_of_birth',
                'join_date', 'salary_type', 'base_salary', 'hourly_rate',
                'service_charge_pct', 'bank_name', 'bank_account', 'notes',
                'is_clocked_in', 'profile_image', 'emergency_contact',
            ]);
        });
    }
};