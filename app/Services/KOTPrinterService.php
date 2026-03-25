<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Exception;

class KOTPrinterService
{
    protected $printer;
    protected $connector;

    public function __construct()
    {
        $this->initializePrinter();
    }

    /**
     * Initialize printer connection
     */
    protected function initializePrinter()
    {
        try {
            // Try network printer first
            $printerIp = config('pos.kitchen_printer_ip', '192.168.1.100');
            $printerPort = config('pos.kitchen_printer_port', 9100);
            
            $this->connector = new NetworkPrintConnector($printerIp, $printerPort);
            $this->printer = new Printer($this->connector);
        } catch (Exception $e) {
            // Fallback to USB/Local printer
            try {
                $this->connector = new FilePrintConnector("php://stdout");
                $this->printer = new Printer($this->connector);
            } catch (Exception $e) {
                // Log error but don't fail the KOT process
                \Log::error('KOT Printer initialization failed: ' . $e->getMessage());
                $this->printer = null;
            }
        }
    }

    /**
     * Print KOT for order items
     */
    public function printKOT(Order $order, $kotRound, $items)
    {
        if (!$this->printer) {
            \Log::info('KOT Printer not available, skipping print for Order #' . $order->order_number);
            return false;
        }

        try {
            // Header
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("KITCHEN ORDER\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("TICKET\n");
            $this->printer->text("====================\n");
            $this->printer->feed(1);

            // Order Info
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->setTextSize(1, 1);
            $this->printer->text("Order #: " . $order->order_number . "\n");
            $this->printer->text("KOT Round: " . $kotRound . "\n");
            $this->printer->text("Time: " . now()->format('H:i:s') . "\n");
            $this->printer->text("Date: " . now()->format('Y-m-d') . "\n");
            
            // Table Info
            if ($order->table) {
                $this->printer->text("Table: " . $order->table->name . "\n");
                if ($order->table->section) {
                    $this->printer->text("Section: " . $order->table->section . "\n");
                }
            }
            
            // Customer Info
            if ($order->customer_name) {
                $this->printer->text("Customer: " . $order->customer_name . "\n");
            }
            if ($order->guests) {
                $this->printer->text("Guests: " . $order->guests . "\n");
            }
            
            $this->printer->text("====================\n");
            $this->printer->feed(1);

            // Order Type
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 1);
            $this->printer->text(strtoupper($order->type) . "\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->feed(1);

            // Items
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("ITEMS TO PREPARE:\n");
            $this->printer->text("====================\n");
            $this->printer->feed(1);

            foreach ($items as $index => $item) {
                $this->printer->setTextSize(1, 1);
                $this->printer->text(($index + 1) . ". " . $item->item_name . "\n");
                
                // Quantity
                $this->printer->setTextSize(2, 1);
                $this->printer->text("   Qty: " . $item->quantity . "\n");
                $this->printer->setTextSize(1, 1);
                
                // Notes
                if ($item->notes) {
                    $this->printer->text("   Notes: " . $item->notes . "\n");
                }
                
                // Modifiers
                if ($item->modifiers) {
                    $modifiers = is_array($item->modifiers) ? $item->modifiers : json_decode($item->modifiers, true);
                    if ($modifiers && is_array($modifiers)) {
                        foreach ($modifiers as $modifier) {
                            $this->printer->text("   + " . $modifier['name'] . "\n");
                        }
                    }
                }
                
                $this->printer->feed(1);
            }

            // Special Instructions
            if ($order->notes) {
                $this->printer->text("====================\n");
                $this->printer->text("SPECIAL INSTRUCTIONS:\n");
                $this->printer->text($order->notes . "\n");
                $this->printer->feed(1);
            }

            // Footer
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("====================\n");
            $this->printer->text("PLEASE ACKNOWLEDGE\n");
            $this->printer->text("RECEIPT: ________\n");
            $this->printer->feed(2);

            // Cut paper
            $this->printer->cut();
            
            return true;
        } catch (Exception $e) {
            \Log::error('KOT Print failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Print void/cancellation notice
     */
    public function printVoidNotice(Order $order, OrderItem $item, $reason)
    {
        if (!$this->printer) {
            return false;
        }

        try {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("VOID NOTICE\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("====================\n");
            $this->printer->feed(1);

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Order #: " . $order->order_number . "\n");
            $this->printer->text("Time: " . now()->format('H:i:s') . "\n");
            $this->printer->text("Table: " . ($order->table?->name ?? 'N/A') . "\n");
            $this->printer->feed(1);

            $this->printer->setTextSize(2, 1);
            $this->printer->text("CANCELLED ITEM:\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text($item->item_name . " x" . $item->quantity . "\n");
            
            if ($reason) {
                $this->printer->text("Reason: " . $reason . "\n");
            }
            
            $this->printer->feed(2);
            $this->printer->cut();
            
            return true;
        } catch (Exception $e) {
            \Log::error('Void notice print failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Close printer connection
     */
    public function __destruct()
    {
        if ($this->printer) {
            try {
                $this->printer->close();
            } catch (Exception $e) {
                \Log::error('Failed to close printer: ' . $e->getMessage());
            }
        }
    }
}
