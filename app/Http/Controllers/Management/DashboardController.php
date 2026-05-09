<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ── Statuses considered "revenue-generating" ──────────────────────────────
    // IMPORTANT: Run the debug route first to verify your actual DB values:
    //   GET /debug/order-statuses
    // Then update this array to match whatever statuses your paid orders use.
    private const PAID_STATUSES = ['paid', 'completed', 'settled', 'complete', 'cash', 'card'];

    // Alternatively, exclude-based approach (comment out PAID_STATUSES above and
    // use this in every whereIn call instead):
    //   ->whereNotIn('payment_status', ['pending', 'cancelled', 'unpaid', 'refunded'])

    /**
     * Get comprehensive dashboard statistics
     * Route name: dashboard.stats
     */
    public function stats(Request $request)
    {
        $todayData = $this->getTodayData();
        $weekData  = $this->getWeekData();
        $monthData = $this->getMonthData();

        return response()->json([
            'today' => $todayData,
            'week'  => $weekData,
            'month' => $monthData,
        ]);
    }

    /**
     * Get recent PAID orders for dashboard display
     * Route name: dashboard.recent-orders
     */
    public function recentOrders(Request $request)
    {
        $limit = $request->get('limit', 10);

        $orders = Order::with(['table', 'items'])
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'table_id'       => $order->table_id,
                    'customer_name'  => $order->customer_name,
                    'total_amount'   => $order->total,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at->toISOString(),
                ];
            });

        return response()->json($orders->toArray());
    }

    /**
     * Get hourly revenue data for today
     * GET /api/dashboard/hourly
     */
    public function hourly(Request $request)
    {
        $today = now()->format('Y-m-d');

        $hourlyData = Order::selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                SUM(total) as revenue
            ')
            ->whereDate('created_at', $today)
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($h) => [
                'hour'    => (int) $h->hour,
                'label'   => sprintf('%02d:00', $h->hour),
                'orders'  => (int) $h->orders,
                'revenue' => round((float) $h->revenue, 2),
            ]);

        $completeHourly = [];
        for ($i = 0; $i < 24; $i++) {
            $hourData         = $hourlyData->firstWhere('hour', $i);
            $completeHourly[] = [
                'hour'    => $i,
                'label'   => sprintf('%02d:00', $i),
                'orders'  => $hourData ? $hourData['orders']  : 0,
                'revenue' => $hourData ? $hourData['revenue'] : 0,
            ];
        }

        return response()->json($completeHourly);
    }

    /**
     * Get top menu items for dashboard
     * GET /api/dashboard/top-items
     */
    public function topItems(Request $request)
    {
        $limit = $request->get('limit', 5);

        $topItems = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->whereIn('orders.payment_status', self::PAID_STATUSES)
            ->whereDate('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('menu_items.id', 'menu_items.name')
            ->selectRaw('
                menu_items.id,
                menu_items.name,
                SUM(order_items.quantity) as total_qty,
                SUM(order_items.quantity * order_items.unit_price) as total_revenue
            ')
            ->orderBy('total_qty', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'total_qty'     => (int) $item->total_qty,
                    'total_revenue' => round((float) $item->total_revenue, 2),
                ];
            });

        return response()->json($topItems->toArray());
    }

    /**
     * Get comprehensive dashboard data in a single call
     * Route name: dashboard.index
     */
    public function index(Request $request)
    {
        $dailyData   = $this->getDailyData();
        $weeklyData  = $this->getWeeklyData();
        $monthlyData = $this->getMonthlyData();

        $dailyRecentOrders   = $this->getRecentOrdersByPeriod('today', 10);
        $weeklyRecentOrders  = $this->getRecentOrdersByPeriod('week',  10);
        $monthlyRecentOrders = $this->getRecentOrdersByPeriod('month', 10);

        $hourlyData = $this->getHourlyData();
        $topItems   = $this->getTopItemsData(10);

        return response()->json([
            'daily' => [
                'revenue'         => $dailyData['total_revenue'],
                'orders'          => $dailyData['order_count'],
                'recent_orders'   => $dailyRecentOrders,
                'avg_order_value' => $dailyData['avg_order_value'],
                'date_range'      => $dailyData['date_range'],
                'hourly'          => $hourlyData,
            ],
            'weekly' => [
                'revenue'          => $weeklyData['total_revenue'],
                'orders'           => $weeklyData['order_count'],
                'recent_orders'    => $weeklyRecentOrders,
                'avg_order_value'  => $weeklyData['avg_order_value'],
                'date_range'       => $weeklyData['date_range'],
                'daily_breakdown'  => $weeklyData['daily_breakdown'],
            ],
            'monthly' => [
                'revenue'         => $monthlyData['total_revenue'],
                'orders'          => $monthlyData['order_count'],
                'recent_orders'   => $monthlyRecentOrders,
                'avg_order_value' => $monthlyData['avg_order_value'],
                'date_range'      => $monthlyData['date_range'],
                'top_items'       => $topItems,
            ],
            'summary' => [
                'total_revenue'   => $this->getSummaryData()['total_revenue'],
                'total_orders'    => $this->getSummaryData()['total_orders'],
                'avg_order_value' => $this->getSummaryData()['total_orders'] > 0
                    ? round($this->getSummaryData()['total_revenue'] / $this->getSummaryData()['total_orders'], 2)
                    : 0,
            ],
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getSummaryData(): array
    {
        $totalRevenue = Order::whereIn('payment_status', self::PAID_STATUSES)->sum('total');
        $totalOrders  = Order::whereIn('payment_status', self::PAID_STATUSES)->count();

        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_orders'  => $totalOrders,
        ];
    }

    private function getDailyData(): array
    {
        $today = now()->format('Y-m-d');

        $orders = Order::whereDate('created_at', $today)
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->with(['table', 'items'])
            ->get();

        $totalRevenue  = $orders->sum('total');
        $orderCount    = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return [
            'total_revenue'   => round($totalRevenue, 2),
            'order_count'     => $orderCount,
            'avg_order_value' => round($avgOrderValue, 2),
            'date_range'      => $today,
            'orders'          => $orders->toArray(),
        ];
    }

    // Legacy aliases used by stats() — kept for backward compat
    private function getTodayData(): array { return $this->getDailyData(); }
    private function getWeekData():  array { return $this->getWeeklyData(); }
    private function getMonthData(): array { return $this->getMonthlyData(); }

    private function getWeeklyData(): array
    {
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd   = now()->format('Y-m-d');

        $orders = Order::whereDate('created_at', '>=', $weekStart)
            ->whereDate('created_at', '<=', $weekEnd)
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->with(['table', 'items'])
            ->get();

        $totalRevenue  = $orders->sum('total');
        $orderCount    = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        $dailyBreakdown = [];
        for ($date = $weekStart; $date <= $weekEnd; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
            $dayOrders        = $orders->filter(fn($o) => $o->created_at->format('Y-m-d') === $date);
            $dailyBreakdown[] = [
                'date'    => $date,
                'revenue' => round($dayOrders->sum('total'), 2),
                'orders'  => $dayOrders->count(),
            ];
        }

        return [
            'total_revenue'   => round($totalRevenue, 2),
            'order_count'     => $orderCount,
            'avg_order_value' => round($avgOrderValue, 2),
            'date_range'      => $weekStart . ' to ' . $weekEnd,
            'daily_breakdown' => $dailyBreakdown,
            'orders'          => $orders->toArray(),
        ];
    }

    private function getMonthlyData(): array
    {
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd   = now()->format('Y-m-d');

        $orders = Order::whereDate('created_at', '>=', $monthStart)
            ->whereDate('created_at', '<=', $monthEnd)
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->with(['table', 'items'])
            ->get();

        $totalRevenue  = $orders->sum('total');
        $orderCount    = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return [
            'total_revenue'   => round($totalRevenue, 2),
            'order_count'     => $orderCount,
            'avg_order_value' => round($avgOrderValue, 2),
            'date_range'      => $monthStart . ' to ' . $monthEnd,
            'orders'          => $orders->toArray(),
        ];
    }

    private function getRecentOrdersByPeriod(string $period, int $limit = 10): array
    {
        $query = Order::with(['table', 'items'])
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->orderBy('created_at', 'desc');

        match ($period) {
            'today' => $query->whereDate('created_at', now()->format('Y-m-d')),
            'week'  => $query->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d')),
            'month' => $query->whereDate('created_at', '>=', now()->startOfMonth()->format('Y-m-d')),
            default => null,
        };

        return $query->limit($limit)->get()->map(function ($order) {
            return [
                'id'             => $order->id,
                'order_number'   => $order->order_number,
                'table_id'       => $order->table_id,
                'table_name'     => $order->table ? $order->table->name : 'N/A',
                'customer_name'  => $order->customer_name,
                'total_amount'   => $order->total,
                'status'         => $order->status,
                'payment_status' => $order->payment_status,
                'order_type'     => $order->order_type,
                'created_at'     => $order->created_at->toISOString(),
                'items_count'    => $order->items ? $order->items->count() : 0,
            ];
        })->toArray();
    }

    private function getRecentOrdersData(int $limit = 10): array
    {
        return Order::with(['table'])
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'table_id'       => $order->table_id,
                    'customer_name'  => $order->customer_name,
                    'total_amount'   => $order->total,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at->toISOString(),
                ];
            })->toArray();
    }

    private function getHourlyData(): array
    {
        $today = now()->format('Y-m-d');

        $hourlyData = Order::selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                SUM(total) as revenue
            ')
            ->whereDate('created_at', $today)
            ->whereIn('payment_status', self::PAID_STATUSES)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($h) => [
                'hour'    => (int) $h->hour,
                'label'   => sprintf('%02d:00', $h->hour),
                'orders'  => (int) $h->orders,
                'revenue' => round((float) $h->revenue, 2),
            ]);

        $completeHourly = [];
        for ($i = 0; $i < 24; $i++) {
            $hourData         = $hourlyData->firstWhere('hour', $i);
            $completeHourly[] = [
                'hour'    => $i,
                'label'   => sprintf('%02d:00', $i),
                'orders'  => $hourData ? $hourData['orders']  : 0,
                'revenue' => $hourData ? $hourData['revenue'] : 0,
            ];
        }

        return $completeHourly;
    }

    private function getTopItemsData(int $limit = 5): array
    {
        return Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->whereIn('orders.payment_status', self::PAID_STATUSES)
            ->whereDate('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('menu_items.id', 'menu_items.name')
            ->selectRaw('
                menu_items.id,
                menu_items.name,
                SUM(order_items.quantity) as total_qty,
                SUM(order_items.quantity * order_items.unit_price) as total_revenue
            ')
            ->orderBy('total_qty', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'total_qty'     => (int) $item->total_qty,
                    'total_revenue' => round((float) $item->total_revenue, 2),
                ];
            })->toArray();
    }
}