<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing advance creation...\n";
    
    // Check if staff exists
    $staff = \App\Models\Staff::first();
    if (!$staff) {
        echo "ERROR: No staff found in database\n";
        exit;
    }
    
    echo "Found staff: {$staff->name} (ID: {$staff->id})\n";
    
    // Check current advances count
    $currentCount = \App\Models\StaffAdvance::count();
    echo "Current advances in DB: {$currentCount}\n";
    
    // Test data
    $data = [
        'staff_id' => $staff->id,
        'date' => now()->format('Y-m-d'),
        'amount' => 200.00,
        'reason' => 'Debug test advance',
        'status' => 'pending',
        'deduct_month' => 5,
        'deduct_year' => 2026,
        'notes' => 'Debug notes'
    ];
    
    echo "Creating advance with data:\n";
    print_r($data);
    
    // Create advance
    $advance = \App\Models\StaffAdvance::create($data);
    
    echo "Advance created successfully!\n";
    echo "Advance ID: {$advance->id}\n";
    echo "Advance exists: " . ($advance->exists ? 'true' : 'false') . "\n";
    
    // Verify it's actually in the database
    $dbAdvance = \App\Models\StaffAdvance::find($advance->id);
    echo "Found in DB: " . ($dbAdvance ? 'true' : 'false') . "\n";
    
    // Check new count
    $newCount = \App\Models\StaffAdvance::count();
    echo "New advances count: {$newCount}\n";
    
    if ($newCount > $currentCount) {
        echo "SUCCESS: Advance was saved to database!\n";
    } else {
        echo "ERROR: Advance count did not increase!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
