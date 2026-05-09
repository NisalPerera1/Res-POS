<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing payroll generation with monthly service charge distribution...\n";
    
    $month = 4;
    $year = 2026;
    
    // Get service charge from orders for the month
    $monthlyServiceChargeTotal = \DB::table('orders')
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->where('payment_status', 'paid')
        ->whereNotNull('table_id')
        ->sum('tax_amount');
    
    echo "Monthly service charge from orders: Rs. {$monthlyServiceChargeTotal}\n";
    
    // Get active staff count
    $activeStaffCount = \App\Models\Staff::where('is_active', true)->count();
    echo "Active staff count: {$activeStaffCount}\n";
    
    if ($activeStaffCount > 0 && $monthlyServiceChargeTotal > 0) {
        $expectedServiceChargePerStaff = round($monthlyServiceChargeTotal / $activeStaffCount, 2);
        echo "Expected service charge per staff: Rs. {$expectedServiceChargePerStaff}\n";
    }
    
    // Test payroll generation
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => $month,
        'year' => $year,
        'staff_ids' => [1, 2] // Test with first 2 staff members
    ]);
    
    echo "\nGenerating payroll for April 2026...\n";
    
    // Delete existing payroll to allow regeneration
    \App\Models\Payroll::whereIn('staff_id', [1, 2])
        ->where('month', $month)
        ->where('year', $year)
        ->delete();
    
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response = $controller->generatePayroll($request);
    
    echo "Payroll generation response: " . $response->getStatusCode() . "\n";
    echo $response->getContent() . "\n";
    
    // Parse response to verify service charge distribution
    $data = json_decode($response->getContent(), true);
    
    if (!empty($data['payrolls'])) {
        echo "\nVerification:\n";
        foreach ($data['payrolls'] as $payroll) {
            echo "- Staff ID: {$payroll['staff_id']}, Service Charge: Rs. {$payroll['service_charge']}\n";
            
            if ($payroll['service_charge'] == $expectedServiceChargePerStaff) {
                echo "  ✅ Correct service charge distribution!\n";
            } else {
                echo "  ❌ Incorrect service charge distribution!\n";
            }
        }
        
        // Check if staff service charge records were created
        echo "\nStaff Service Charge Records:\n";
        $staffServiceCharges = \App\Models\StaffServiceCharge::where('month', $month)
            ->where('year', $year)
            ->get();
            
        foreach ($staffServiceCharges as $charge) {
            $staff = \App\Models\Staff::find($charge->staff_id);
            echo "- Staff: {$staff->name}, Percentage: {$charge->service_charge_pct}%, Share: Rs. {$charge->share_amount}\n";
        }
    } else {
        echo "No payroll data found in response\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
