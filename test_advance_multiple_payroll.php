<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing advance availability for multiple payroll generations...\n";
    
    $staff = \App\Models\Staff::first();
    $month = 4;
    $year = 2026;
    
    echo "Testing with staff: {$staff->name} (ID: {$staff->id})\n";
    
    // Create a new advance for testing
    $advance = \App\Models\StaffAdvance::create([
        'staff_id' => $staff->id,
        'date' => now()->format('Y-m-d'),
        'amount' => 300.00,
        'reason' => 'Test advance for multiple payroll',
        'status' => 'approved',
        'deduct_month' => $month,
        'deduct_year' => $year,
        'notes' => 'Test advance',
    ]);
    
    echo "Created advance: ID {$advance->id}, Amount: {$advance->amount}, Status: {$advance->status}\n";
    
    // Test advance availability before payroll
    $advanceDeduction1 = $staff->getAdvancesForMonth($month, $year);
    echo "Advance deduction before payroll: Rs. {$advanceDeduction1}\n";
    
    // Generate payroll first time
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => $month,
        'year' => $year,
        'staff_ids' => [$staff->id]
    ]);
    
    echo "Generating payroll (first time)...\n";
    
    // Delete existing payroll first to allow regeneration
    \App\Models\Payroll::where('staff_id', $staff->id)
        ->where('month', $month)
        ->where('year', $year)
        ->delete();
    
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response1 = $controller->generatePayroll($request);
    
    echo "First payroll generation: " . $response1->getStatusCode() . "\n";
    
    // Check advance status after first payroll
    $updatedAdvance = \App\Models\StaffAdvance::find($advance->id);
    echo "Advance status after first payroll: {$updatedAdvance->status}\n";
    
    // Test advance availability after first payroll
    $advanceDeduction2 = $staff->getAdvancesForMonth($month, $year);
    echo "Advance deduction after first payroll: Rs. {$advanceDeduction2}\n";
    
    // Generate payroll second time
    echo "Generating payroll (second time)...\n";
    
    // Delete existing payroll to allow regeneration
    \App\Models\Payroll::where('staff_id', $staff->id)
        ->where('month', $month)
        ->where('year', $year)
        ->delete();
    
    $response2 = $controller->generatePayroll($request);
    
    echo "Second payroll generation: " . $response2->getStatusCode() . "\n";
    
    // Parse responses to compare advance deductions
    $data1 = json_decode($response1->getContent(), true);
    $data2 = json_decode($response2->getContent(), true);
    
    if (!empty($data1['payrolls']) && !empty($data2['payrolls'])) {
        $payroll1 = $data1['payrolls'][0];
        $payroll2 = $data2['payrolls'][0];
        
        echo "\nComparison:\n";
        echo "- First payroll advance deductions: Rs. {$payroll1['advance_deductions']}\n";
        echo "- Second payroll advance deductions: Rs. {$payroll2['advance_deductions']}\n";
        
        if ($payroll1['advance_deductions'] == $payroll2['advance_deductions']) {
            echo "✅ SUCCESS: Advance deductions are consistent across payroll generations!\n";
        } else {
            echo "❌ ERROR: Advance deductions are inconsistent!\n";
        }
    } else {
        echo "No payroll data found in responses\n";
    }
    
    // Clean up test advance
    $advance->delete();
    echo "Test advance cleaned up\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
