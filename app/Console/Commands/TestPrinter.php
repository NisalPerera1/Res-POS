<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KOTPrinterService;
use App\Models\Order;

class TestPrinter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printer:test {--type=kot : Type of print test (kot, void)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test kitchen printer connectivity and printing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing KOT Printer Service...');
        
        try {
            $printer = new KOTPrinterService();
            
            if ($this->option('type') === 'kot') {
                // Create a test order for KOT printing
                $testOrder = new Order([
                    'order_number' => 'TEST-' . date('His'),
                    'customer_name' => 'Test Customer',
                    'type' => 'dine_in',
                    'notes' => 'This is a test KOT print',
                    'guests' => 2,
                ]);
                
                // Create test items
                $testItems = collect([
                    (object)[
                        'item_name' => 'Test Burger',
                        'quantity' => 2,
                        'notes' => 'No onions',
                        'modifiers' => json_encode([
                            ['name' => 'Extra Cheese'],
                            ['name' => 'Bacon']
                        ])
                    ],
                    (object)[
                        'item_name' => 'Test Fries',
                        'quantity' => 1,
                        'notes' => 'Extra salt',
                        'modifiers' => null
                    ]
                ]);
                
                $success = $printer->printKOT($testOrder, 1, $testItems);
                
                if ($success) {
                    $this->info('✅ KOT Test Print Successful!');
                } else {
                    $this->error('❌ KOT Test Print Failed!');
                }
            } elseif ($this->option('type') === 'void') {
                // Test void notice
                $testOrder = new Order([
                    'order_number' => 'TEST-' . date('His'),
                    'customer_name' => 'Test Customer',
                ]);
                
                $testItem = (object)[
                    'item_name' => 'Test Item to Void',
                    'quantity' => 1,
                ];
                
                $success = $printer->printVoidNotice($testOrder, $testItem, 'Test void reason');
                
                if ($success) {
                    $this->info('✅ Void Notice Test Print Successful!');
                } else {
                    $this->error('❌ Void Notice Test Print Failed!');
                }
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Printer Test Error: ' . $e->getMessage());
        }
        
        $this->info('Printer test completed.');
    }
}
