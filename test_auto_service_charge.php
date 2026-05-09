<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing automatic service charge collection and equal distribution...\n";
    
    // Test the getServiceChargeDistribution method
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => 4,
        'year' => 2026
    ]);
    
    echo "Testing service charge distribution for April 2026...\n";
    
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response = $controller->getServiceChargeDistribution($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content:\n";
    echo $response->getContent() . "\n";
    
    // Parse the response to verify equal distribution
    $data = json_decode($response->getContent(), true);
    
    if (isset($data['distribution']) && !empty($data['distribution'])) {
        $staffCount = count($data['distribution']);
        $totalServiceCharge = $data['total_service_charge'];
        
        echo "\nVerification:\n";
        echo "- Total service charge collected: Rs. {$totalServiceCharge}\n";
        echo "- Number of active staff: {$staffCount}\n";
        
        if ($staffCount > 0) {
            $expectedPercentage = round(100 / $staffCount, 2);
            $expectedShare = round($totalServiceCharge / $staffCount, 2);
            
            echo "- Expected percentage per staff: {$expectedPercentage}%\n";
            echo "- Expected share per staff: Rs. {$expectedShare}\n";
            
            // Verify each staff member gets equal distribution
            $allEqual = true;
            foreach ($data['distribution'] as $staff) {
                $actualPercentage = $staff['service_charge_pct'];
                $actualShare = $staff['share_amount'];
                
                echo "- Staff {$staff['name']}: {$actualPercentage}% = Rs. {$actualShare}\n";
                
                if ($actualPercentage != $expectedPercentage || $actualShare != $expectedShare) {
                    $allEqual = false;
                    echo "  ERROR: Unequal distribution detected!\n";
                }
            }
            
            if ($allEqual) {
                echo "\n✅ SUCCESS: Equal distribution verified!\n";
            } else {
                echo "\n❌ ERROR: Unequal distribution found!\n";
            }
        }
    } else {
        echo "\nNo distribution data found. Checking for active staff...\n";
        
        $activeStaff = \App\Models\Staff::where('is_active', true)->get();
        echo "Active staff count: " . $activeStaff->count() . "\n";
        
        if ($activeStaff->isEmpty()) {
            echo "No active staff found. Creating test staff...\n";
            
            \App\Models\Staff::create([
                'name' => 'Test Staff 1',
                'phone' => '1234567890',
                'email' => 'test1@example.com',
                'joined_date' => now(),
                'role' => 'waiter',
                'salary_type' => 'monthly',
                'base_salary' => 20000,
                'is_active' => true,
                'service_charge_pct' => 0,
            ]);
            
            \App\Models\Staff::create([
                'name' => 'Test Staff 2',
                'phone' => '1234567891',
                'email' => 'test2@example.com',
                'joined_date' => now(),
                'role' => 'cashier',
                'salary_type' => 'monthly',
                'base_salary' => 18000,
                'is_active' => true,
                'service_charge_pct' => 0,
            ]);
            
            echo "Created 2 test staff members. Please run the test again.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
