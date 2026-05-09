<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing advance creation with current database structure...\n";
    
    // Check current count
    $beforeCount = \App\Models\StaffAdvance::count();
    echo "Advances before: {$beforeCount}\n";
    
    // Test data matching frontend form
    $data = [
        'staff_id' => 1,
        'date' => '2026-04-29',
        'amount' => 250.00,
        'reason' => 'Frontend test advance',
        'status' => 'pending',
        'deduct_month' => 4,  // April
        'deduct_year' => 2026,
        'notes' => 'Frontend test notes'
    ];
    
    echo "Creating advance with data:\n";
    print_r($data);
    
    // Test validation rules (same as controller)
    $rules = [
        'staff_id'     => 'required|exists:staff,id',
        'date'         => 'required|date',
        'amount'       => 'required|numeric|min:1',
        'reason'       => 'nullable|string|max:255',
        'status'       => 'nullable|in:pending,approved,rejected',
        'deduct_month' => 'nullable|integer|min:1|max:12',
        'deduct_year'  => 'nullable|integer|min:2020',
        'notes'        => 'nullable|string',
    ];
    
    // Validate data
    $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);
    if ($validator->fails()) {
        echo "Validation failed:\n";
        print_r($validator->errors()->toArray());
        exit;
    }
    
    echo "Validation passed!\n";
    
    // Create advance
    $advance = \App\Models\StaffAdvance::create($data);
    
    echo "Advance created successfully!\n";
    echo "Advance ID: {$advance->id}\n";
    echo "Advance exists: " . ($advance->exists ? 'true' : 'false') . "\n";
    
    // Check after count
    $afterCount = \App\Models\StaffAdvance::count();
    echo "Advances after: {$afterCount}\n";
    
    // Verify in database
    $dbAdvance = \App\Models\StaffAdvance::find($advance->id);
    echo "Found in DB: " . ($dbAdvance ? 'true' : 'false') . "\n";
    
    if ($dbAdvance) {
        echo "DB Advance data:\n";
        print_r($dbAdvance->toArray());
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
