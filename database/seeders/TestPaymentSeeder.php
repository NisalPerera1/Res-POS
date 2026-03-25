<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Order;

class TestPaymentSeeder extends Seeder
{
    public function run()
    {
        // Get some existing orders
        $orders = Order::take(5)->get();
        
        foreach ($orders as $index => $order) {
            // Create a payment for each order
            Payment::create([
                'order_id' => $order->id,
                'user_id' => 1,
                'method' => ['cash', 'card', 'mobile', 'voucher'][rand(0, 3)],
                'amount' => $order->total,
                'tendered' => $order->total + rand(0, 50),
                'change_amount' => rand(0, 50),
                'receipt_number' => 'RCP-' . date('Ymd') . '-TEST' . ($index + 1),
            ]);
            
            // Update order payment status
            $order->update([
                'payment_status' => 'paid',
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
        
        echo "Created " . $orders->count() . " test payments\n";
    }
}
