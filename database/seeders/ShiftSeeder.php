<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shift;
use Carbon\Carbon;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        // Get active staff
        $staff = User::where('is_active', true)->get();
        
        if ($staff->isEmpty()) {
            return;
        }

        // Create some test shifts for this month
        $today = Carbon::today();
        
        foreach ($staff as $user) {
            // Create a few shifts for each staff member
            for ($i = 0; $i < 3; $i++) {
                $shiftDate = $today->copy()->subDays($i);
                
                // Skip if shift already exists for this date
                $existing = Shift::where('user_id', $user->id)
                    ->where('shift_date', $shiftDate->toDateString())
                    ->first();
                
                if (!$existing) {
                    Shift::create([
                        'user_id' => $user->id,
                        'shift_date' => $shiftDate->toDateString(),
                        'clock_in' => $shiftDate->copy()->setHour(9)->setMinute(0),
                        'clock_out' => $shiftDate->copy()->setHour(17)->setMinute(0),
                        'hours_worked' => 8,
                        'tips_collected' => rand(20, 100),
                        'service_charge_earned' => 0, // Will be calculated
                        'notes' => 'Test shift',
                        'status' => 'completed'
                    ]);
                }
            }
            
            // Create an active shift for today for some staff
            if ($user->id <= 2) {
                Shift::create([
                    'user_id' => $user->id,
                    'shift_date' => $today->toDateString(),
                    'clock_in' => $today->copy()->setHour(9)->setMinute(0),
                    'clock_out' => null,
                    'hours_worked' => null,
                    'tips_collected' => 0,
                    'service_charge_earned' => 0,
                    'notes' => 'Active shift',
                    'status' => 'active'
                ]);
            }
        }
        
        echo "Created test shifts for " . $staff->count() . " staff members\n";
    }
}
