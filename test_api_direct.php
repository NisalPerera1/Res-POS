<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing API endpoint directly...\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'staff_id' => 1,
        'date' => '2026-04-29',
        'amount' => 300.00,
        'reason' => 'API direct test',
        'status' => 'pending',
        'deduct_month' => 5,
        'deduct_year' => 2026,
        'notes' => 'API test notes'
    ]);
    
    echo "Request data:\n";
    print_r($request->all());
    
    // Create controller instance
    $controller = new \App\Http\Controllers\Api\StaffController();
    
    echo "Calling storeAdvance method...\n";
    $response = $controller->storeAdvance($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content:\n";
    echo $response->getContent() . "\n";
    
    // Check if advance was actually saved
    $count = \App\Models\StaffAdvance::count();
    echo "Total advances in DB: {$count}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
