<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Creating advance for April 2026...\n";
    
    // Test data for April
    $data = [
        'staff_id' => 1,
        'date' => '2026-04-29',
        'amount' => 150.00,
        'reason' => 'April advance test',
        'status' => 'pending',
        'deduct_month' => 4,  // April
        'deduct_year' => 2026,
        'notes' => 'April test notes'
    ];
    
    echo "Creating advance with data:\n";
    print_r($data);
    
    // Create advance
    $advance = \App\Models\StaffAdvance::create($data);
    
    echo "Advance created successfully!\n";
    echo "Advance ID: {$advance->id}\n";
    
    // Test API for April
    $request = new \Illuminate\Http\Request();
    $request->merge(['month' => 4, 'year' => 2026]);
    
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response = $controller->getAdvances($request);
    
    echo "API Response for April:\n";
    echo $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
