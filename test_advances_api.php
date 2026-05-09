<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing getAdvances API endpoint...\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => 4,
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
    
    // Check total advances in database
    $totalCount = \App\Models\StaffAdvance::count();
    echo "Total advances in DB: {$totalCount}\n";
    
    // Check advances for current month
    $monthAdvances = \App\Models\StaffAdvance::with('staff')
        ->where('deduct_month', 4)
        ->where('deduct_year', 2026)
        ->get();
    
    echo "Advances for April 2026: " . $monthAdvances->count() . "\n";
    foreach ($monthAdvances as $advance) {
        echo "- ID: {$advance->id}, Staff: {$advance->staff->name}, Amount: {$advance->amount}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
