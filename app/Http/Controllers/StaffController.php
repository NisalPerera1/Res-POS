<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift;
use App\Models\Payroll;
use App\Models\LeaveRequest;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffController extends Controller
{
    // ══════════════════════════════════════════
    // STAFF CRUD
    // ══════════════════════════════════════════

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->role)   $query->where('role', $request->role);
        if ($request->active) $query->where('is_active', $request->active === 'true');
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('employee_id', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $staff = $query->orderBy('name')->get();

        // Add computed fields
        $staff = $staff->map(fn($u) => array_merge($u->toArray(), [
            'role_label'       => $u->role_label,
            'years_of_service' => $u->years_of_service,
        ]));

        return response()->json($staff);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        // Recent shifts
        $recentShifts = Shift::where('user_id', $id)
            ->orderByDesc('shift_date')
            ->limit(10)
            ->get();

        // Latest payroll
        $latestPayroll = Payroll::where('user_id', $id)
            ->orderByDesc('year')->orderByDesc('month')
            ->first();

        // This month stats
        $thisMonth = $this->getMonthStats($id, now()->year, now()->month);

        return response()->json([
            'staff'          => array_merge($user->toArray(), [
                'role_label'       => $user->role_label,
                'years_of_service' => $user->years_of_service,
            ]),
            'recent_shifts'  => $recentShifts,
            'latest_payroll' => $latestPayroll,
            'this_month'     => $thisMonth,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:100',
            'pin'                => 'required|string|min:4|max:6',
            'role'               => 'required|in:admin,manager,cashier,waiter,kitchen,bartender,delivery',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'address'            => 'nullable|string|max:255',
            'salary_type'        => 'in:monthly,daily,hourly',
            'base_salary'        => 'nullable|numeric|min:0',
            'service_charge_pct' => 'nullable|numeric|min:0|max:100',
            'join_date'          => 'nullable|date',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account'       => 'nullable|string|max:50',
        ]);

        // Check PIN not already used
        $users = User::all();
        foreach ($users as $u) {
            if (Hash::check($request->pin, $u->pin)) {
                return response()->json(['message' => 'This PIN is already in use by another staff member'], 422);
            }
        }

        $staff = User::create([
            'name'               => $request->name,
            'pin'                => Hash::make($request->pin),
            'role'               => $request->role,
            'phone'              => $request->phone,
            'email'              => $request->email,
            'address'            => $request->address,
            'salary_type'        => $request->salary_type ?? 'monthly',
            'base_salary'        => $request->base_salary ?? 0,
            'service_charge_pct' => $request->service_charge_pct ?? 0,
            'join_date'          => $request->join_date,
            'bank_name'          => $request->bank_name,
            'bank_account'       => $request->bank_account,
            'employee_id'        => User::generateEmployeeId(),
            'is_active'          => true,
        ]);

        return response()->json(array_merge($staff->toArray(), [
            'role_label' => $staff->role_label,
        ]), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'               => 'sometimes|string|max:100',
            'role'               => 'sometimes|in:admin,manager,cashier,waiter,kitchen,bartender,delivery',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'address'            => 'nullable|string|max:255',
            'salary_type'        => 'in:monthly,daily,hourly',
            'base_salary'        => 'nullable|numeric|min:0',
            'service_charge_pct' => 'nullable|numeric|min:0|max:100',
            'join_date'          => 'nullable|date',
            'is_active'          => 'boolean',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account'       => 'nullable|string|max:50',
        ]);

        $staff = User::findOrFail($id);
        $staff->update($request->except('pin', 'employee_id'));

        // Update PIN separately if provided
        if ($request->filled('pin')) {
            $request->validate(['pin' => 'string|min:4|max:6']);
            // Check not used by others
            $others = User::where('id', '!=', $id)->get();
            foreach ($others as $u) {
                if (Hash::check($request->pin, $u->pin)) {
                    return response()->json(['message' => 'This PIN is already in use'], 422);
                }
            }
            $staff->update(['pin' => Hash::make($request->pin)]);
        }

        return response()->json(array_merge($staff->fresh()->toArray(), [
            'role_label' => $staff->role_label,
        ]));
    }

    public function destroy($id)
    {
        $staff = User::findOrFail($id);
        // Soft delete — just deactivate
        $staff->update(['is_active' => false]);
        return response()->json(['message' => 'Staff deactivated']);
    }

    public function toggleActive($id)
    {
        $staff = User::findOrFail($id);
        $staff->update(['is_active' => !$staff->is_active]);
        return response()->json(['is_active' => $staff->is_active]);
    }

    // ══════════════════════════════════════════
    // SHIFTS
    // ══════════════════════════════════════════

    public function clockIn(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        // Check not already clocked in today
        $existing = Shift::where('user_id', $id)
            ->where('shift_date', today())
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already clocked in', 'shift' => $existing], 422);
        }

        $shift = Shift::create([
            'user_id'    => $id,
            'shift_date' => today(),
            'clock_in'   => now(),
            'status'     => 'active',
        ]);

        return response()->json($shift, 201);
    }

    public function clockOut(Request $request, $id)
    {
        $shift = Shift::where('user_id', $id)
            ->where('shift_date', today())
            ->where('status', 'active')
            ->firstOrFail();

        $clockIn      = Carbon::parse($shift->clock_in);
        $clockOut     = now();
        $hoursWorked  = round($clockIn->diffInMinutes($clockOut) / 60, 2);

        $shift->update([
            'clock_out'    => $clockOut,
            'hours_worked' => $hoursWorked,
            'status'       => 'completed',
        ]);

        return response()->json($shift->fresh());
    }

    public function shifts(Request $request, $id)
    {
        $query = Shift::where('user_id', $id)->orderByDesc('shift_date');

        if ($request->month && $request->year) {
            $query->whereYear('shift_date', $request->year)
                  ->whereMonth('shift_date', $request->month);
        }

        return response()->json($query->paginate(20));
    }

    public function storeShift(Request $request, $id)
    {
        $request->validate([
            'shift_date'  => 'required|date',
            'clock_in'    => 'required|date_format:H:i',
            'clock_out'   => 'nullable|date_format:H:i',
            'tips_collected' => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        $clockIn   = Carbon::parse($request->shift_date . ' ' . $request->clock_in);
        $clockOut  = $request->clock_out
            ? Carbon::parse($request->shift_date . ' ' . $request->clock_out)
            : null;
        $hours     = $clockOut ? round($clockIn->diffInMinutes($clockOut) / 60, 2) : 0;

        $shift = Shift::create([
            'user_id'        => $id,
            'shift_date'     => $request->shift_date,
            'clock_in'       => $clockIn,
            'clock_out'      => $clockOut,
            'hours_worked'   => $hours,
            'tips_collected' => $request->tips_collected ?? 0,
            'notes'          => $request->notes,
            'status'         => $clockOut ? 'completed' : 'active',
        ]);

        return response()->json($shift, 201);
    }

    public function updateShift(Request $request, $id, $shiftId)
    {
        $shift = Shift::where('user_id', $id)->findOrFail($shiftId);
        $shift->update($request->all());

        // Recalculate hours if times changed
        if ($shift->clock_in && $shift->clock_out) {
            $hours = round(Carbon::parse($shift->clock_in)->diffInMinutes(Carbon::parse($shift->clock_out)) / 60, 2);
            $shift->update(['hours_worked' => $hours]);
        }

        return response()->json($shift->fresh());
    }

    // ══════════════════════════════════════════
    // PAYROLL
    // ══════════════════════════════════════════

    public function payrolls(Request $request, $id)
    {
        $payrolls = Payroll::where('user_id', $id)
            ->orderByDesc('year')->orderByDesc('month')
            ->get()
            ->map(fn($p) => array_merge($p->toArray(), ['month_label' => $p->month_label]));

        return response()->json($payrolls);
    }

    /**
     * Calculate payroll for a staff member for a given month
     * Service charge is split equally among all active staff that month
     */
    public function calculatePayroll(Request $request, $id)
    {
        $request->validate([
            'year'       => 'required|integer',
            'month'      => 'required|integer|min:1|max:12',
            'bonus'      => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes'      => 'nullable|string',
        ]);

        $year  = $request->year;
        $month = $request->month;
        $staff = User::findOrFail($id);

        DB::beginTransaction();
        try {
            $stats = $this->getMonthStats($id, $year, $month);

            // Total service charges collected that month (tax_amount represents service charge)
            // Using same logic as ReportController
            $totalServiceCharge = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('payment_status', 'paid')
                ->whereNotNull('table_id') // Only table orders have service charge
                ->sum('tax_amount');

            // Count active staff that month (who worked at least 1 shift)
            $activeStaffCount = Shift::whereYear('shift_date', $year)
                ->whereMonth('shift_date', $month)
                ->where('status', 'completed')
                ->distinct('user_id')
                ->count('user_id');

            // Equal split of service charge
            $serviceChargeShare = $activeStaffCount > 0
                ? round($totalServiceCharge / $activeStaffCount, 2)
                : 0;

            // If staff has custom percentage, use that instead
            if ($staff->service_charge_pct > 0) {
                $serviceChargeShare = round($totalServiceCharge * ($staff->service_charge_pct / 100), 2);
            }

            // Calculate base salary
            $baseSalary = 0;
            if ($staff->salary_type === 'monthly') {
                $baseSalary = $staff->base_salary;
            } elseif ($staff->salary_type === 'daily') {
                $baseSalary = $staff->base_salary * $stats['days_worked'];
            } elseif ($staff->salary_type === 'hourly') {
                $baseSalary = $staff->hourly_rate * $stats['hours_worked'];
            }

            $tips       = $stats['total_tips'];
            $bonus      = $request->bonus ?? 0;
            $deductions = $request->deductions ?? 0;
            $grossPay   = $baseSalary + $serviceChargeShare + $tips + $bonus;
            $netPay     = max(0, $grossPay - $deductions);

            // Upsert payroll record
            $payroll = Payroll::updateOrCreate(
                ['user_id' => $id, 'year' => $year, 'month' => $month],
                [
                    'days_worked'          => $stats['days_worked'],
                    'hours_worked'         => $stats['hours_worked'],
                    'base_salary'          => round($baseSalary, 2),
                    'service_charge_share' => $serviceChargeShare,
                    'tips'                 => $tips,
                    'bonus'                => $bonus,
                    'deductions'           => $deductions,
                    'gross_pay'            => round($grossPay, 2),
                    'net_pay'              => round($netPay, 2),
                    'notes'                => $request->notes,
                    'status'               => 'draft',
                ]
            );

            DB::commit();
            return response()->json(array_merge($payroll->toArray(), [
                'month_label'           => $payroll->month_label,
                'total_service_charge'  => $totalServiceCharge,
                'active_staff_count'    => $activeStaffCount,
            ]));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updatePayroll(Request $request, $id, $payrollId)
    {
        $payroll = Payroll::where('user_id', $id)->findOrFail($payrollId);
        $request->validate([
            'bonus'      => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'status'     => 'in:draft,approved,paid',
            'notes'      => 'nullable|string',
        ]);

        $payroll->update($request->only('bonus','deductions','status','notes','base_salary','service_charge_share','tips'));

        // Recalculate
        $gross = $payroll->base_salary + $payroll->service_charge_share
               + $payroll->tips + $payroll->bonus;
        $net   = max(0, $gross - $payroll->deductions);

        $payroll->update([
            'gross_pay' => round($gross, 2),
            'net_pay'   => round($net, 2),
            'paid_at'   => $request->status === 'paid' ? now() : $payroll->paid_at,
        ]);

        return response()->json(array_merge($payroll->fresh()->toArray(), [
            'month_label' => $payroll->month_label,
        ]));
    }

    // ══════════════════════════════════════════
    // LEAVE REQUESTS
    // ══════════════════════════════════════════

    public function leaveRequests($id)
    {
        return response()->json(
            LeaveRequest::where('user_id', $id)
                ->with('approver:id,name')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function storeLeave(Request $request, $id)
    {
        $request->validate([
            'type'      => 'required|in:sick,annual,unpaid,other',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'reason'    => 'nullable|string',
        ]);

        $days  = Carbon::parse($request->from_date)
            ->diffInDays(Carbon::parse($request->to_date)) + 1;

        $leave = LeaveRequest::create([
            'user_id'   => $id,
            'type'      => $request->type,
            'from_date' => $request->from_date,
            'to_date'   => $request->to_date,
            'days'      => $days,
            'reason'    => $request->reason,
            'status'    => 'pending',
        ]);

        return response()->json($leave, 201);
    }

    public function updateLeave(Request $request, $id, $leaveId)
    {
        $leave = LeaveRequest::where('user_id', $id)->findOrFail($leaveId);
        $request->validate(['status' => 'required|in:approved,rejected']);

        $leave->update([
            'status'      => $request->status,
            'approved_by' => auth()->id(),
        ]);

        return response()->json($leave->fresh(['approver:id,name']));
    }

    // ══════════════════════════════════════════
    // OVERVIEW / DASHBOARD
    // ══════════════════════════════════════════

    public function overview()
    {
        $totalStaff    = User::where('is_active', true)->count();
        $onShift       = Shift::where('shift_date', today())->where('status', 'active')->count();
        $pendingLeave  = LeaveRequest::where('status', 'pending')->count();
        $pendingPayroll= Payroll::where('status', 'draft')->count();

        // Staff on shift today with their names
        $todayShifts = Shift::where('shift_date', today())
            ->with('user:id,name,role,avatar')
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'user_id'     => $s->user_id,
                'name'        => $s->user?->name,
                'role'        => $s->user?->role,
                'clock_in'    => $s->clock_in?->format('H:i'),
                'clock_out'   => $s->clock_out?->format('H:i'),
                'hours'       => $s->formatted_hours,
                'status'      => $s->status,
            ]);

        // Service charge this month (tax_amount represents service charge)
        // Using same logic as ReportController
        $serviceChargeMonth = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('payment_status', 'paid')
            ->whereNotNull('table_id') // Only table orders have service charge
            ->sum('tax_amount');

        // Per-staff service charge share this month
        $activeStaffThisMonth = Shift::whereYear('shift_date', now()->year)
            ->whereMonth('shift_date', now()->month)
            ->where('status', 'completed')
            ->distinct('user_id')
            ->count('user_id');

        $perStaffShare = $activeStaffThisMonth > 0
            ? round($serviceChargeMonth / $activeStaffThisMonth, 2)
            : 0;

        return response()->json([
            'total_staff'         => $totalStaff,
            'on_shift_today'      => $onShift,
            'pending_leave'       => $pendingLeave,
            'pending_payroll'     => $pendingPayroll,
            'today_shifts'        => $todayShifts,
            'service_charge_month'=> round($serviceChargeMonth, 2),
            'active_staff_month'  => $activeStaffThisMonth,
            'per_staff_share'     => $perStaffShare,
        ]);
    }

    // ── Private helper ─────────────────────────────────
    private function getMonthStats(int $userId, int $year, int $month): array
    {
        $shifts = Shift::where('user_id', $userId)
            ->whereYear('shift_date', $year)
            ->whereMonth('shift_date', $month)
            ->where('status', 'completed')
            ->get();

        return [
            'days_worked'  => $shifts->count(),
            'hours_worked' => round($shifts->sum('hours_worked'), 2),
            'total_tips'   => round($shifts->sum('tips_collected'), 2),
        ];
    }

    // All leaves (for leave tab)
    public function allLeaves()
    {
        $leaves = LeaveRequest::with('user:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($l) => array_merge($l->toArray(), [
                'user_name' => $l->user?->name,
            ]));
        return response()->json($leaves);
    }

    // Payroll list for a month
    public function payrollList(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $payrolls = Payroll::where('year', $year)
            ->where('month', $month)
            ->with('user:id,name,role')
            ->get()
            ->map(fn($p) => array_merge($p->toArray(), [
                'user_name' => $p->user?->name,
            ]));

        $serviceTotal = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('payment_status', 'paid')
            ->whereNotNull('table_id') // Only table orders have service charge
            ->sum('tax_amount');

        $activeCount = Shift::whereYear('shift_date', $year)
            ->whereMonth('shift_date', $month)
            ->where('status', 'completed')
            ->distinct('user_id')->count('user_id');

        return response()->json([
            'payrolls'              => $payrolls,
            'service_charge_total'  => round($serviceTotal, 2),
            'per_staff_share'       => $activeCount > 0 ? round($serviceTotal / $activeCount, 2) : 0,
        ]);
    }

    // Generate payroll for all active staff
    public function generateAllPayrolls(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $staff = User::where('is_active', true)->get();
        $count = 0;

        foreach ($staff as $s) {
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['year' => $year, 'month' => $month, 'bonus' => 0, 'deductions' => 0]);
            $this->calculatePayroll($fakeRequest, $s->id);
            $count++;
        }

        return response()->json(['message' => "Generated {$count} payrolls"]);
    }
}