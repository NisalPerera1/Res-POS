<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "=== Order Status Check ===\n";
echo "Total orders: " . Order::count() . "\n";

$orders = Order::select('id', 'order_number', 'total', 'payment_status', 'status', 'created_at')->latest()->take(5)->get();

foreach ($orders as $order) {
    echo "Order #{$order->order_number}: Total={$order->total}, Payment={$order->payment_status}, Status={$order->status}, Created={$order->created_at}\n";
}

echo "\n=== Payment Status Breakdown ===\n";
$paymentStatuses = Order::select('payment_status')
    ->selectRaw('COUNT(*) as count')
    ->groupBy('payment_status')
    ->get();

foreach ($paymentStatuses as $status) {
    echo "{$status->payment_status}: {$status->count} orders\n";
}

echo "\n=== Paid Orders Total ===\n";
$paidTotal = Order::where('payment_status', 'paid')->sum('total');
echo "Total revenue from paid orders: {$paidTotal}\n";
