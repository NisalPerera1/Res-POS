<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Carbon\Carbon;

class ServiceChargeSeeder extends Seeder
{
    public function run()
    {
        // Create some test orders with service charge (tax_amount) for this month
        $orders = [
            [
                'id' => 1,
                'order_number' => 'ORD-001',
                'user_id' => 1,
                'customer_name' => 'John Doe',
                'table_id' => 1,
                'order_type' => 'dine_in',
                'subtotal' => 100.00,
                'tax_amount' => 15.00, // This is the service charge
                'total' => 115.00,
                'payment_status' => 'paid',
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'id' => 2,
                'order_number' => 'ORD-002',
                'user_id' => 2,
                'customer_name' => 'Jane Smith',
                'table_id' => 2,
                'order_type' => 'dine_in',
                'subtotal' => 80.00,
                'tax_amount' => 12.00,
                'total' => 92.00,
                'payment_status' => 'paid',
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'id' => 3,
                'order_number' => 'ORD-003',
                'user_id' => 1,
                'customer_name' => 'Bob Johnson',
                'table_id' => 3,
                'order_type' => 'dine_in',
                'subtotal' => 120.00,
                'tax_amount' => 18.00,
                'total' => 138.00,
                'payment_status' => 'paid',
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($orders as $order) {
            // Check if order already exists
            $existing = Order::find($order['id']);
            if (!$existing) {
                Order::create($order);
            }
        }

        echo "Created " . count($orders) . " test orders with service charge\n";
        echo "Total service charge: $" . array_sum(array_column($orders, 'tax_amount')) . "\n";
    }
}
