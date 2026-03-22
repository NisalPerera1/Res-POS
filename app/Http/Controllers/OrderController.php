<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\MenuItem;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'table_id'        => $request->table_id,
                'user_id'         => auth()->id(),
                'type'            => $request->type ?? 'dine_in',
                'status'          => 'pending',
                'guests'          => $request->guests ?? 1,
                'subtotal'        => 0,
                'tax_rate'        => 10,
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
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity'     => 'required|integer|min:1',
            'modifiers'    => 'nullable|array',
            'notes'        => 'nullable|string|max:255',
        ]);

        $order    = Order::findOrFail($orderId);
        $menuItem = MenuItem::findOrFail($request->menu_item_id);

        if (!$menuItem->is_available) {
            return response()->json(['message' => 'Item is not available'], 422);
        }

        $modifierCost      = 0;
        $selectedModifiers = [];
        if ($request->modifiers) {
            foreach ($request->modifiers as $modId) {
                $modifier = \App\Models\Modifier::find($modId);
                if ($modifier) {
                    $modifierCost       += $modifier->price;
                    $selectedModifiers[] = [
                        'id'    => $modifier->id,
                        'name'  => $modifier->name,
                        'price' => $modifier->price,
                    ];
                }
            }
        }

        $unitPrice  = $menuItem->price + $modifierCost;
        $totalPrice = $unitPrice * $request->quantity;

        // Only merge with existing UNSENT item (kot_round is null)
        $existingUnsent = OrderItem::where('order_id', $orderId)
            ->where('menu_item_id', $menuItem->id)
            ->whereNull('kot_round')
            ->where('is_void', false)
            ->first();

        if ($existingUnsent) {
            $newQty = $existingUnsent->quantity + $request->quantity;
            $existingUnsent->update([
                'quantity'    => $newQty,
                'total_price' => $existingUnsent->unit_price * $newQty,
            ]);
            $item = $existingUnsent->fresh();
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
                'status'       => 'pending',
                'kot_round'    => null,
            ]);
        }

        // Recalculate totals
        $order->load('activeItems');
        $order->recalculate();

        return response()->json([
            'item'  => $item->load('menuItem'),
            'order' => $this->orderWithItems($orderId),
        ]);
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
        $item->update(['is_void' => true, 'status' => 'void']);

        $order = Order::findOrFail($orderId);
        $order->load('activeItems');
        $order->recalculate();

        return response()->json([
            'message' => 'Item voided',
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

        try {
            broadcast(new OrderStatusUpdated($order))->toOthers();
        } catch (\Exception $e) {
            // Broadcasting optional — don't fail if Pusher not configured
        }

        return response()->json([
            'message'    => "KOT Round {$nextRound} sent to kitchen",
            'round'      => $nextRound,
            'items_sent' => $unsentItems->count(),
            'order'      => $this->orderWithItems($id),  // ✅ full order returned
        ]);
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
}