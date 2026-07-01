<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\KOTPrinterService;
use Illuminate\Http\Request;

class KOTPreviewController extends Controller
{
    protected $kotPrinter;

    public function __construct()
    {
        $this->kotPrinter = new KOTPrinterService();
    }

    /**
     * Preview KOT ticket in browser
     */
    public function preview($orderId, Request $request)
    {
        $order = Order::with(['table', 'items'])->findOrFail($orderId);
        
        // Get KOT round from request or use latest
        $kotRound = $request->get('kot_round', $order->items->max('kot_round') ?? 1);
        
        // Get items for this KOT round
        $items = $order->items()->where('kot_round', $kotRound)->get();
        
        if ($items->isEmpty()) {
            // If no items for specific round, get all items
            $items = $order->items;
        }
        
        // Generate HTML preview
        $html = $this->kotPrinter->generateHTMLPreview($order, $kotRound, $items);
        
        return response($html)
            ->header('Content-Type', 'text/html');
    }
}
