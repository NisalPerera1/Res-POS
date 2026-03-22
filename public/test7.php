<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$table = \App\Models\Table::with(['currentOrder.items.menuItem', 'currentOrder.payments'])->find(1);
echo json_encode($table, JSON_PRETTY_PRINT);
