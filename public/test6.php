<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

$items = \Illuminate\Support\Facades\DB::select("SELECT id, order_id, status, is_void FROM order_items ORDER BY id DESC LIMIT 10");
echo json_encode($items, JSON_PRETTY_PRINT);
