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
        Schema::table('users', function (Blueprint $table) {
            // Add wage fields for flexible wage management
            $table->decimal('daily_wage', 8, 2)->nullable()->after('hourly_rate');
            $table->decimal('half_day_wage', 8, 2)->nullable()->after('daily_wage');
            
            // Add wage effective date for historical tracking
            $table->date('wage_effective_date')->nullable()->after('half_day_wage');
            
            // Add indexes
            $table->index(['salary_type', 'daily_wage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['salary_type', 'daily_wage']);
            $table->dropColumn(['daily_wage', 'half_day_wage', 'wage_effective_date']);
        });
    }
};
