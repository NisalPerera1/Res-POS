<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

$order = \App\Models\Order::find(69);
$item = $order->items()->first();

// Dump before
echo "BEFORE: " . json_encode(\App\Models\Order::with('activeItems')->find(69)) . "\n";

// Update
$item->update(['status' => 'confirmed']);

// Dump after
echo "AFTER: " . json_encode(\App\Models\Order::with('activeItems')->find(69)) . "\n";
