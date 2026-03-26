<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Services\KOTPrinterService;
use App\Jobs\PrintKOTJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\OrderStatusUpdated;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['table', 'user', 'items.menuItem'])
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->table_id) {
            $query->where('table_id', $request->table_id);
        }

        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        return response()->json(
            $this->orderWithItems($id)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_id'       => 'nullable|exists:tables,id',
            'type'           => 'nullable|in:dine_in,takeaway,delivery',
            'guests'         => 'nullable|integer|min:1',
            'customer_name'  => 'nullable|string|max:100',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Determine service charge based on order type
            $isDirectOrder = is_null($request->table_id);
            $taxRate = $isDirectOrder ? 0 : 10; // 0% for direct orders, 10% for table orders
            
            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'table_id'        => $request->table_id,
                'user_id'         => auth()->id(),
                'type'            => $request->type ?? ($isDirectOrder ? 'takeaway' : 'dine_in'),
                'status'          => 'pending',
                'guests'          => $request->guests ?? 1,
                'subtotal'        => 0,
                'tax_rate'        => $taxRate,
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'total'           => 0,
                'payment_status'  => 'unpaid',
                'customer_name'   => $request->customer_name,
                'customer_notes'  => $request->customer_notes,
            ]);

            // ✅ Link table to order
            if ($request->table_id) {
                Table::where('id', $request->table_id)->update([
                    'status'           => 'occupied',
                    'current_order_id' => $order->id,
                ]);
            }

            DB::commit();

            // ✅ Return full order with items (not just $order->load(['table']))
            return response()->json(
                $this->orderWithItems($order->id),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

public function addItem(Request $request, $orderId)
{
    $request->validate([
        'menu_item_id'        => 'required|exists:menu_items,id',
        'quantity'            => 'required|integer|min:1',
        'selected_modifiers'  => 'nullable|array',   // array of modifier IDs
        'selected_modifiers.*'=> 'exists:modifiers,id',
        'notes'               => 'nullable|string|max:255',
        'is_instant'          => 'nullable|boolean',
    ]);

    $order    = Order::findOrFail($orderId);
    $menuItem = MenuItem::findOrFail($request->menu_item_id);

    if (!$menuItem->is_available) {
        return response()->json(['message' => 'Item is not available'], 422);
    }

    $isInstant = $menuItem->is_instant || $request->boolean('is_instant');

    // Build modifiers snapshot
    $modifierCost      = 0;
    $selectedModifiers = [];

    if ($request->selected_modifiers && count($request->selected_modifiers) > 0) {
        $modifiers = Modifier::with(['menuItems' => function($query) use ($menuItem) {
            $query->where('menu_item_id', $menuItem->id);
        }])
            ->whereIn('id', $request->selected_modifiers)
            ->where('is_active', true)
            ->get();

        foreach ($modifiers as $mod) {
            // Get final price using new pricing logic (absolute or incremental)
            $finalPrice = $mod->getFinalPriceForMenuItem($menuItem->id, $menuItem->price);
            
            // Calculate how much this modifier adds to the base price
            $incrementAmount = $finalPrice - $menuItem->price;
            $modifierCost += $incrementAmount;
            
            $selectedModifiers[] = [
                'id'         => $mod->id,
                'group_id'   => $mod->modifier_group_id,
                'name'       => $mod->name,
                'price'      => $incrementAmount, // Store increment amount
                'final_price' => $finalPrice,    // Store final price for display
                'pricing_type' => $mod->menuItems()
                    ->where('menu_item_id', $menuItem->id)
                    ->first()?->pivot->pricing_type ?? 'absolute',
            ];
        }
    }

    $unitPrice  = (float) $menuItem->price + $modifierCost;
    $totalPrice = $unitPrice * $request->quantity;

    // Build a modifier signature for grouping
    // Items with same menu_item + same modifiers = same line
    $modifierSignature = collect($selectedModifiers)
        ->pluck('id')->sort()->values()->implode(',');

    if ($isInstant) {
        $existing = OrderItem::where('order_id', $orderId)
            ->where('menu_item_id', $menuItem->id)
            ->where('status', 'served')
            ->where('is_void', false)
            ->where('kot_round', 0)
            ->whereRaw("COALESCE(JSON_EXTRACT(modifiers, '$[*].id'), '[]') = ?",
                [json_encode(collect($selectedModifiers)->pluck('id')->sort()->values())])
            ->first();

        // Simpler merge for instant: just check same item + kot_round 0
        $existing = OrderItem::where('order_id', $orderId)
            ->where('menu_item_id', $menuItem->id)
            ->where('is_void', false)
            ->where('kot_round', 0)
            ->first();

        if ($existing && empty($selectedModifiers)) {
            $newQty = $existing->quantity + $request->quantity;
            $existing->update([
                'quantity'    => $newQty,
                'total_price' => $existing->unit_price * $newQty,
            ]);
            $item = $existing->fresh();
        } else {
            $item = OrderItem::create([
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_name'    => $menuItem->name,
                'unit_price'   => $unitPrice,
                'quantity'     => $request->quantity,
                'total_price'  => $totalPrice,
                'modifiers'    => $selectedModifiers ?: null,
                'notes'        => $request->notes,
                'status'       => 'served',
                'kot_round'    => 0,
                'kot_sent_at'  => now(),
            ]);
        }
    } else {
        // Normal item: only merge if same menu_item + same modifiers + unsent
        $existing = null;

        if (empty($selectedModifiers)) {
            // No modifiers — safe to merge with any unsent same item
            $existing = OrderItem::where('order_id', $orderId)
                ->where('menu_item_id', $menuItem->id)
                ->whereNull('kot_round')
                ->where('is_void', false)
                ->whereNull('modifiers')
                ->first();
        }
        // With modifiers = always new line (different config)

        if ($existing) {
            $newQty = $existing->quantity + $request->quantity;
            $existing->update([
                'quantity'    => $newQty,
                'total_price' => $existing->unit_price * $newQty,
            ]);
            $item = $existing->fresh();
        } else {
            $item = OrderItem::create([
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'item_name'    => $this->buildItemName($menuItem->name, $selectedModifiers),
                'unit_price'   => $unitPrice,
                'quantity'     => $request->quantity,
                'total_price'  => $totalPrice,
                'modifiers'    => $selectedModifiers ?: null,
                'notes'        => $request->notes,
                'status'       => 'pending',
                'kot_round'    => null,
            ]);
        }
    }

    $order->load('activeItems');
    $order->recalculate();

    return response()->json([
        'item'       => $item->load('menuItem'),
        'order'      => $this->orderWithItems($orderId),
        'is_instant' => $isInstant,
    ]);
}

/**
 * Build display name with key modifiers
 * e.g. "Beef Burger (Large, Hot)"
 */
private function buildItemName(string $baseName, array $modifiers): string
{
    if (empty($modifiers)) return $baseName;

    $modNames = collect($modifiers)->pluck('name')->implode(', ');
    return "{$baseName} ({$modNames})";
}

public function updateCustomer(Request $request, $id)
{
    $request->validate(['customer_name' => 'nullable|string|max:100']);
    $order = Order::findOrFail($id);
    $order->update(['customer_name' => $request->customer_name ?? 'Walk-in']);
    return response()->json(['message' => 'Updated', 'customer_name' => $order->customer_name]);
}

public function storeDirect(Request $request)
{
    $request->validate([
        'customer_name'  => 'nullable|string|max:100',
        'type'           => 'nullable|in:takeaway,bar,counter',
        'customer_notes' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        $order = Order::create([
            'order_number'    => Order::generateOrderNumber(),
            'table_id'        => null,   // ← no table
            'user_id'         => auth()->id(),
            'type'            => $request->type ?? 'takeaway',
            'status'          => 'pending',
            'guests'          => 1,
            'subtotal'        => 0,
            'tax_rate'        => 10,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total'           => 0,
            'payment_status'  => 'unpaid',
            'customer_name'   => $request->customer_name ?? 'Walk-in',
            'customer_notes'  => $request->customer_notes,
        ]);

        DB::commit();
        return response()->json($this->orderWithItems($order->id), 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

    public function updateItem(Request $request, $orderId, $itemId)
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'notes'    => 'sometimes|nullable|string|max:255',
            'status'   => 'sometimes|in:pending,preparing,ready,served',
        ]);

        $item = OrderItem::where('order_id', $orderId)->findOrFail($itemId);

        if ($request->has('quantity')) {
            $item->update([
                'quantity'    => $request->quantity,
                'total_price' => $item->unit_price * $request->quantity,
            ]);
        }

        if ($request->has('notes')) {
            $item->update(['notes' => $request->notes]);
        }

        if ($request->has('status')) {
            $item->update(['status' => $request->status]);
        }

        $order = Order::findOrFail($orderId);
        $order->load('activeItems');
        $order->recalculate();

        return response()->json([
            'item'  => $item->fresh(),
            'order' => $this->orderWithItems($orderId),
        ]);
    }

    public function voidItem(Request $request, $orderId, $itemId)
    {
        $item = OrderItem::where('order_id', $orderId)->findOrFail($itemId);
        
        // Only print void notice if item was already sent to kitchen
        $shouldPrintVoid = $item->kot_round && !$item->is_void;
        
        $item->update(['is_void' => true]);

        // Print void notice to kitchen if item was already sent
        if ($shouldPrintVoid) {
            try {
                $printer = new KOTPrinterService();
                $reason = $request->get('reason', 'Customer request');
                $printSuccess = $printer->printVoidNotice(
                    Order::findOrFail($orderId), 
                    $item, 
                    $reason
                );
                
                if ($printSuccess) {
                    \Log::info('Void notice printed for Order #' . Order::find($orderId)->order_number . ' Item: ' . $item->item_name);
                } else {
                    \Log::warning('Void notice print failed for Order #' . Order::find($orderId)->order_number . ' Item: ' . $item->item_name);
                }
            } catch (\Exception $e) {
                \Log::error('Void notice printing error: ' . $e->getMessage());
            }
        }

        $order = Order::findOrFail($orderId);
        $order->load('activeItems');
        $order->recalculate();

        return response()->json([
            'message' => 'Item voided' . ($shouldPrintVoid && isset($printSuccess) && $printSuccess ? " and notice printed" : ""),
            'printed' => $shouldPrintVoid && ($printSuccess ?? false),
            'order'   => $this->orderWithItems($orderId),
        ]);
    }

    public function sendKOT(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        $unsentItems = $order->items()
            ->whereNull('kot_round')
            ->where('is_void', false)
            ->get();

        if ($unsentItems->isEmpty()) {
            return response()->json([
                'message' => 'No new items to send. All items already sent to kitchen.'
            ], 422);
        }

        $nextRound = ($order->items()->max('kot_round') ?? 0) + 1;

        $order->items()
            ->whereNull('kot_round')
            ->where('is_void', false)
            ->update([
                'kot_round'   => $nextRound,
                'kot_sent_at' => now(),
                'status'      => 'preparing',
            ]);

        $order->update([
            'status'      => 'confirmed',
            'kot_sent_at' => $order->kot_sent_at ?? now(),
        ]);

        // Print KOT to kitchen printer (async for faster response)
        try {
            $autoPrint = config('pos.kot.auto_print', true);
            
            if ($autoPrint) {
                // Dispatch to queue for async printing
                PrintKOTJob::dispatch($order, $nextRound, $unsentItems);
                \Log::info('KOT print job dispatched for Order #' . $order->order_number . ' Round ' . $nextRound);
                $printSuccess = true; // Assume success for UI feedback
            } else {
                // Sync printing if auto-print disabled
                $printer = new KOTPrinterService();
                $printSuccess = $printer->printKOT($order, $nextRound, $unsentItems);
                
                if ($printSuccess) {
                    \Log::info('KOT printed successfully for Order #' . $order->order_number . ' Round ' . $nextRound);
                } else {
                    \Log::warning('KOT print failed for Order #' . $order->order_number . ' Round ' . $nextRound);
                }
            }
        } catch (\Exception $e) {
            \Log::error('KOT printing error: ' . $e->getMessage());
            $printSuccess = false;
        }

        try {
            broadcast(new OrderStatusUpdated($order))->toOthers();
        } catch (\Exception $e) {
            // Broadcasting optional — don't fail if Pusher not configured
        }

        return response()->json([
            'message'    => "KOT Round {$nextRound} sent to kitchen" . (isset($printSuccess) && $printSuccess ? " and print dispatched" : ""),
            'round'      => $nextRound,
            'items_sent' => $unsentItems->count(),
            'printed'    => $printSuccess ?? false,
            'order'      => $this->orderWithItems($id),  // ✅ full order returned
        ]);
    }

    /**
     * Manually print KOT for an order
     */
    public function printKOT(Request $request, $id)
    {
        $request->validate([
            'kot_round' => 'nullable|integer|min:1'
        ]);

        $order = Order::with(['items', 'table'])->findOrFail($id);
        $kotRound = $request->kot_round ?? ($order->items()->max('kot_round') ?? 1);

        $items = $order->items()
            ->where('kot_round', $kotRound)
            ->where('is_void', false)
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'No items found for KOT Round ' . $kotRound
            ], 404);
        }

        try {
            $printer = new KOTPrinterService();
            $success = $printer->printKOT($order, $kotRound, $items);

            if ($success) {
                \Log::info('Manual KOT printed for Order #' . $order->order_number . ' Round ' . $kotRound);
                return response()->json([
                    'message' => 'KOT printed successfully',
                    'order' => $order->order_number,
                    'kot_round' => $kotRound,
                    'items_count' => $items->count()
                ]);
            } else {
                return response()->json([
                    'message' => 'KOT print failed',
                    'error' => 'Printer not responding'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Manual KOT print error: ' . $e->getMessage());
            return response()->json([
                'message' => 'KOT print error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,served,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $order->update(['completed_at' => now()]);
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update([
                    'status'           => 'free',
                    'current_order_id' => null,
                ]);
            }
        }

        try {
            broadcast(new OrderStatusUpdated($order))->toOthers();
        } catch (\Exception $e) {}

        return response()->json($this->orderWithItems($id));
    }

    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'payments'           => 'required|array|min:1',
            'payments.*.method'  => 'required|in:cash,card,mobile,voucher,complimentary',
            'payments.*.amount'  => 'required|numeric|min:0.01',
            'payments.*.tendered'=> 'nullable|numeric|min:0',
        ]);

        $order = Order::with('payments')->findOrFail($id);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order is already fully paid'], 422);
        }

        DB::beginTransaction();
        try {
            $totalPaid   = $order->payments->sum('amount');
            $newPayments = [];

            foreach ($request->payments as $p) {
                $tendered     = $p['tendered'] ?? $p['amount'];
                $changeAmount = max(0, $tendered - $p['amount']);

                $payment = \App\Models\Payment::create([
                    'order_id'      => $order->id,
                    'user_id'       => auth()->id(),
                    'method'        => $p['method'],
                    'amount'        => $p['amount'],
                    'tendered'      => $tendered,
                    'change_amount' => $changeAmount,
                    'reference'     => $p['reference'] ?? null,
                    'receipt_number'=> 'RCP-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                ]);

                $totalPaid    += $p['amount'];
                $newPayments[] = $payment;
            }

            $status = $totalPaid >= $order->total ? 'paid' : 'partial';
            $order->update(['payment_status' => $status]);

            if ($status === 'paid') {
                $order->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update([
                        'status'           => 'free',
                        'current_order_id' => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'payments'      => $newPayments,
                'order'         => $this->orderWithItems($id),
                'total_paid'    => $totalPaid,
                'balance_due'   => max(0, $order->total - $totalPaid),
                'change_amount' => $newPayments[0]->change_amount ?? 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function applyDiscount(Request $request, $id)
    {
        $request->validate([
            'discount_type'  => 'required|in:amount,percent',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $order    = Order::findOrFail($id);
        $discount = $request->discount_type === 'percent'
            ? round($order->subtotal * ($request->discount_value / 100), 2)
            : $request->discount_value;

        $order->update([
            'discount_amount' => $discount,
            'total'           => max(0, $order->subtotal + $order->tax_amount - $discount),
        ]);

        return response()->json($this->orderWithItems($id));
    }

    // ── Private helper — always returns full order ────────
    private function orderWithItems($orderId): Order
    {
        return Order::with([
            'items' => function ($q) {
                $q->orderBy('kot_round', 'asc')
                  ->orderBy('created_at', 'asc');
                // ✅ Do NOT filter is_void here
                // Frontend handles filtering so voided items
                // don't cause total mismatches
            },
            'items.menuItem',
            'table',
            'payments',
        ])->findOrFail($orderId);
    }


/**
 * PATCH /api/orders/{id}/service-charge
 * Update or remove service charge before payment
 */
public function updateServiceCharge(Request $request, $id)
{
    $request->validate([
        'tax_rate' => 'required|numeric|min:0|max:100',
    ]);

    $order = Order::findOrFail($id);

    // Only allow service charge updates for table orders (not direct orders)
    if (is_null($order->table_id)) {
        return response()->json([
            'message' => 'Service charge cannot be modified for direct orders'
        ], 422);
    }

    $taxRate   = (float) $request->tax_rate;
    $taxAmount = round($order->subtotal * ($taxRate / 100), 2);
    $total     = round($order->subtotal + $taxAmount - $order->discount_amount, 2);

    $order->update([
        'tax_rate'   => $taxRate,
        'tax_amount' => $taxAmount,
        'total'      => $total,
    ]);

    return response()->json($this->orderWithItems($id));
}

    /**
     * Remove an order item completely
     * DELETE /api/orders/{id}/items/{itemId}
     */
    public function removeItem($id, $itemId)
    {
        $order = Order::findOrFail($id);
        $item = $order->items()->findOrFail($itemId);

        // Only allow removing items that haven't been sent to kitchen
        if ($item->kot_round) {
            return response()->json([
                'message' => 'Cannot remove item that has been sent to kitchen'
            ], 422);
        }

        $item->delete();
        $order->recalculate($order->tax_rate);

        return response()->json($this->orderWithItems($id));
    }

    /**
     * Get kitchen items for specific table
     * GET /api/kitchen/tables/{tableId}/items
     */
    public function getKitchenItemsByTable($tableId)
    {
        $items = OrderItem::with(['order', 'menuItem'])
            ->whereHas('order', function($query) use ($tableId) {
                $query->where('table_id', $tableId)
                      ->where('payment_status', '!=', 'paid')
                      ->whereIn('status', ['pending', 'preparing', 'ready']);
            })
            ->where('is_void', false)
            ->orderBy('kot_round', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function($item) {
                return $item->order_id . '-' . $item->kot_round;
            });

        return response()->json($items);
    }

    /**
     * Get all kitchen items (for main kitchen display)
     * GET /api/kitchen/items
     */
    public function getAllKitchenItems()
    {
        $items = OrderItem::with(['order', 'menuItem', 'order.table'])
            ->whereHas('order', function($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->whereIn('status', ['pending', 'preparing', 'ready']);
            })
            ->where('is_void', false)
            ->orderBy('kot_round', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function($item) {
                return $item->order_id . '-' . $item->kot_round;
            });

        return response()->json($items);
    }

    // ──────────────────────────────────────────────────────────────
    // Pricing resolution
    // ──────────────────────────────────────────────────────────────

    /**
     * Resolve the final unit price for a menu item + selected modifiers.
     *
     * Pricing strategy (per modifier, via pivot):
     *
     *   ABSOLUTE  → add the modifier's custom_price (or default price) on top of base.
     *               e.g. Extra Cheese = Rs.150 fixed, always added as-is.
     *
     *   INCREMENT → add increment_price to the item's base price.
     *               e.g. Rice base Rs.1000 + Normal portion (+200) = Rs.1200
     *                    Koththu base Rs.800 + Full portion (+200) = Rs.1000
     *
     * @param  MenuItem $menuItem
     * @param  int[]    $selectedModifierIds
     * @return float    Final unit price to charge.
     */
    private function resolveUnitPrice(MenuItem $menuItem, array $selectedModifierIds): float
    {
        if (empty($selectedModifierIds)) {
            return (float) $menuItem->price;
        }

        // Delegate to model — all pivot logic lives there
        return $menuItem->computeFinalPrice($selectedModifierIds);
    }

    /**
     * GET /menu/items/{item}/price-preview
     *
     * Returns the computed price for a given set of modifier selections,
     * used by the frontend modifier picker to show live total.
     *
     * Query params: ?modifiers[]=45&modifiers[]=67
     */
    public function pricePreview(Request $request, MenuItem $item)
    {
        $selectedModifiers = $request->input('modifiers', []);

        // Cast to int[]
        $selectedModifiers = array_map('intval', (array) $selectedModifiers);

        $unitPrice = $this->resolveUnitPrice($item, $selectedModifiers);

        return response()->json([
            'menu_item_id' => $item->id,
            'base_price'   => (float) $item->price,
            'unit_price'   => $unitPrice,
            'modifier_ids' => $selectedModifiers,
        ]);
    }


}