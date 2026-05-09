<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing advance creation...\n";
    
    // Get first staff member
    $staff = \App\Models\Staff::first();
    if (!$staff) {
        echo "No staff found in database\n";
        exit;
    }
    
    echo "Found staff: {$staff->name} (ID: {$staff->id})\n";
    
    // Test data
    $data = [
        'staff_id' => $staff->id,
        'date' => now()->format('Y-m-d'),
        'amount' => 100.00,
        'reason' => 'Test advance',
        'status' => 'pending',
        'deduct_month' => 5,
        'deduct_year' => 2026,
        'notes' => 'Test notes'
    ];
    
    echo "Creating advance with data:\n";
    print_r($data);
    
    // Create advance
    $advance = \App\Models\StaffAdvance::create($data);
    
    echo "Advance created successfully!\n";
    echo "Advance ID: {$advance->id}\n";
    echo "Advance exists: " . ($advance->exists ? 'true' : 'false') . "\n";
    echo "Advance attributes:\n";
    print_r($advance->getAttributes());
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
