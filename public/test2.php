<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

$order = \App\Models\Order::with(['activeItems.menuItem', 'pendingItems.menuItem', 'items.menuItem', 'table', 'user'])->find(69);
echo json_encode($order);
