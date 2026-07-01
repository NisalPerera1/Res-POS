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
            $isTakeaway = $order->type === 'takeaway';
            
            // Header - Different styling based on order type
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            
            if ($isTakeaway) {
                $this->printer->text("TAKEAWAY ORDER\n");
            } else {
                $this->printer->text("TABLE ORDER\n");
            }
            
            $this->printer->setTextSize(1, 1);
            $this->printer->text("KITCHEN TICKET\n");
            $this->printer->text("====================\n");
            $this->printer->feed(1);

            // Order Info
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->setTextSize(1, 1);
            $this->printer->text("Order #: " . $order->order_number . "\n");
            $this->printer->text("KOT Round: " . $kotRound . "\n");
            $this->printer->text("Time: " . now()->format('H:i:s') . "\n");
            $this->printer->text("Date: " . now()->format('Y-m-d') . "\n");
            
            // Table Info (for table orders)
            if (!$isTakeaway && $order->table) {
                $this->printer->setTextSize(2, 1);
                $this->printer->text("TABLE: " . $order->table->name . "\n");
                $this->printer->setTextSize(1, 1);
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

            // Order Type Badge
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 1);
            
            if ($isTakeaway) {
                $this->printer->text("⚡ TAKEAWAY ⚡\n");
            } else {
                $this->printer->text("🍽️ DINING IN\n");
            }
            
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
     * Generate HTML preview of KOT ticket
     */
    public function generateHTMLPreview(Order $order, $kotRound, $items)
    {
        $isTakeaway = $order->type === 'takeaway';
        $textColor = $isTakeaway ? '#dc2626' : '#000000';
        $borderColor = $isTakeaway ? '#ef4444' : '#000000';
        $bgColor = $isTakeaway ? '#fef2f2' : '#ffffff';
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT Preview - Order #' . $order->order_number . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Courier New", monospace;
            background: #f5f5f5;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .receipt {
            width: 80mm;
            background: ' . $bgColor . ';
            padding: 10px;
            border: 2px solid ' . $borderColor . ';
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed ' . $borderColor . ';
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: ' . $textColor . ';
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 16px;
            color: ' . $textColor . ';
        }
        .divider {
            border-top: 2px dashed ' . $borderColor . ';
            margin: 10px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin: 3px 0;
            color: ' . $textColor . ';
        }
        .table-info {
            font-size: 20px;
            font-weight: bold;
            color: ' . $textColor . ';
            text-align: center;
            margin: 10px 0;
            padding: 5px;
            background: ' . ($isTakeaway ? '#fee2e2' : '#e5e5e5') . ';
            border-radius: 4px;
        }
        .order-type {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: ' . $textColor . ';
            margin: 10px 0;
            padding: 8px;
            background: ' . ($isTakeaway ? '#fecaca' : '#d4d4d4') . ';
            border-radius: 4px;
        }
        .items-section {
            margin: 10px 0;
        }
        .items-title {
            font-size: 14px;
            font-weight: bold;
            color: ' . $textColor . ';
            margin-bottom: 5px;
        }
        .item {
            margin: 8px 0;
            padding: 5px;
            border-left: 3px solid ' . $borderColor . ';
            padding-left: 8px;
        }
        .item-name {
            font-size: 12px;
            font-weight: bold;
            color: ' . $textColor . ';
        }
        .item-qty {
            font-size: 16px;
            font-weight: bold;
            color: ' . $textColor . ';
            margin-top: 3px;
        }
        .item-notes {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
            font-style: italic;
        }
        .item-modifier {
            font-size: 10px;
            color: #666;
            margin-top: 1px;
        }
        .special-instructions {
            margin: 10px 0;
            padding: 8px;
            background: ' . ($isTakeaway ? '#fef3c7' : '#fef9c3') . ';
            border-radius: 4px;
            font-size: 11px;
            color: ' . $textColor . ';
        }
        .footer {
            text-align: center;
            border-top: 2px dashed ' . $borderColor . ';
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer p {
            font-size: 12px;
            color: ' . $textColor . ';
            margin: 3px 0;
        }
        .signature-line {
            border-top: 1px solid ' . $borderColor . ';
            margin-top: 15px;
            padding-top: 5px;
            font-size: 11px;
            color: ' . $textColor . ';
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: ' . $textColor . ';
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print</button>
    <div class="receipt">
        <div class="header">
            <h1>' . ($isTakeaway ? 'TAKEAWAY ORDER' : 'TABLE ORDER') . '</h1>
            <h2>KITCHEN TICKET</h2>
        </div>
        
        <div class="divider"></div>
        
        <div class="info-row">
            <span>Order #:</span>
            <span>' . $order->order_number . '</span>
        </div>
        <div class="info-row">
            <span>KOT Round:</span>
            <span>' . $kotRound . '</span>
        </div>
        <div class="info-row">
            <span>Time:</span>
            <span>' . now()->format('H:i:s') . '</span>
        </div>
        <div class="info-row">
            <span>Date:</span>
            <span>' . now()->format('Y-m-d') . '</span>
        </div>';
        
        // Table Info
        if (!$isTakeaway && $order->table) {
            $html .= '<div class="table-info">
                🍽️ TABLE: ' . $order->table->name . '
            </div>';
            if ($order->table->section) {
                $html .= '<div class="info-row">
                    <span>Section:</span>
                    <span>' . $order->table->section . '</span>
                </div>';
            }
        }
        
        // Customer Info
        if ($order->customer_name) {
            $html .= '<div class="info-row">
                <span>Customer:</span>
                <span>' . $order->customer_name . '</span>
            </div>';
        }
        if ($order->guests) {
            $html .= '<div class="info-row">
                <span>Guests:</span>
                <span>' . $order->guests . '</span>
            </div>';
        }
        
        $html .= '<div class="divider"></div>';
        
        // Order Type Badge
        $html .= '<div class="order-type">
            ' . ($isTakeaway ? '⚡ TAKEAWAY ⚡' : '🍽️ DINING IN') . '
        </div>';
        
        // Items
        $html .= '<div class="items-section">
            <div class="items-title">ITEMS TO PREPARE:</div>
            <div class="divider"></div>';
        
        foreach ($items as $index => $item) {
            $html .= '<div class="item">
                <div class="item-name">' . ($index + 1) . '. ' . $item->item_name . '</div>
                <div class="item-qty">Qty: ' . $item->quantity . '</div>';
            
            if ($item->notes) {
                $html .= '<div class="item-notes">Notes: ' . $item->notes . '</div>';
            }
            
            if ($item->modifiers) {
                $modifiers = is_array($item->modifiers) ? $item->modifiers : json_decode($item->modifiers, true);
                if ($modifiers && is_array($modifiers)) {
                    foreach ($modifiers as $modifier) {
                        $html .= '<div class="item-modifier">+ ' . $modifier['name'] . '</div>';
                    }
                }
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Special Instructions
        if ($order->notes) {
            $html .= '<div class="special-instructions">
                <strong>SPECIAL INSTRUCTIONS:</strong><br>
                ' . $order->notes . '
            </div>';
        }
        
        // Footer
        $html .= '<div class="footer">
            <div class="divider"></div>
            <p>PLEASE ACKNOWLEDGE</p>
            <div class="signature-line">RECEIPT: ________</div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
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
