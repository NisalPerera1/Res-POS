<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing advance creation and payroll integration...\n";
    
    // Create a test advance (auto-approved)
    $staff = \App\Models\Staff::first();
    if (!$staff) {
        echo "No staff found\n";
        exit;
    }
    
    echo "Found staff: {$staff->name} (ID: {$staff->id})\n";
    
    // Create advance for current month
    $advanceData = [
        'staff_id' => $staff->id,
        'date' => now()->format('Y-m-d'),
        'amount' => 500.00,
        'reason' => 'Test advance for payroll',
        'deduct_month' => 4, // April
        'deduct_year' => 2026,
        'notes' => 'Auto-approved advance test',
    ];
    
    $advance = \App\Models\StaffAdvance::create($advanceData);
    
    echo "Created advance: ID {$advance->id}, Status: {$advance->status}, Amount: {$advance->amount}\n";
    
    // Test getAdvancesForMonth method
    $advanceDeduction = $staff->getAdvancesForMonth(4, 2026);
    echo "Advance deduction for April 2026: Rs. {$advanceDeduction}\n";
    
    // Test payroll generation
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => 4,
        'year' => 2026,
        'staff_ids' => [$staff->id]
    ]);
    
    echo "Generating payroll for April 2026...\n";
    
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response = $controller->generatePayroll($request);
    
    echo "Payroll generation response: " . $response->getStatusCode() . "\n";
    echo $response->getContent() . "\n";
    
    // Check if advance was marked as deducted
    $updatedAdvance = \App\Models\StaffAdvance::find($advance->id);
    echo "Advance status after payroll: {$updatedAdvance->status}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
