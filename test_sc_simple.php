<?php

require_once 'vendor/autoload.php';

use App\Models\Order;

echo "Testing Service Charge Fix...\n";

// Test creating a table order
$order = Order::create([
    'order_number' => 'TEST-001',
    'table_id' => 1,
    'user_id' => 1,
    'type' => 'dine_in',
    'status' => 'pending',
    'guests' => 2,
    'subtotal' => 1000,
    'tax_rate' => 0,
    'tax_amount' => 0,
    'discount_amount' => 0,
    'total' => 1000,
    'payment_status' => 'unpaid',
    'customer_name' => 'Test Customer',
    'customer_notes' => 'Test order',
    'service_charge_rate' => 0
]);

echo "Created order with tax_rate: {$order->tax_rate}\n";
echo "Created order with service_charge_rate: {$order->service_charge_rate}\n";

// Test recalculation
$order->recalculate();

echo "After recalculation - tax_rate: {$order->tax_rate}\n";
echo "After recalculation - tax_amount: {$order->tax_amount}\n";
echo "After recalculation - total: {$order->total}\n";

// Clean up
$order->delete();

echo "Test completed successfully!\n";
echo "Expected: tax_rate=0, service_charge_rate=0, tax_amount=0, total=1000\n";
echo "Actual: tax_rate={$order->tax_rate}, service_charge_rate={$order->service_charge_rate}, tax_amount={$order->tax_amount}, total={$order->total}\n";
