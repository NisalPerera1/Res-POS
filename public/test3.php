<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

try {
    // Clean old
    \App\Models\Order::query()->delete();
    \App\Models\Table::query()->update(['status' => 'free', 'current_order_id' => null]);

    // Create table order
    $table = \App\Models\Table::find(1);
    $order = \App\Models\Order::create(['table_id' => 1, 'status' => 'pending', 'type' => 'dine_in']);
    $table->update(['status' => 'occupied', 'current_order_id' => $order->id]);

    // Add item
    $item = \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'menu_item_id' => 1,
        'item_name' => "Cheeseburger",
        'unit_price' => 10,
        'quantity' => 1,
        'total_price' => 10,
        'status' => 'pending'
    ]);

    // Show API before KOT
    $ctrl = app(\App\Http\Controllers\OrderController::class);
    $req1 = \Illuminate\Http\Request::create('/api/orders/'.$order->id, 'GET');
    $api_before = $ctrl->show($order->id)->getContent();

    // Call KOT
    $req2 = \Illuminate\Http\Request::create('/api/orders/'.$order->id.'/kot', 'POST');
    $api_kot_response = $ctrl->sendKOT($req2, $order->id)->getContent();

    // Show API after KOT
    $req3 = \Illuminate\Http\Request::create('/api/orders/'.$order->id, 'GET');
    $api_after = $ctrl->show($order->id)->getContent();

    file_put_contents('debug_cart.json', json_encode([
        'before' => json_decode($api_before),
        'kot_response' => json_decode($api_kot_response),
        'after' => json_decode($api_after)
    ], JSON_PRETTY_PRINT));

    echo "SUCCESS";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine();
}
