<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Process payment for an order (supports split payments)
     */
    public function processPayment(Request $request, $orderId)
    {
        $request->validate([
            'payments'             => 'required|array|min:1',
            'payments.*.method'   => 'required|in:cash,card,mobile,voucher,complimentary',
            'payments.*.amount'   => 'required|numeric|min:0.01',
            'payments.*.tendered' => 'nullable|numeric|min:0',
        ]);

        $order = Order::with('payments')->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order is already fully paid'], 422);
        }

        DB::beginTransaction();
        try {
            $totalPaid    = $order->payments->sum('amount');
            $newPayments  = [];

            foreach ($request->payments as $p) {
                $tendered     = $p['tendered'] ?? $p['amount'];
                $changeAmount = max(0, $tendered - $p['amount']);

                $payment = Payment::create([
                    'order_id'       => $order->id,
                    'user_id'        => auth()->id(),
                    'method'         => $p['method'],
                    'amount'         => $p['amount'],
                    'tendered'       => $tendered,
                    'change_amount'  => $changeAmount,
                    'reference'      => $p['reference'] ?? null,
                    'receipt_number' => $this->generateReceiptNumber(),
                ]);

                $totalPaid      += $p['amount'];
                $newPayments[]   = $payment;
            }

            // Update payment status
            $status = $totalPaid >= $order->total ? 'paid' : 'partial';
            $order->update(['payment_status' => $status]);

            if ($status === 'paid') {
                $order->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                // Free the table
                if ($order->table_id) {
                    \App\Models\Table::where('id', $order->table_id)->update([
                        'status'           => 'free',
                        'current_order_id' => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'payments'       => $newPayments,
                'order'          => $order->fresh(['payments', 'table']),
                'total_paid'     => $totalPaid,
                'balance_due'    => max(0, $order->total - $totalPaid),
                'change_amount'  => $newPayments[0]['change_amount'] ?? 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate receipt data
     */
    public function receipt($orderId)
    {
        $order = Order::with([
            'table', 'user', 'items.menuItem', 'payments'
        ])->findOrFail($orderId);

        return response()->json([
            'order'           => $order,
            'restaurant_name' => config('app.name'),
            'printed_at'      => now()->format('d M Y H:i'),
        ]);
    }

    private function generateReceiptNumber(): string
    {
        return 'RCP-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}