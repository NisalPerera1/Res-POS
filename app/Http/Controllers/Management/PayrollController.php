<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Staff;
use App\Models\Advance;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with('staff');
        
        if ($request->has('month_year')) {
            $query->where('month_year', $request->month_year);
        }
        
        return $query->get();
    }

    public function generatePayroll(Request $request)
    {
        $validated = $request->validate([
            'month_year' => 'required|string', // Format: 2024-01
        ]);

        // Get service charge from orders
        $totalServiceCharge = $this->getServiceChargeFromOrders($validated['month_year']);
        
        // Get active staff
        $activeStaff = Staff::where('status', 'Active')->get();
        $serviceChargePerStaff = $activeStaff->count() > 0 ? $totalServiceCharge / $activeStaff->count() : 0;

        $payrollEntries = [];
        foreach ($activeStaff as $staff) {
            // Check if payroll already exists for this staff and month
            $existing = Payroll::where('staff_id', $staff->id)
                              ->where('month_year', $validated['month_year'])
                              ->first();

            if (!$existing) {
                // Get advance deduction for this month
                $advanceDeduction = $this->getAdvanceDeductionForMonth($staff->id, $validated['month_year']);
                
                $payroll = Payroll::create([
                    'staff_id' => $staff->id,
                    'employee_name' => $staff->name,
                    'role' => $staff->role,
                    'status' => 'Pending',
                    'basic_salary' => $staff->salary,
                    'service_charge' => $serviceChargePerStaff,
                    'bonuses' => 0,
                    'advance_deduction' => $advanceDeduction,
                    'leave_deduction' => 0,
                    'net_salary' => $staff->salary + $serviceChargePerStaff - $advanceDeduction,
                    'method' => 'Bank Transfer',
                    'payroll_date' => now(),
                    'month_year' => $validated['month_year'],
                ]);

                $payroll->calculateNetSalary();
                $payrollEntries[] = $payroll;
            }
        }

        return response()->json([
            'message' => 'Payroll generated successfully',
            'service_charge_per_staff' => $serviceChargePerStaff,
            'total_service_charge' => $totalServiceCharge,
            'entries_created' => count($payrollEntries)
        ]);
    }

    public function updateBonuses(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'bonuses' => 'required|numeric|min:0',
        ]);

        $payroll->bonuses = $validated['bonuses'];
        $payroll->calculateNetSalary();
        $payroll->save();

        return response()->json($payroll);
    }

    private function getServiceChargeFromOrders($monthYear)
    {
        // This should connect to your orders table
        // For now, returning a calculated value
        // In real implementation, you'd query orders table:
        // Order::whereMonth('created_at', '=', $month)
        //       ->whereYear('created_at', '=', $year)
        //       ->sum('service_charge');
        
        // Mock calculation - replace with actual query
        return 250000; // Rs. 250,000 total service charge
    }

    private function getAdvanceDeductionForMonth($staffId, $monthYear)
    {
        $advances = Advance::where('staff_id', $staffId)
                          ->where('status', 'Active')
                          ->where('remaining_balance', '>', 0)
                          ->get();

        $totalDeduction = 0;
        foreach ($advances as $advance) {
            $totalDeduction += min($advance->monthly_deduction, $advance->remaining_balance);
        }

        return $totalDeduction;
    }

    public function distributeServiceCharge(Request $request)
    {
        $validated = $request->validate([
            'month_year' => 'required|string',
        ]);

        $totalServiceCharge = $this->getServiceChargeFromOrders($validated['month_year']);
        $activeStaffCount = Staff::where('status', 'Active')->count();
        $serviceChargePerStaff = $activeStaffCount > 0 ? $totalServiceCharge / $activeStaffCount : 0;

        // Update existing payroll entries
        Payroll::where('month_year', $validated['month_year'])
              ->update([
                  'service_charge' => $serviceChargePerStaff
              ]);

        return response()->json([
            'message' => 'Service charge distributed successfully',
            'total_service_charge' => $totalServiceCharge,
            'active_staff_count' => $activeStaffCount,
            'service_charge_per_staff' => $serviceChargePerStaff
        ]);
    }
}
