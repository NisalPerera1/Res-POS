<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing API endpoint accessibility...\n";
    
    // Simulate a frontend request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'staff_id' => 1,
        'date' => '2026-04-29',
        'amount' => 180.00,
        'reason' => 'Frontend simulation test',
        'status' => 'pending',
        'deduct_month' => 4,
        'deduct_year' => 2026,
        'notes' => 'Frontend simulation notes'
    ]);
    
    echo "Request data:\n";
    print_r($request->all());
    
    // Call the controller method
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response = $controller->storeAdvance($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content:\n";
    echo $response->getContent() . "\n";
    
    // Check if it was actually saved
    $newCount = \App\Models\StaffAdvance::count();
    echo "Total advances in DB: {$newCount}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
