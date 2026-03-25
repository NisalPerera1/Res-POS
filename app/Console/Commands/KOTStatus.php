<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KOTPrinterService;

class KOTStatus extends Command
{
    protected $signature = 'kot:status';
    protected $description = 'Check KOT printer status and connectivity';

    public function handle()
    {
        $this->info('🖨 Checking KOT Printer Status...');
        
        try {
            $printer = new KOTPrinterService();
            $this->info('✅ Printer service initialized successfully');
            
            // Check configuration
            $ip = config('pos.kitchen_printer_ip', '192.168.1.100');
            $port = config('pos.kitchen_printer_port', 9100);
            $autoPrint = config('pos.kot.auto_print', true);
            
            $this->info('🔧 Configuration:');
            $this->info('   IP: ' . $ip);
            $this->info('   Port: ' . $port);
            $this->info('   Auto Print: ' . ($autoPrint ? 'YES' : 'NO'));
            
            // Test print
            $this->info('📄 Testing print capability...');
            $testOrder = new \App\Models\Order([
                'order_number' => 'STATUS-' . date('His'),
                'customer_name' => 'Status Check',
                'type' => 'dine_in',
                'notes' => 'Printer status test',
                'guests' => 1,
            ]);
            
            $testItems = collect([
                (object)[
                    'item_name' => 'Status Test Item',
                    'quantity' => 1,
                    'notes' => 'Printer check',
                    'modifiers' => null
                ]
            ]);
            
            $success = $printer->printKOT($testOrder, 1, $testItems);
            
            if ($success) {
                $this->info('✅ KOT Printer is WORKING!');
                $this->info('📋 Test KOT printed successfully');
                $this->info('🎯 Kitchen printing is fully functional');
            } else {
                $this->error('❌ KOT Printer FAILED!');
                $this->error('💡 Check printer connection:');
                $this->error('   - Printer is powered on');
                $this->error('   - Network cable connected');
                $this->error('   - IP address correct: ' . $ip);
                $this->error('   - Port open: ' . $port);
                $this->error('   - Paper loaded');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Printer Error: ' . $e->getMessage());
            $this->error('💡 Troubleshooting:');
            $this->error('   1. Check printer is turned on');
            $this->error('   2. Verify network connection');
            $this->error('   3. Check IP/Port in .env file');
            $this->error('   4. Ensure printer has paper');
        }
        
        $this->info('🏁 Status check completed.');
    }
}
