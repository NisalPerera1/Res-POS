<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\ServiceCharge;
use App\Models\StaffLeave;

class StaffSeeder extends Seeder
{
    public function run()
    {
        // Create sample staff
        $staff1 = Staff::create([
            'name' => 'John Doe',
            'role' => 'Manager',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'base_salary' => 3000.00,
            'daily_rate' => 100.00,
            'status' => 'active'
        ]);

        $staff2 = Staff::create([
            'name' => 'Jane Smith',
            'role' => 'Cashier',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'base_salary' => 2000.00,
            'daily_rate' => 66.67,
            'status' => 'active'
        ]);

        $staff3 = Staff::create([
            'name' => 'Mike Johnson',
            'role' => 'Waiter',
            'email' => 'mike@example.com',
            'phone' => '1122334455',
            'base_salary' => 1800.00,
            'daily_rate' => 60.00,
            'status' => 'active'
        ]);

        // Create sample service charge for current month
        ServiceCharge::create([
            'total_amount' => 1500.00,
            'source' => 'pos_orders',
            'month' => now()->month,
            'year' => now()->year,
            'distributed_amount' => 0,
            'remaining_amount' => 1500.00
        ]);

        // Create sample leaves
        StaffLeave::create([
            'staff_id' => $staff1->id,
            'date' => now()->subDays(5)->format('Y-m-d'),
            'type' => 'full_day',
            'reason' => 'Sick leave'
        ]);

        StaffLeave::create([
            'staff_id' => $staff2->id,
            'date' => now()->subDays(3)->format('Y-m-d'),
            'type' => 'half_day',
            'reason' => 'Personal appointment'
        ]);

        $this->command->info('Sample staff data created successfully!');
    }
}
