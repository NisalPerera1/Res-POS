<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Run this migration if your staff_advances table was created by an older
 * migration that is missing deduct_month, deduct_year, or notes columns.
 *
 * Safe to run even if columns already exist — each addition is guarded.
 *
 * php artisan make:migration add_missing_columns_to_staff_advances_table
 * Then paste this content in, and run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_advances', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_advances', 'deduct_month')) {
                $table->tinyInteger('deduct_month')->unsigned()->nullable()->after('status');
            }
            if (!Schema::hasColumn('staff_advances', 'deduct_year')) {
                $table->year('deduct_year')->nullable()->after('deduct_month');
            }
            if (!Schema::hasColumn('staff_advances', 'notes')) {
                $table->text('notes')->nullable()->after('deduct_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_advances', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['deduct_month', 'deduct_year', 'notes'],
                fn($col) => Schema::hasColumn('staff_advances', $col)
            ));
        });
    }
};
