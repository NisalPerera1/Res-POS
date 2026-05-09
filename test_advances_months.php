<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking all advances in database...\n";
    
    $advances = \App\Models\StaffAdvance::with('staff')->get();
    
    echo "Total advances: " . $advances->count() . "\n";
    
    foreach ($advances as $advance) {
        echo "ID: {$advance->id}\n";
        echo "Staff: " . ($advance->staff ? $advance->staff->name : 'N/A') . "\n";
        echo "Date: {$advance->date}\n";
        echo "Amount: {$advance->amount}\n";
        echo "Deduct Month: {$advance->deduct_month}\n";
        echo "Deduct Year: {$advance->deduct_year}\n";
        echo "Status: {$advance->status}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
