<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing getAdvances API endpoint for May 2026...\n";
    
    // Create a mock request for May (month 5)
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => 5,
        'year' => 2026
    ]);
    
    echo "Request data:\n";
    print_r($request->all());
    
    // Create controller instance
    $controller = new \App\Http\Controllers\Api\StaffController();
    
    echo "Calling getAdvances method...\n";
    $response = $controller->getAdvances($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content:\n";
    echo $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
