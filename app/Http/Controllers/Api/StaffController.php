<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffAdvance;
use App\Models\StaffAttendance;
use App\Models\MonthlyServiceCharge;
use App\Models\StaffServiceCharge;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // STAFF CRUD
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request): JsonResponse
    {
        $query = Staff::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('employee_id', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|unique:staff,email',
            'nic'              => 'nullable|string|max:20|unique:staff,nic',
            'address'          => 'nullable|string',
            'joined_date'      => 'required|date',
            'role'             => 'required|in:admin,manager,cashier,waiter,kitchen,bartender,delivery',
            'salary_type'      => 'required|in:monthly,daily,hourly',
            'base_salary'      => 'required|numeric|min:0',
            'is_active'        => 'boolean',
            'bank_name'        => 'nullable|string',
            'bank_account'     => 'nullable|string',
            'emergency_contact_name'  => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);

        // Set default service charge percentage (will be calculated dynamically)
        $data['service_charge_pct'] = 0;

        $staff = Staff::create($data);

        return response()->json($staff->fresh(), 201);
    }

    public function show(Staff $staff): JsonResponse
    {
        return response()->json($staff->load(['leaves', 'advances', 'payrolls']));
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'sometimes|required|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'email'            => ['nullable', 'email', Rule::unique('staff', 'email')->ignore($staff->id)],
            'nic'              => ['nullable', 'string', 'max:20', Rule::unique('staff', 'nic')->ignore($staff->id)],
            'address'          => 'nullable|string',
            'joined_date'      => 'sometimes|required|date',
            'role'             => 'sometimes|required|in:admin,manager,cashier,waiter,kitchen,bartender,delivery',
            'salary_type'      => 'sometimes|required|in:monthly,daily,hourly',
            'base_salary'      => 'sometimes|required|numeric|min:0',
            'service_charge_pct' => 'sometimes|required|numeric|min:0|max:100',
            'is_active'        => 'boolean',
            'bank_name'        => 'nullable|string',
            'bank_account'     => 'nullable|string',
            'emergency_contact_name'  => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);

        $staff->update($data);

        return response()->json($staff->fresh());
    }

    public function destroy(Staff $staff): JsonResponse
    {
        // Soft delete — preserves historical records
        $staff->delete();

        return response()->json(['message' => 'Staff member removed.']);
    }

    // ═══════════════════════════════════════════════════════════
    // LEAVES
    // ═══════════════════════════════════════════════════════════

    public function getLeaves(Request $request): JsonResponse
    {
        $query = StaffLeave::with('staff:id,name,employee_id');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        } elseif ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        return response()->json($query->orderBy('date', 'desc')->get());
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'staff_id'        => 'required|exists:staff,id',
            'date'            => 'required|date',
            'type'            => 'required|in:full_day,half_day',
            'half_day_period' => 'nullable|in:morning,afternoon',
            'reason'          => 'nullable|string|max:255',
            'is_paid'         => 'boolean',
        ]);

        // Prevent duplicates
        $existing = StaffLeave::where('staff_id', $data['staff_id'])
            ->where('date', $data['date'])
            ->where('type', $data['type'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'A leave record already exists for this date and type.'], 422);
        }

        $leave = StaffLeave::create($data);

        return response()->json($leave->load('staff:id,name,employee_id'), 201);
    }

    public function updateLeave(Request $request, StaffLeave $leave): JsonResponse
    {
        $data = $request->validate([
            'staff_id'        => 'sometimes|required|exists:staff,id',
            'date'            => 'sometimes|required|date',
            'type'            => 'sometimes|required|in:full_day,half_day',
            'half_day_period' => 'nullable|in:morning,afternoon',
            'reason'          => 'nullable|string|max:255',
            'is_paid'         => 'boolean',
        ]);

        $leave->update($data);

        return response()->json($leave->fresh()->load('staff:id,name,employee_id'));
    }

    public function destroyLeave(StaffLeave $leave): JsonResponse
    {
        $leave->delete();

        return response()->json(['message' => 'Leave record deleted.']);
    }

    // ═══════════════════════════════════════════════════════════
    // ADVANCES
    // ═══════════════════════════════════════════════════════════

    public function getAdvances(Request $request): JsonResponse
    {
        $query = StaffAdvance::with('staff:id,name,employee_id');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->where('deduct_month', $request->month)
                  ->where('deduct_year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('date', 'desc')->get());
    }

    public function storeAdvance(Request $request): JsonResponse
    {
        Log::info('Store advance request received', ['request_data' => $request->all()]);

        $data = $request->validate([
            'staff_id'     => 'required|exists:staff,id',
            'date'         => 'required|date',
            'amount'       => 'required|numeric|min:1',
            'reason'       => 'nullable|string|max:255',
            'deduct_month' => 'nullable|integer|min:1|max:12',
            'deduct_year'  => 'nullable|integer|min:2020',
            'notes'        => 'nullable|string',
        ]);

        // Auto-approve all advances - no approval workflow needed
        $data['status'] = 'approved';

        Log::info('Validated advance data', ['validated_data' => $data]);

        try {
            $advance = StaffAdvance::create($data);
            
            Log::info('StaffAdvance created', [
                'advance_id' => $advance->id,
                'exists' => $advance->exists,
                'attributes' => $advance->getAttributes()
            ]);

            // Verify it actually persisted
            if (!$advance->exists || !$advance->id) {
                Log::error('StaffAdvance::create silently failed', ['data' => $data]);
                return response()->json(['message' => 'Failed to save advance — check logs.'], 500);
            }

            return response()->json(
                $advance->fresh()->load('staff:id,name,employee_id'),
                201
            );
        } catch (\Exception $e) {
            Log::error('StaffAdvance store error: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function updateAdvance(Request $request, StaffAdvance $advance): JsonResponse
    {
        $data = $request->validate([
            'amount'       => 'sometimes|numeric|min:1',
            'reason'       => 'nullable|string|max:255',
            'status'       => 'in:pending,approved,rejected,deducted',
            'deduct_month' => 'nullable|integer|min:1|max:12',
            'deduct_year'  => 'nullable|integer|min:2020',
            'notes'        => 'nullable|string',
        ]);

        $advance->update($data);

        return response()->json($advance->fresh()->load('staff:id,name,employee_id'));
    }

    public function destroyAdvance(StaffAdvance $advance): JsonResponse
    {
        $advance->delete();

        return response()->json(['message' => 'Advance record deleted.']);
    }

    // ═══════════════════════════════════════════════════════════
    // ATTENDANCE
    // ═══════════════════════════════════════════════════════════

    public function getAttendance(Request $request): JsonResponse
    {
        $query = StaffAttendance::with('staff:id,name,employee_id');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year);
        }

        return response()->json($query->orderBy('date', 'desc')->get());
    }

    public function storeAttendance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'staff_id'       => 'required|exists:staff,id',
            'date'           => 'required|date',
            'check_in'       => 'nullable|date_format:H:i',
            'check_out'      => 'nullable|date_format:H:i',
            'status'         => 'required|in:present,absent,late,half_day',
            'notes'          => 'nullable|string',
        ]);

        // Compute worked_minutes if both times present
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $in  = Carbon::createFromFormat('H:i', $data['check_in']);
            $out = Carbon::createFromFormat('H:i', $data['check_out']);
            $data['worked_minutes'] = max(0, $in->diffInMinutes($out));
        }

        $record = StaffAttendance::updateOrCreate(
            ['staff_id' => $data['staff_id'], 'date' => $data['date']],
            $data
        );

        return response()->json($record->load('staff:id,name,employee_id'), 201);
    }

    // ═══════════════════════════════════════════════════════════
    // SERVICE CHARGE DISTRIBUTION
    // ═══════════════════════════════════════════════════════════

    public function getServiceChargeDistribution(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        // Auto-calculate service charge from completed table orders
        $total = DB::table('orders')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('payment_status', 'paid')
            ->whereNotNull('table_id') // Only table orders have service charge
            ->sum('tax_amount'); // tax_amount is actually service charge

        $activeStaff = Staff::active()
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->get();

        $staffCount = $activeStaff->count();
        
        if ($staffCount === 0) {
            return response()->json([
                'total_service_charge' => $total,
                'distributed' => false,
                'distribution' => []
            ]);
        }

        // Calculate equal percentage for each staff member
        $equalPercentage = round(100 / $staffCount, 2);
        $sharePerStaff = $staffCount > 0 ? round($total / $staffCount, 2) : 0;

        $distribution = $activeStaff->map(function (Staff $s) use ($equalPercentage, $sharePerStaff) {
            return [
                'staff_id'          => $s->id,
                'name'              => $s->name,
                'employee_id'       => $s->employee_id,
                'service_charge_pct' => $equalPercentage,
                'share_amount'      => $sharePerStaff,
            ];
        });

        return response()->json([
            'month'                => (int) $month,
            'year'                 => (int) $year,
            'total_service_charge' => $total,
            'distribution'         => $distribution,
        ]);
    }

    public function updateServiceChargePool(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month'        => 'required|integer|min:1|max:12',
            'year'         => 'required|integer|min:2020',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $monthly = MonthlyServiceCharge::updateOrCreate(
            ['month' => $data['month'], 'year' => $data['year']],
            ['total_amount' => $data['total_amount']]
        );

        // Recompute per-staff shares
        $activeStaff = Staff::active()->get();
        foreach ($activeStaff as $s) {
            StaffServiceCharge::updateOrCreate(
                ['staff_id' => $s->id, 'month' => $data['month'], 'year' => $data['year']],
                [
                    'monthly_service_charge_id' => $monthly->id,
                    'service_charge_pct'         => $s->service_charge_pct,
                    'share_amount'               => round($data['total_amount'] * ($s->service_charge_pct / 100), 2),
                ]
            );
        }

        return response()->json(['message' => 'Service charge pool updated.', 'monthly' => $monthly]);
    }

    // ═══════════════════════════════════════════════════════════
    // PAYROLLS
    // ═══════════════════════════════════════════════════════════

    public function getPayrolls(Request $request): JsonResponse
    {
        $query = Payroll::with('staff:id,name,employee_id,role');

        if ($request->filled('month')) $query->where('month', $request->month);
        if ($request->filled('year'))  $query->where('year',  $request->year);
        if ($request->filled('staff_id')) $query->where('staff_id', $request->staff_id);
        if ($request->filled('status')) $query->where('status', $request->status);

        return response()->json($query->orderBy('staff_id')->get());
    }

    /**
     * Generate payroll for one or more staff members for a given month.
     * Automatically calculates:
     *  - leave deductions (unpaid leaves × daily rate)
     *  - advance deductions (approved advances due this month)
     *  - service charge share
     */
    public function generatePayroll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer|min:2020',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:staff,id',
        ]);

        $month = (int) $data['month'];
        $year  = (int) $data['year'];

        // Working days in this month (Mon–Sat = 26 approx; we compute actual)
        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        // Fetch service charge distribution for this month
        $monthly = MonthlyServiceCharge::where('month', $month)->where('year', $year)->first();

        $generated = [];

        DB::transaction(function () use ($data, $month, $year, $workingDays, $monthly, &$generated) {
            foreach ($data['staff_ids'] as $staffId) {
                $staff = Staff::findOrFail($staffId);

                // Skip if already approved/paid
                $existing = Payroll::where('staff_id', $staffId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->whereIn('status', ['approved', 'paid'])
                    ->first();

                if ($existing) continue;

                // ── Leaves ──────────────────────────────────────────
                $leaveData  = $staff->getLeavesForMonth($month, $year);
                $unpaidDays = $leaveData['unpaidFull'] + ($leaveData['unpaidHalf'] * 0.5);

                $dailyRate   = $staff->base_salary / max(1, $workingDays);
                $leaveDeduct = round($dailyRate * $unpaidDays, 2);

                // ── Advances ─────────────────────────────────────────
                $advanceDeduct = $staff->getAdvancesForMonth($month, $year);

                // ── Service charge ──────────────────────────────────
                $svcCharge = 0.0;
                
                // Auto-calculate monthly service charge from orders and distribute equally
                $monthlyServiceChargeTotal = DB::table('orders')
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->where('payment_status', 'paid')
                    ->whereNotNull('table_id') // Only table orders have service charge
                    ->sum('tax_amount'); // tax_amount is actually service charge
                
                if ($monthlyServiceChargeTotal > 0) {
                    // Get count of active staff for equal distribution
                    $activeStaffCount = Staff::where('is_active', true)->count();
                    
                    if ($activeStaffCount > 0) {
                        // Distribute service charge equally among active staff
                        $svcCharge = round($monthlyServiceChargeTotal / $activeStaffCount, 2);
                        
                        // Create/update staff service charge record for tracking
                        StaffServiceCharge::updateOrCreate(
                            [
                                'staff_id' => $staffId,
                                'month'    => $month,
                                'year'     => $year
                            ],
                            [
                                'service_charge_pct' => round((100 / $activeStaffCount), 2),
                                'share_amount'       => $svcCharge
                            ]
                        );
                    }
                }

                // ── Build payroll record ─────────────────────────────
                $payroll = Payroll::updateOrCreate(
                    ['staff_id' => $staffId, 'month' => $month, 'year' => $year],
                    [
                        'base_salary'        => $staff->base_salary,
                        'service_charge'     => $svcCharge,
                        'bonus'              => 0,
                        'overtime_pay'       => 0,
                        'leave_deductions'   => $leaveDeduct,
                        'advance_deductions' => $advanceDeduct,
                        'other_deductions'   => 0,
                        'working_days'       => $workingDays,
                        'leaves_taken'       => $leaveData['fullDays'],
                        'half_leaves_taken'  => $leaveData['halfDays'],
                        'status'             => 'draft',
                    ]
                );

                $payroll->recompute();
                $payroll->save();

                // Mark advances as deducted
                if ($advanceDeduct > 0) {
                    StaffAdvance::where('staff_id', $staffId)
                        ->where('status', 'approved')
                        ->where('deduct_month', $month)
                        ->where('deduct_year', $year)
                        ->update(['status' => 'deducted']);
                }

                $generated[] = $payroll->load('staff:id,name,employee_id');
            }
        });

        return response()->json([
            'message'   => count($generated) . ' payroll record(s) generated.',
            'payrolls'  => $generated,
        ], 201);
    }

    public function updatePayroll(Request $request, Payroll $payroll): JsonResponse
    {
        if (in_array($payroll->status, ['paid'])) {
            return response()->json(['message' => 'Cannot edit a paid payroll.'], 422);
        }

        $data = $request->validate([
            'bonus'            => 'sometimes|numeric|min:0',
            'overtime_pay'     => 'sometimes|numeric|min:0',
            'other_deductions' => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|in:draft,approved,paid',
            'paid_date'        => 'nullable|date',
            'payment_method'   => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);

        $payroll->fill($data);
        $payroll->recompute();
        $payroll->save();

        return response()->json($payroll->fresh()->load('staff:id,name,employee_id'));
    }

    // ── Summary for a given month ────────────────────────────
    public function monthlySummary(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $payrolls = Payroll::with('staff:id,name,employee_id,role')
            ->where('month', $month)->where('year', $year)->get();

        return response()->json([
            'month'            => (int) $month,
            'year'             => (int) $year,
            'total_staff'      => $payrolls->count(),
            'total_gross'      => $payrolls->sum('gross_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net'        => $payrolls->sum('net_pay'),
            'total_service'    => $payrolls->sum('service_charge'),
            'by_status'        => $payrolls->groupBy('status')->map->count(),
            'payrolls'         => $payrolls,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // ── Service Charge Distribution ──────────────────────────────────────
    
    /**
     * Collect service charge from orders and distribute to active staff
     * POST /api/staff/service-charge-distribute
     */
    public function distributeServiceCharge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2020|max:2030',
        ]);

        try {
            DB::beginTransaction();

            // Get service charge from completed orders for the month
            $serviceChargeTotal = DB::table('orders')
                ->whereMonth('created_at', $data['month'])
                ->whereYear('created_at', $data['year'])
                ->where('payment_status', 'paid')
                ->whereNotNull('table_id') // Only table orders have service charge
                ->sum('tax_amount'); // tax_amount is actually service charge

            if ($serviceChargeTotal <= 0) {
                return response()->json([
                    'message' => 'No service charge found for the selected period',
                    'total_amount' => 0
                ]);
            }

            // Get active staff members
            $activeStaff = Staff::where('is_active', true)->get();
            
            if ($activeStaff->isEmpty()) {
                return response()->json([
                    'message' => 'No active staff members found',
                    'total_amount' => $serviceChargeTotal
                ]);
            }

            $staffCount = $activeStaff->count();
            $sharePerStaff = round($serviceChargeTotal / $staffCount, 2);

            // Create or update monthly service charge record
            $monthlyServiceCharge = MonthlyServiceCharge::updateOrCreate(
                [
                    'month' => $data['month'],
                    'year'  => $data['year']
                ],
                [
                    'total_amount' => $serviceChargeTotal
                ]
            );

            // Distribute to each staff member
            foreach ($activeStaff as $staff) {
                StaffServiceCharge::updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'month'    => $data['month'],
                        'year'     => $data['year']
                    ],
                    [
                        'monthly_service_charge_id' => $monthlyServiceCharge->id,
                        'service_charge_pct'        => round((100 / $staffCount), 2),
                        'share_amount'              => $sharePerStaff
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Service charge distributed successfully',
                'total_amount' => $serviceChargeTotal,
                'staff_count' => $staffCount,
                'share_per_staff' => $sharePerStaff,
                'month' => $data['month'],
                'year' => $data['year']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service charge distribution error: ' . $e->getMessage(), [
                'month' => $data['month'],
                'year' => $data['year']
            ]);
            
            return response()->json([
                'message' => 'Failed to distribute service charge: ' . $e->getMessage()
            ], 500);
        }
    }

    
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    private function getWorkingDaysInMonth(int $month, int $year): int
    {
        $date  = Carbon::create($year, $month, 1);
        $days  = 0;
        $end   = $date->copy()->endOfMonth();

        while ($date->lte($end)) {
            // Count Mon–Sat (adjust if your restaurant is open on Sunday too)
            if (!$date->isSunday()) {
                $days++;
            }
            $date->addDay();
        }

        return $days;
    }
}