<?php
namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectOrderController extends Controller
{
    /**
     * GET /api/direct-orders/pending
     * All unpaid direct orders (no table_id)
     */
    public function getPendingOrders()
    {
        $orders = Order::with([
            'items'          => fn($q) => $q->where('is_void', false)->orderBy('created_at'),
            'items.menuItem',
        ])
        ->whereNull('table_id')
        ->where('payment_status', '!=', 'paid')
        ->whereHas('items', function($query) {
            $query->whereNotNull('kot_round');
        })
        ->orderByDesc('created_at')
        ->get();

        return response()->json($orders);
    }

    /**
     * POST /api/direct-orders
     * Create a new direct order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'type'          => 'nullable|in:takeaway,dine_in,bar,counter,delivery',
            'customer_name' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'table_id'        => null,
                'user_id'         => auth()->id(),
                'type'            => $request->type ?? 'takeaway',
                'status'          => 'pending',
                'guests'          => 1,
                'subtotal'        => 0,
                'tax_rate'        => 0, // 0% service charge for direct orders
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'total'           => 0,
                'payment_status'  => 'unpaid',
                'customer_name'   => $request->customer_name ?? 'Walk-in',
            ]);

            DB::commit();
            return response()->json($this->fullOrder($order->id), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/direct-orders/{id}
     * Get a specific direct order with all items
     */
    public function getOrder($id)
    {
        $order = Order::whereNull('table_id')->find($id);

        if (! $order) {
            return response()->json(['message' => 'Direct order not found.'], 404);
        }

        return response()->json($this->fullOrder($id));
    }

    /**
     * POST /api/direct-orders/{id}/switch
     * Switch to a direct order — validates it exists, is direct, and is unpaid
     */
    public function switchOrder($id)
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (! is_null($order->table_id)) {
            return response()->json(['message' => 'This is a table order, not a direct order.'], 422);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order has already been paid.'], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Order has been cancelled.'], 422);
        }

        return response()->json($this->fullOrder($id));
    }

    /**
     * PATCH /api/direct-orders/{id}/customer
     * Update customer name
     */
    public function updateCustomer(Request $request, $id)
    {
        $request->validate(['customer_name' => 'nullable|string|max:100']);

        $order = Order::whereNull('table_id')->find($id);

        if (! $order) {
            return response()->json(['message' => 'Direct order not found.'], 404);
        }

        $order->update(['customer_name' => $request->customer_name ?? 'Walk-in']);

        return response()->json(['customer_name' => $order->customer_name]);
    }

    /**
     * PATCH /api/direct-orders/{id}/type
     * Update order type (takeaway / dine_in / bar / counter / delivery)
     */
    public function updateType(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:takeaway,dine_in,bar,counter,delivery',
        ]);

        $order = Order::whereNull('table_id')
            ->where('payment_status', '!=', 'paid')
            ->find($id);

        if (! $order) {
            return response()->json(['message' => 'Direct order not found or already paid.'], 404);
        }

        $order->update(['type' => $request->type]);

        return response()->json(['type' => $order->type]);
    }

    /**
     * POST /api/direct-orders/{id}/cancel
     * Cancel an unpaid direct order
     */
    public function cancelOrder($id)
    {
        $order = Order::whereNull('table_id')
            ->where('payment_status', '!=', 'paid')
            ->find($id);

        if (! $order) {
            return response()->json(['message' => 'Direct order not found or already paid.'], 404);
        }

        DB::beginTransaction();
        try {
            $order->items()->update(['is_void' => true]);
            $order->update([
                'status'          => 'cancelled',
                'subtotal'        => 0,
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'total'           => 0,
            ]);

            DB::commit();
            return response()->json(['message' => 'Order cancelled.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/direct-orders/{id}
     * Delete a pending direct order
     */
    public function deleteOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            
            // Only allow deletion of orders without KOT items or unpaid orders
            if ($order->payment_status === 'paid') {
                return response()->json(['message' => 'Cannot delete paid orders'], 422);
            }
            
            $order->delete();
            
            DB::commit();
            return response()->json(['message' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/direct-orders/{id}/items/{itemId}/addons
     * Add an add-on to a direct order item
     */
    public function addAddon(Request $request, $orderId, $orderItemId)
    {
        $request->validate([
            'addon_name' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|in:grams,kg,pieces,units',
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
            'is_custom_price' => 'nullable|boolean',
        ]);

        $orderItem = OrderItem::where('order_id', $orderId)
            ->findOrFail($orderItemId);

        // Calculate unit price from total and quantity
        $unitPrice = $request->total_price / $request->quantity;

        $addon = OrderItemAddon::create([
            'order_item_id' => $orderItem->id,
            'addon_name' => $request->addon_name,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'unit_price' => $unitPrice,
            'total_price' => $request->total_price,
            'notes' => $request->notes,
            'is_custom_price' => $request->boolean('is_custom_price', false),
        ]);

        // Update order totals
        $this->updateOrderTotals($orderId);

        return response()->json($addon->load('orderItem'), 201);
    }

    /**
     * PUT /api/direct-orders/{id}/items/{itemId}/addons/{addonId}
     * Update an existing add-on
     */
    public function updateAddon(Request $request, $orderId, $orderItemId, $addonId)
    {
        $request->validate([
            'addon_name' => 'sometimes|string|max:100',
            'quantity' => 'sometimes|numeric|min:0.01',
            'unit' => 'sometimes|in:grams,kg,pieces,units',
            'total_price' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string|max:255',
            'is_custom_price' => 'sometimes|boolean',
        ]);

        $addon = OrderItemAddon::whereHas('orderItem', function ($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->findOrFail($addonId);

        // Update fields
        if ($request->has('quantity') && $request->has('total_price')) {
            $addon->unit_price = $request->total_price / $request->quantity;
        }

        $addon->update($request->only([
            'addon_name', 'quantity', 'unit', 'total_price', 
            'notes', 'is_custom_price'
        ]));

        // Update order totals
        $this->updateOrderTotals($orderId);

        return response()->json($addon->load('orderItem'));
    }

    /**
     * DELETE /api/direct-orders/{id}/items/{itemId}/addons/{addonId}
     * Remove an add-on from an order item
     */
    public function deleteAddon($orderId, $orderItemId, $addonId)
    {
        $addon = OrderItemAddon::whereHas('orderItem', function ($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->findOrFail($addonId);

        $addon->delete();

        // Update order totals
        $this->updateOrderTotals($orderId);

        return response()->json(['message' => 'Addon deleted successfully']);
    }

    /**
     * Update order totals after addon changes
     */
    private function updateOrderTotals($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $subtotal = 0;
        foreach ($order->items as $item) {
            if (!$item->is_void) {
                $itemTotal = $item->total_price;
                // Add addon totals
                $itemTotal += $item->addons->sum('total_price');
                $subtotal += $itemTotal;
            }
        }

        $order->subtotal = $subtotal;
        $order->tax_amount = $subtotal * ($order->tax_rate / 100);
        $order->total = $subtotal + $order->tax_amount;
        $order->save();
    }

    // ── Private helper ──────────────────────────────────────────────────────────
    private function fullOrder($id): Order
    {
        return Order::with([
            'items' => function ($q) {
                $q->where('is_void', false)
                  ->orderBy('kot_round', 'asc')
                  ->orderBy('created_at', 'asc');
            },
            'items.menuItem',
            'items.addons',
            'payments',
        ])->findOrFail($id);
    }
}