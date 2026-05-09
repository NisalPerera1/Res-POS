<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate comprehensive summary report for date range
     * GET /api/reports/summary?from=2026-03-01&to=2026-03-31
     */
    public function summary(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $from = $request->from . ' 00:00:00';
        $to   = $request->to . ' 23:59:59';

        // ── Base queries ──────────────────────────────────────
        $ordersQuery = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid');

        $paymentsQuery = Payment::whereBetween('created_at', [$from, $to]);

        // ── Summary metrics ───────────────────────────────────
        $paidOrders = $ordersQuery->count();
        $totalRevenue = $ordersQuery->sum('total');
        $avgOrderValue = $paidOrders > 0 ? $ordersQuery->avg('total') : 0;
        $totalPayments = $paymentsQuery->count();

        $summary = [
            'total_revenue'    => round($totalRevenue, 2),
            'total_orders'     => $paidOrders,
            'avg_order_value'  => round($avgOrderValue, 2),
            'total_payments'   => $totalPayments,
        ];

        // ── Daily breakdown ───────────────────────────────────
        $daily = Order::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total) as revenue
            ')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($d) => [
                'date'    => $d->date,
                'orders'  => (int) $d->orders,
                'revenue' => round((float) $d->revenue, 2),
            ])
            ->values();

        // ── Hourly breakdown ──────────────────────────────────
        $hourly = Order::selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                SUM(total) as revenue
            ')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($h) => [
                'hour'    => (int) $h->hour,
                'label'   => sprintf('%02d:00', $h->hour),
                'orders'  => (int) $h->orders,
                'revenue' => round((float) $h->revenue, 2),
            ])
            ->values();

        // Fill missing hours with zeros
        $completeHourly = collect(range(0, 23))->map(function($hour) use ($hourly) {
            $existing = $hourly->firstWhere('hour', $hour);
            return $existing ?? [
                'hour'    => $hour,
                'label'   => sprintf('%02d:00', $hour),
                'orders'  => 0,
                'revenue' => 0,
            ];
        });

        // ── Payment methods breakdown ────────────────────────
        $byMethod = Payment::selectRaw('
                method,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('method')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn($m) => [
                'method'      => $m->method,
                'method_label'=> $this->getPaymentMethodLabel($m->method),
                'count'       => (int) $m->count,
                'total'       => round((float) $m->total, 2),
            ])
            ->values();

        // ── Order types breakdown ────────────────────────────
        $byType = Order::selectRaw('
                type,
                COUNT(*) as count,
                SUM(total) as total
            ')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('type')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn($t) => [
                'type'  => $t->type,
                'count' => (int) $t->count,
                'total' => round((float) $t->total, 2),
            ])
            ->values();

        // ── Top selling items ─────────────────────────────────
        $topItems = OrderItem::selectRaw('
                item_name,
                SUM(quantity) as total_qty,
                SUM(total_price) as total_revenue,
                COUNT(DISTINCT order_id) as order_count
            ')
            ->whereHas('order', fn($q) => $q
                ->whereBetween('created_at', [$from, $to])
                ->where('payment_status', 'paid')
            )
            ->where('is_void', false)
            ->groupBy('item_name')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($i) => [
                'item_name'     => $i->item_name,
                'total_qty'     => (int) $i->total_qty,
                'total_revenue' => round((float) $i->total_revenue, 2),
                'order_count'   => (int) $i->order_count,
            ])
            ->values();

        // ── Table performance ────────────────────────────────
        $tablePerf = Order::selectRaw('
                tables.id as table_id,
                tables.name as table_name,
                tables.section as section,
                COUNT(*) as order_count,
                SUM(total) as revenue,
                AVG(total) as avg_order
            ')
            ->join('tables', 'tables.id', '=', 'orders.table_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->whereNotNull('orders.table_id')
            ->groupBy('tables.id', 'tables.name', 'tables.section')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'table_id'   => $t->table_id,
                'table_name' => $t->table_name,
                'section'    => $t->section,
                'order_count'=> (int) $t->order_count,
                'revenue'    => round((float) $t->revenue, 2),
                'avg_order'  => round((float) $t->avg_order, 2),
            ])
            ->values();

        // ── Period info ──────────────────────────────────────
        $period = [
            'from' => $request->from,
            'to'   => $request->to,
            'days' => $this->calculateDaysBetween($request->from, $request->to),
        ];

        return response()->json([
            'period'       => $period,
            'summary'      => $summary,
            'daily'        => $daily,
            'hourly'       => $completeHourly,
            'by_method'    => $byMethod,
            'by_type'      => $byType,
            'top_items'    => $topItems,
            'table_perf'   => $tablePerf,
        ]);
    }

    /**
     * Overall revenue report for dashboard
     * GET /api/reports/today
     */
    public function today()
    {
        $today = now()->format('Y-m-d');
        
        // Get only today's paid orders
        $orders = Order::whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->get();

        $totalRevenue = $orders->sum('total');
        $orderCount   = $orders->count();
        $avgOrder     = $orderCount > 0 ? $orders->avg('total') : 0;

        // Hourly data for today's paid orders only
        $hourly = Order::selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                SUM(total) as revenue
            ')
            ->whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($h) => [
                'hour'    => (int) $h->hour,
                'label'   => sprintf('%02d:00', $h->hour),
                'orders'  => (int) $h->orders,
                'revenue' => round((float) $h->revenue, 2),
            ])
            ->values();

        // Fill missing hours
        $completeHourly = collect(range(0, 23))->map(function($hour) use ($hourly) {
            $existing = $hourly->firstWhere('hour', $hour);
            return $existing ?? [
                'hour'    => $hour,
                'label'   => sprintf('%02d:00', $hour),
                'orders'  => 0,
                'revenue' => 0,
            ];
        });

        return response()->json([
            'date'          => 'All Time',
            'total_revenue' => round($totalRevenue, 2),
            'order_count'   => $orderCount,
            'avg_order'     => round($avgOrder, 2),
            'hourly'        => $completeHourly,
        ]);
    }

    /**
     * Get recent transactions with filtering options
     * GET /api/reports/transactions?limit=50&date=2026-03-25&method=cash&table_id=1
     */
    public function transactions(Request $request)
    {
        $request->validate([
            'limit'    => 'nullable|integer|min:1|max:200',
            'date'     => 'nullable|date',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date|after_or_equal:from',
            'method'   => 'nullable|in:cash,card,mobile,voucher,complimentary',
            'table_id' => 'nullable|integer|exists:tables,id',
            'status'   => 'nullable|in:paid,partial,pending',
        ]);

        $limit = $request->get('limit', 50);

        // Build base query
        $query = Payment::with([
            'order' => fn($q) => $q->with(['table', 'user']),
            'user'
        ]);

        // Apply filters
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->from && $request->to) {
            $query->whereBetween('created_at', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ]);
        } else {
            // Default to today if no date specified
            $query->whereDate('created_at', now()->format('Y-m-d'));
        }

        if ($request->method) {
            $query->where('method', $request->method);
        }

        if ($request->table_id) {
            $query->whereHas('order', fn($q) => $q->where('table_id', $request->table_id));
        }

        if ($request->status) {
            $query->whereHas('order', fn($q) => $q->where('payment_status', $request->status));
        }

        // Get transactions with pagination
        $transactions = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($payment) => [
                'id'             => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'amount'         => round($payment->amount, 2),
                'method'         => $payment->method,
                'method_label'   => $this->getPaymentMethodLabel($payment->method),
                'tendered'       => round($payment->tendered, 2),
                'change_amount'  => round($payment->change_amount, 2),
                'reference'      => $payment->reference,
                'paid_at'        => $payment->created_at->format('Y-m-d H:i:s'),
                'paid_at_time'   => $payment->created_at->format('H:i:s'),
                'paid_at_date'   => $payment->created_at->format('Y-m-d'),
                'cashier'        => $payment->user?->name,
                'order'          => [
                    'id'            => $payment->order->id,
                    'order_number'  => $payment->order->order_number,
                    'type'          => $payment->order->type,
                    'status'        => $payment->order->status,
                    'payment_status'=> $payment->order->payment_status,
                    'total'         => round($payment->order->total, 2),
                    'subtotal'      => round($payment->order->subtotal, 2),
                    'tax_amount'    => round($payment->order->tax_amount, 2),
                    'customer_name' => $payment->order->customer_name,
                    'guests'        => $payment->order->guests,
                    'table'         => $payment->order->table ? [
                        'id'   => $payment->order->table->id,
                        'name' => $payment->order->table->name,
                        'section' => $payment->order->table->section,
                    ] : null,
                    'staff'         => $payment->order->user?->name,
                    'created_at'    => $payment->order->created_at->format('Y-m-d H:i:s'),
                    'completed_at'  => $payment->order->completed_at?->format('Y-m-d H:i:s'),
                ],
            ]);

        // Get summary stats for the filtered results
        $summary = [
            'total_count'   => $transactions->count(),
            'total_amount'  => round($transactions->sum('amount'), 2),
            'avg_amount'    => $transactions->count() > 0 ? round($transactions->sum('amount') / $transactions->count(), 2) : 0,
            'by_method'     => $transactions->groupBy('method')->map(fn($group) => [
                'count' => $group->count(),
                'total' => round($group->sum('amount'), 2),
            ]),
        ];

        return response()->json([
            'transactions' => $transactions,
            'summary'      => $summary,
            'filters'      => [
                'limit'    => $limit,
                'date'     => $request->date ?? ($request->from && $request->to ? "{$request->from} to {$request->to}" : now()->format('Y-m-d')),
                'method'   => $request->method,
                'table_id' => $request->table_id,
                'status'   => $request->status,
            ],
        ]);
    }

    // ── Helper methods ──────────────────────────────────────

    private function getPaymentMethodLabel(string $method): string
    {
        return [
            'cash'          => 'Cash',
            'card'          => 'Card / Tap',
            'mobile'        => 'Mobile Pay',
            'voucher'       => 'Voucher',
            'complimentary' => 'Complimentary',
        ][$method] ?? ucfirst($method);
    }

    private function calculateDaysBetween(string $from, string $to): int
    {
        $start = \Carbon\Carbon::parse($from);
        $end   = \Carbon\Carbon::parse($to);
        return $start->diffInDays($end) + 1;
    }

    /**
     * Get table status for dashboard
     * GET /api/reports/tables
     */
    public function tables()
    {
        $tables = Table::with(['orders' => function($query) {
            $query->where('payment_status', '!=', 'paid')
                ->whereDate('created_at', now()->format('Y-m-d'));
        }])->get();

        $occupied = $tables->filter(function($table) {
            return $table->orders->count() > 0;
        })->count();

        return response()->json([
            'total' => $tables->count(),
            'occupied' => $occupied,
            'available' => $tables->count() - $occupied,
        ]);
    }

    /**
     * Get recent orders for dashboard
     * GET /api/reports/orders?limit=10&sort=created_at:desc
     */
    public function orders(Request $request)
    {
        $limit = $request->get('limit', 10);
        $sort = $request->get('sort', 'created_at:desc');
        
        [$sortField, $sortDirection] = explode(':', $sort);
        
        $orders = Order::with(['table', 'user'])
            ->orderBy($sortField, $sortDirection)
            ->limit($limit)
            ->get();

        return response()->json($orders);
    }

    /**
     * Comprehensive dashboard data
     * GET /api/reports/dashboard
     */
    public function dashboard()
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        
        // Today's data
        $todayFrom = $today . ' 00:00:00';
        $todayTo = $today . ' 23:59:59';
        $yesterdayFrom = $yesterday . ' 00:00:00';
        $yesterdayTo = $yesterday . ' 23:59:59';

        // Stats - Total revenue from all paid orders
        $allPaidOrders = Order::where('payment_status', 'paid');
        $todayOrders = Order::whereBetween('created_at', [$todayFrom, $todayTo])
            ->where('payment_status', 'paid');
        $yesterdayOrders = Order::whereBetween('created_at', [$yesterdayFrom, $yesterdayTo])
            ->where('payment_status', 'paid');

        $totalSales = $allPaidOrders->sum('total');
        $todaySales = $todayOrders->sum('total');
        $yesterdaySales = $yesterdayOrders->sum('total');
        $totalOrderCount = $allPaidOrders->count();
        $todayOrderCount = $todayOrders->count();
        $yesterdayOrderCount = $yesterdayOrders->count();

        // Table status
        $totalTables = Table::count();
        $occupiedTables = Table::where('status', 'occupied')->count();

        // Active staff (simplified - just count all staff for now)
        try {
            $activeStaff = \App\Models\Staff::whereDate('clock_in_at', $today)
                ->whereNull('clock_out_at')
                ->count();
        } catch (\Exception $e) {
            // If clock_in_at column doesn't exist, just count all staff
            $activeStaff = \App\Models\Staff::count();
        }

        // Recent orders
        $recentOrders = Order::with(['table', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'table' => $order->table ? [
                    'id' => $order->table->id,
                    'name' => $order->table->name,
                    'section' => $order->table->section
                ] : null,
                'customer_name' => $order->customer_name,
                'total' => round($order->total, 2),
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'staff' => $order->user?->name
            ]);

        // Tables with status
        $tables = Table::with(['currentOrder'])
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($table) => [
                'id' => $table->id,
                'name' => $table->name,
                'section' => $table->section,
                'status' => $table->status,
                'capacity' => $table->capacity,
                'customer_name' => $table->customer_name,
                'current_order' => $table->currentOrder ? [
                    'id' => $table->currentOrder->id,
                    'order_number' => $table->currentOrder->order_number,
                    'total' => round($table->currentOrder->total, 2)
                ] : null
            ]);

        // Hourly data for today
        $hourlyToday = Order::selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                SUM(total) as revenue
            ')
            ->whereBetween('created_at', [$todayFrom, $todayTo])
            ->where('payment_status', 'paid')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($h) => [
                'hour' => (int) $h->hour,
                'orders' => (int) $h->orders,
                'revenue' => round((float) $h->revenue, 2)
            ])
            ->values();

        // Fill missing hours with zeros
        $completeHourly = collect(range(0, 23))->map(function($hour) use ($hourlyToday) {
            $existing = $hourlyToday->firstWhere('hour', $hour);
            return $existing ?? [
                'hour' => $hour,
                'orders' => 0,
                'revenue' => 0
            ];
        });

        // Category breakdown for all paid orders
        $categoryBreakdown = OrderItem::selectRaw('
                menu_items.category_id,
                categories.name as category_name,
                COUNT(DISTINCT order_items.order_id) as order_count,
                SUM(order_items.total_price) as total_amount
            ')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->whereHas('order', fn($q) => $q->where('payment_status', 'paid'))
            ->where('order_items.is_void', false)
            ->groupBy('menu_items.category_id', 'categories.name')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->category_id,
                'name' => $cat->category_name,
                'orders' => (int) $cat->order_count,
                'amount' => round((float) $cat->total_amount, 2),
                'pct' => $totalSales > 0 ? round(($cat->total_amount / $totalSales) * 100, 1) : 0
            ]);

        return response()->json([
            'stats' => [
                'totalSales' => round($totalSales, 2),
                'todaySales' => round($todaySales, 2),
                'yesterdaySales' => round($yesterdaySales, 2),
                'totalOrders' => $totalOrderCount,
                'todayOrders' => $todayOrderCount,
                'yesterdayOrders' => $yesterdayOrderCount,
                'occupiedTables' => $occupiedTables,
                'totalTables' => $totalTables,
                'activeStaff' => $activeStaff
            ],
            'recentOrders' => $recentOrders,
            'tables' => $tables,
            'hourlyToday' => $completeHourly,
            'categoryBreakdown' => $categoryBreakdown
        ]);
    }
}
