<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Process payment — supports single and split payments
     * POST /api/orders/{id}/payments
     */
    public function processPayment(Request $request, $orderId)
    {
        $request->validate([
            'payments'              => 'required|array|min:1',
            'payments.*.method'     => 'required|in:cash,card,mobile,voucher,complimentary',
            'payments.*.amount'     => 'required|numeric|min:0.01',
            'payments.*.tendered'   => 'nullable|numeric|min:0',
            'payments.*.reference'  => 'nullable|string|max:100',
        ]);

        $order = Order::with(['payments', 'items', 'table', 'user'])
            ->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Order is already fully paid'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $previouslyPaid = $order->payments->sum('amount');
            $newPayments    = [];

            foreach ($request->payments as $p) {
                $tendered     = isset($p['tendered']) ? (float) $p['tendered'] : (float) $p['amount'];
                $changeAmount = max(0, $tendered - (float) $p['amount']);

                $payment = Payment::create([
                    'order_id'       => $order->id,
                    'user_id'        => auth()->id(),
                    'method'         => $p['method'],
                    'amount'         => (float) $p['amount'],
                    'tendered'       => $tendered,
                    'change_amount'  => $changeAmount,
                    'reference'      => $p['reference'] ?? null,
                    'receipt_number' => $this->generateReceiptNumber(),
                ]);

                $newPayments[] = $payment;
            }

            $totalPaid = $previouslyPaid + collect($newPayments)->sum('amount');
            $status    = $totalPaid >= (float) $order->total ? 'paid' : 'partial';

            $order->update(['payment_status' => $status]);

            if ($status === 'paid') {
                $order->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                // Mark all order items as served when payment is completed
                OrderItem::where('order_id', $order->id)
                    ->where('is_void', false)
                    ->where('status', '!=', 'served')
                    ->update([
                        'status' => 'served',
                    ]);

                // Free the table
                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update([
                        'status'           => 'free',
                        'current_order_id' => null,
                    ]);
                }
            }

            DB::commit();

            // Load full order with all relations for response
            $freshOrder = Order::with([
                'items.menuItem',
                'payments',
                'table',
                'user',
            ])->findOrFail($orderId);

            return response()->json([
                'success'        => true,
                'payments'       => $newPayments,
                'order'          => $freshOrder,
                'total_paid'     => round($totalPaid, 2),
                'balance_due'    => max(0, round((float) $order->total - $totalPaid, 2)),
                'change_amount'  => $newPayments[0]->change_amount ?? 0,
                'receipt_number' => $newPayments[0]->receipt_number ?? null,
                'payment_status' => $status,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment failed: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get receipt data for an order
     * GET /api/orders/{id}/receipt
     */
    public function receipt($orderId)
    {
        $order = Order::with([
            'items' => fn($q) => $q->where('is_void', false)->orderBy('kot_round')->orderBy('created_at'),
            'items.menuItem',
            'payments',
            'payments.user',
            'table',
            'user',
        ])->findOrFail($orderId);

        // Group items by kot_round for receipt display
        $itemsByRound = $order->items
            ->groupBy(fn($item) => $item->kot_round === 0 ? 'instant' : ($item->kot_round ?? 'pending'))
            ->toArray();

        return response()->json([
            'receipt' => [
                'restaurant_name' => config('app.name', 'Restaurant POS'),
                'receipt_number'  => $order->payments->first()?->receipt_number ?? 'N/A',
                'printed_at'      => now()->format('d M Y H:i'),
                'cashier'         => $order->user?->name ?? auth()->user()?->name ?? 'Staff',
            ],
            'order'         => [
                'id'             => $order->id,
                'order_number'   => $order->order_number,
                'type'           => $order->type,
                'status'         => $order->status,
                'payment_status' => $order->payment_status,
                'table'          => $order->table?->name,
                'customer_name'  => $order->customer_name,
                'guests'         => $order->guests,
                'notes'          => $order->notes,
                'created_at'     => $order->created_at->format('d M Y H:i'),
                'completed_at'   => $order->completed_at?->format('d M Y H:i'),
            ],
            'items'    => $order->items->map(fn($item) => [
                'id'          => $item->id,
                'name'        => $item->item_name,
                'quantity'    => $item->quantity,
                'unit_price'  => number_format($item->unit_price, 2),
                'total_price' => number_format($item->total_price, 2),
                'modifiers'   => $item->modifiers ?? [],
                'notes'       => $item->notes,
                'status'      => $item->status,
                'kot_round'   => $item->kot_round,
            ]),
            'totals'   => [
                'subtotal'        => number_format($order->subtotal, 2),
                'tax_rate'        => $order->tax_rate,
                'tax_amount'      => number_format($order->tax_amount, 2),
                'discount_amount' => number_format($order->discount_amount, 2),
                'total'           => number_format($order->total, 2),
            ],
            'payments' => $order->payments->map(fn($p) => [
                'id'             => $p->id,
                'method'         => $p->method,
                'method_label'   => $p->method_label,
                'amount'         => number_format($p->amount, 2),
                'tendered'       => number_format($p->tendered, 2),
                'change_amount'  => number_format($p->change_amount, 2),
                'receipt_number' => $p->receipt_number,
                'reference'      => $p->reference,
                'cashier'        => $p->user?->name,
                'paid_at'        => $p->created_at->format('d M Y H:i'),
            ]),
        ]);
    }

    /**
     * List all payments (admin)
     * GET /api/payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['order.table', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->method) {
            $query->where('method', $request->method);
        }

        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->order_id) {
            $query->where('order_id', $request->order_id);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Payment summary / daily report
     * GET /api/payments/summary
     */
    public function summary(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $payments = Payment::with('order')
            ->whereDate('created_at', $date)
            ->get();

        $byMethod = $payments->groupBy('method')->map(fn($group) => [
            'count'  => $group->count(),
            'total'  => round($group->sum('amount'), 2),
        ]);

        return response()->json([
            'date'          => $date,
            'total_revenue' => round($payments->sum('amount'), 2),
            'total_orders'  => $payments->pluck('order_id')->unique()->count(),
            'by_method'     => $byMethod,
            'payments'      => $payments->map(fn($p) => [
                'receipt_number' => $p->receipt_number,
                'method'         => $p->method,
                'amount'         => number_format($p->amount, 2),
                'order_number'   => $p->order->order_number ?? 'N/A',
                'paid_at'        => $p->created_at->format('H:i'),
            ]),
        ]);
    }

    private function generateReceiptNumber(): string
    {
        // Format: RCP-YYYYMMDD-XXXX
        $date   = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        return "RCP-{$date}-{$random}";
    }
}