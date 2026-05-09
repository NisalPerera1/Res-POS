<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Get all users that should be migrated to staff
            $users = User::all();
            
            foreach ($users as $user) {
                // Check if staff record already exists for this user
                $existingStaff = Staff::where('user_id', $user->id)->first();
                if ($existingStaff) {
                    continue; // Skip if already migrated
                }
                
                // Create staff record from user data
                $staff = Staff::create([
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id ?? $this->generateEmployeeId(),
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'address' => $user->address,
                    'date_of_birth' => $user->date_of_birth,
                    'join_date' => $user->join_date ?? now()->toDateString(),
                    'gender' => null, // Not in original users table
                    'emergency_contact' => $user->emergency_contact,
                    'emergency_phone' => $user->emergency_phone,
                    'role' => $user->role,
                    'employment_type' => 'full_time', // Default value
                    'salary_type' => $user->salary_type ?? 'monthly',
                    'base_salary' => $user->base_salary ?? 0,
                    'daily_wage' => $user->daily_wage ?? 0,
                    'hourly_rate' => $user->hourly_rate ?? 0,
                    'service_charge_pct' => $user->service_charge_pct ?? 0,
                    'bank_name' => $user->bank_name,
                    'bank_account' => $user->bank_account,
                    'bank_branch' => null, // Not in original users table
                    'is_active' => $user->is_active ?? true,
                    'termination_date' => null,
                    'termination_reason' => null,
                    'notes' => $user->notes,
                    'profile_image' => $user->profile_image,
                ]);
                
                $this->command->info("Migrated user: {$user->name} to staff record");
            }
            
            // Update employee_id for any staff records that don't have one
            $staffWithoutId = Staff::whereNull('employee_id')->get();
            foreach ($staffWithoutId as $staff) {
                $staff->update(['employee_id' => $this->generateEmployeeId()]);
            }
            
            $this->command->info('Staff migration completed successfully!');
            
        } catch (\Exception $e) {
            $this->command->error('Error during staff migration: ' . $e->getMessage());
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
    
    /**
     * Generate a unique employee ID
     */
    private function generateEmployeeId(): string
    {
        $prefix = 'EMP';
        $year = now()->year;
        
        // Get the highest sequence number for this year
        $lastStaff = Staff::where('employee_id', 'like', $prefix . $year . '%')
            ->orderBy('employee_id', 'desc')
            ->first();
        
        if ($lastStaff) {
            $lastSequence = intval(substr($lastStaff->employee_id, -4));
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
