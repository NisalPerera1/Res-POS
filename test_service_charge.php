<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing service charge distribution...\n";
    
    // Check if there are any completed orders with service charge
    $ordersWithServiceCharge = \DB::table('orders')
        ->where('payment_status', 'paid')
        ->whereNotNull('table_id')
        ->where('tax_amount', '>', 0)
        ->get();
    
    echo "Orders with service charge: " . $ordersWithServiceCharge->count() . "\n";
    
    if ($ordersWithServiceCharge->isEmpty()) {
        echo "No orders with service charge found. Creating test data...\n";
        
        // Create a test order with service charge
        $orderId = \DB::table('orders')->insertGetId([
            'customer_name' => 'Test Customer',
            'order_type' => 'dine_in',
            'order_number' => 'TEST-' . time(),
            'table_id' => 1,
            'user_id' => 1,
            'type' => 'dine_in',
            'status' => 'completed',
            'payment_status' => 'paid',
            'guests' => 2,
            'subtotal' => 1000.00,
            'tax_rate' => 10.00, // 10% service charge
            'tax_amount' => 100.00, // service charge amount
            'discount_amount' => 0.00,
            'total' => 1100.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Created test order with ID: $orderId\n";
    }
    
    // Test the distribution method
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'month' => 4,
        'year' => 2026
    ]);
    
    echo "Testing distribution for April 2026...\n";
    
    $controller = new \App\Http\Controllers\Api\StaffController();
    $response = $controller->distributeServiceCharge($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content:\n";
    echo $response->getContent() . "\n";
    
    // Check the results
    $monthlyServiceCharge = \App\Models\MonthlyServiceCharge::where('month', 4)->where('year', 2026)->first();
    if ($monthlyServiceCharge) {
        echo "Monthly service charge created: Rs. " . $monthlyServiceCharge->total_amount . "\n";
    }
    
    $staffCharges = \App\Models\StaffServiceCharge::where('month', 4)->where('year', 2026)->get();
    echo "Staff service charges created: " . $staffCharges->count() . "\n";
    
    foreach ($staffCharges as $charge) {
        $staff = \App\Models\Staff::find($charge->staff_id);
        echo "- Staff: " . ($staff ? $staff->name : 'Unknown') . ", Share: Rs. " . $charge->share_amount . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
