<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\ServiceCharge;
use App\Models\ServiceChargeDistribution;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Generate payroll for all active staff for a given month
     */
    public function generateMonthlyPayroll(int $month, int $year): array
    {
        $payrolls = [];
        $activeStaff = Staff::active()->get();

        foreach ($activeStaff as $staff) {
            $payroll = $this->generateStaffPayroll($staff, $month, $year);
            $payrolls[] = $payroll;
        }

        return $payrolls;
    }

    /**
     * Generate payroll for a specific staff member
     */
    public function generateStaffPayroll(Staff $staff, int $month, int $year): Payroll
    {
        // Check if payroll already exists
        $existingPayroll = Payroll::where('staff_id', $staff->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existingPayroll) {
            return $existingPayroll;
        }

        // Calculate leave deductions
        $leaveDeductions = $this->calculateLeaveDeductions($staff, $month, $year);

        // Get service charge share
        $serviceChargeShare = $this->getServiceChargeShare($staff, $month, $year);

        // Calculate final salary
        $finalSalary = $staff->base_salary - $leaveDeductions + $serviceChargeShare;

        return Payroll::create([
            'staff_id' => $staff->id,
            'month' => $month,
            'year' => $year,
            'base_salary' => $staff->base_salary,
            'total_leave_deductions' => $leaveDeductions,
            'bonus' => 0,
            'service_charge_share' => $serviceChargeShare,
            'final_salary' => $finalSalary,
            'status' => 'draft',
        ]);
    }

    /**
     * Calculate leave deductions for a staff member
     */
    public function calculateLeaveDeductions(Staff $staff, int $month, int $year): float
    {
        $leaves = StaffLeave::where('staff_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $totalDeductions = 0;

        foreach ($leaves as $leave) {
            if ($leave->type === 'full_day') {
                $totalDeductions += $staff->daily_rate;
            } elseif ($leave->type === 'half_day') {
                $totalDeductions += $staff->daily_rate / 2;
            }
        }

        return $totalDeductions;
    }

    /**
     * Get service charge share for a staff member
     */
    public function getServiceChargeShare(Staff $staff, int $month, int $year): float
    {
        return ServiceChargeDistribution::where('staff_id', $staff->id)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('amount');
    }

    /**
     * Distribute service charge equally among active staff
     */
    public function distributeServiceChargeEqually(float $amount, int $month, int $year): array
    {
        return DB::transaction(function () use ($amount, $month, $year) {
            $activeStaff = Staff::active()->get();
            $staffCount = $activeStaff->count();

            if ($staffCount === 0) {
                throw new \Exception('No active staff found for distribution');
            }

            $sharePerStaff = $amount / $staffCount;
            $distributions = [];

            // Update or create service charge record
            $serviceCharge = ServiceCharge::updateOrCreate(
                ['month' => $month, 'year' => $year],
                [
                    'total_amount' => $amount,
                    'distributed_amount' => DB::raw('distributed_amount + ' . $amount),
                    'remaining_amount' => DB::raw('remaining_amount - ' . $amount),
                ]
            );

            foreach ($activeStaff as $staff) {
                $distribution = ServiceChargeDistribution::create([
                    'staff_id' => $staff->id,
                    'amount' => $sharePerStaff,
                    'percentage' => round((100 / $staffCount), 2),
                    'distribution_date' => now(),
                    'month' => $month,
                    'year' => $year,
                ]);

                $distributions[] = $distribution;
            }

            return $distributions;
        });
    }

    /**
     * Distribute service charge by custom percentages
     */
    public function distributeServiceChargeByPercentage(array $distributionData, int $month, int $year): array
    {
        return DB::transaction(function () use ($distributionData, $month, $year) {
            $totalAmount = array_sum(array_column($distributionData, 'amount'));
            $distributions = [];

            // Update or create service charge record
            $serviceCharge = ServiceCharge::updateOrCreate(
                ['month' => $month, 'year' => $year],
                [
                    'total_amount' => $totalAmount,
                    'distributed_amount' => DB::raw('distributed_amount + ' . $totalAmount),
                    'remaining_amount' => DB::raw('remaining_amount - ' . $totalAmount),
                ]
            );

            foreach ($distributionData as $data) {
                $distribution = ServiceChargeDistribution::create([
                    'staff_id' => $data['staff_id'],
                    'amount' => $data['amount'],
                    'percentage' => $data['percentage'] ?? null,
                    'distribution_date' => now(),
                    'month' => $month,
                    'year' => $year,
                ]);

                $distributions[] = $distribution;
            }

            return $distributions;
        });
    }

    /**
     * Get payroll summary for a month
     */
    public function getPayrollSummary(int $month, int $year): array
    {
        $payrolls = Payroll::forMonth($month, $year)->get();

        return [
            'month' => $month,
            'year' => $year,
            'total_staff' => $payrolls->count(),
            'total_base_salary' => $payrolls->sum('base_salary'),
            'total_leave_deductions' => $payrolls->sum('total_leave_deductions'),
            'total_bonus' => $payrolls->sum('bonus'),
            'total_service_charge_share' => $payrolls->sum('service_charge_share'),
            'total_final_salary' => $payrolls->sum('final_salary'),
            'status_breakdown' => $payrolls->groupBy('status')->map->count(),
            'payrolls' => $payrolls->load('staff'),
        ];
    }

    /**
     * Update payroll bonus
     */
    public function updatePayrollBonus(Payroll $payroll, float $bonus): Payroll
    {
        if ($payroll->status === 'paid') {
            throw new \Exception('Cannot update bonus for paid payroll');
        }

        $payroll->bonus = $bonus;
        $payroll->final_salary = $payroll->base_salary 
            - $payroll->total_leave_deductions 
            + $bonus 
            + $payroll->service_charge_share;
        $payroll->save();

        return $payroll;
    }

    /**
     * Approve payroll
     */
    public function approvePayroll(Payroll $payroll): Payroll
    {
        if ($payroll->status === 'paid') {
            throw new \Exception('Payroll is already paid');
        }

        $payroll->status = 'approved';
        $payroll->save();

        return $payroll;
    }

    /**
     * Mark payroll as paid
     */
    public function markPayrollAsPaid(Payroll $payroll): Payroll
    {
        if ($payroll->status !== 'approved') {
            throw new \Exception('Payroll must be approved before marking as paid');
        }

        $payroll->status = 'paid';
        $payroll->save();

        return $payroll;
    }
}
