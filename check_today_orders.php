<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Carbon\Carbon;

echo "=== Today's Orders Check ===\n";
$today = Carbon::today();
echo "Today: " . $today->format('Y-m-d') . "\n";

$todayOrders = Order::whereDate('created_at', $today)
    ->select('id', 'order_number', 'total', 'payment_status', 'status', 'created_at')
    ->latest()
    ->get();

echo "Today's total orders: " . $todayOrders->count() . "\n\n";

foreach ($todayOrders as $order) {
    echo "Order #{$order->order_number}: Total={$order->total}, Payment={$order->payment_status}, Status={$order->status}, Time={$order->created_at->format('H:i')}\n";
}

$todayPaidRevenue = Order::whereDate('created_at', $today)
    ->where('payment_status', 'paid')
    ->sum('total');

echo "\nToday's paid revenue: {$todayPaidRevenue}\n";

// Check last 2 orders specifically
echo "\n=== Last 2 Orders Overall ===\n";
$lastOrders = Order::latest()->take(2)->get();
foreach ($lastOrders as $order) {
    echo "Order #{$order->order_number}: Total={$order->total}, Payment={$order->payment_status}, Status={$order->status}, Date={$order->created_at->format('Y-m-d H:i')}\n";
}
