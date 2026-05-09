<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\POS\OrderController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QrOrderController extends Controller
{
    /**
     * QR/online ordering: create a DIRECT order and immediately send items to kitchen.
     *
     * POST /api/qr/orders
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'type' => 'required|in:dine_in,takeaway,delivery',
            'customer_name' => 'nullable|string|max:100',
            'customer_notes' => 'nullable|string|max:500',
            'guests' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|integer|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selected_modifiers' => 'nullable|array',
            'items.*.selected_modifiers.*' => 'integer|exists:modifiers,id',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.is_instant' => 'nullable|boolean',
        ]);

        $tableId = $validated['table_id'] ?? null;

        $orderController = app(OrderController::class);

        DB::beginTransaction();
        try {
            // DIRECT order: by convention, table_id must be NULL.
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'table_id' => null,
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'status' => 'pending',
                'guests' => $validated['guests'] ?? 1,
                'subtotal' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => 0,
                'payment_status' => 'unpaid',
                'customer_name' => $validated['customer_name'] ?? 'Walk-in',
                'customer_notes' => $validated['customer_notes'] ?? null,
                'notes' => $tableId ? ('QR Table: ' . $tableId) : null,
            ]);

            foreach ($validated['items'] as $item) {
                $addItemRequest = Request::create('/api/qr/orders/items', 'POST', [
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'selected_modifiers' => $item['selected_modifiers'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'is_instant' => $item['is_instant'] ?? null,
                ]);

                $resp = $orderController->addItem($addItemRequest, $order->id);
                if ($resp->getStatusCode() >= 400) {
                    throw new \RuntimeException($resp->getData(true)['message'] ?? 'Failed to add item');
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 422);
        }

        // Send to kitchen (best-effort): this updates order/item state and triggers printing.
        try {
            $sendKotRequest = Request::create('/api/qr/orders/send-kot', 'POST', []);
            return $orderController->sendKOT($sendKotRequest, $order->id);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Order created, but failed to send to kitchen',
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

