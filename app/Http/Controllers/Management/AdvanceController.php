<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index()
    {
        return Advance::with('staff')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'type' => 'required|in:Salary Advance,Emergency Advance,Personal Loan',
            'amount' => 'required|numeric|min:0',
            'monthly_deduction' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $advance = Advance::create([
            ...$validated,
            'remaining_balance' => $validated['amount'],
            'status' => 'Active'
        ]);

        // Add to payment history
        PaymentHistory::create([
            'staff_id' => $advance->staff_id,
            'employee_name' => $advance->staff->name,
            'type' => 'Advance',
            'amount' => $advance->amount,
            'method' => 'Cash',
            'reference' => 'ADV-' . date('Y-m') . '-' . str_pad($advance->id, 3, '0', STR_PAD_LEFT),
            'payment_date' => $advance->date,
        ]);

        return response()->json($advance, 201);
    }

    public function getAdvancesByMonth($monthYear)
    {
        $advances = Advance::with('staff')
            ->where('status', 'Active')
            ->where('remaining_balance', '>', 0)
            ->whereDate('date', '<=', $monthYear . '-31')
            ->get();

        return response()->json($advances);
    }

    public function processDeductions(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'month_year' => 'required|string',
        ]);

        $advances = Advance::where('staff_id', $validated['staff_id'])
            ->where('status', 'Active')
            ->where('remaining_balance', '>', 0)
            ->get();

        $totalDeduction = 0;
        foreach ($advances as $advance) {
            $deduction = $advance->processMonthlyDeduction();
            $totalDeduction += $deduction;
        }

        return response()->json([
            'total_deduction' => $totalDeduction,
            'advances_processed' => $advances->count()
        ]);
    }
}
