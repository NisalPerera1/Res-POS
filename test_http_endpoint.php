<?php

// Test the actual HTTP endpoint
$url = 'http://localhost:8003/api/staff/advances';
$data = [
    'staff_id' => 1,
    'date' => '2026-04-29',
    'amount' => 120.00,
    'reason' => 'HTTP test advance',
    'status' => 'pending',
    'deduct_month' => 4,
    'deduct_year' => 2026,
    'notes' => 'HTTP test notes'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: {$httpCode}\n";
echo "Response: {$response}\n";

// Check if advance was created
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\StaffAdvance::count();
echo "Total advances in DB: {$count}\n";
