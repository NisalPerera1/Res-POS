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
            // Add PIN field if it doesn't exist
            if (!Schema::hasColumn('users', 'pin')) {
                $table->string('pin')->nullable()->after('password');
            }
            
            // Add status fields if they don't exist
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
            
            if (!Schema::hasColumn('users', 'is_clocked_in')) {
                $table->boolean('is_clocked_in')->default(false)->after('is_active');
            }
            
            // Add additional fields if they don't exist
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('is_clocked_in');
            }
            
            if (!Schema::hasColumn('users', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('profile_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('users', 'pin')) {
                $columnsToDrop[] = 'pin';
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $columnsToDrop[] = 'is_active';
            }
            if (Schema::hasColumn('users', 'is_clocked_in')) {
                $columnsToDrop[] = 'is_clocked_in';
            }
            if (Schema::hasColumn('users', 'profile_image')) {
                $columnsToDrop[] = 'profile_image';
            }
            if (Schema::hasColumn('users', 'emergency_contact')) {
                $columnsToDrop[] = 'emergency_contact';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
