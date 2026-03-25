<?php
namespace App\Http\Controllers;

use App\Models\Order;
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
                'tax_rate'        => 10,
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
            'payments',
        ])->findOrFail($id);
    }
}