<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing API advance creation...\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'staff_id' => 1,
        'date' => '2026-04-29',
        'amount' => 150.00,
        'reason' => 'Test advance via API',
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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
