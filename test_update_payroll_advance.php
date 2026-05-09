<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing payroll update with new advance...\n";
    
    $staff = \App\Models\Staff::first();
    $month = 4;
    $year = 2026;
    
    // Get current advance deduction
    $advanceDeduction = $staff->getAdvancesForMonth($month, $year);
    echo "Current advance deduction: Rs. {$advanceDeduction}\n";
    
    // Get existing payroll
    $payroll = \App\Models\Payroll::where('staff_id', $staff->id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();
    
    if ($payroll) {
        echo "Found existing payroll ID: {$payroll->id}\n";
        echo "Current advance deductions: Rs. {$payroll->advance_deductions}\n";
        
        // Update payroll with new advance deduction
        $payroll->advance_deductions = $advanceDeduction;
        $payroll->total_deductions = $payroll->leave_deductions + $advanceDeduction + $payroll->other_deductions;
        $payroll->net_pay = $payroll->gross_pay - $payroll->total_deductions;
        $payroll->save();
        
        echo "Updated payroll:\n";
        echo "- Advance deductions: Rs. {$payroll->advance_deductions}\n";
        echo "- Total deductions: Rs. {$payroll->total_deductions}\n";
        echo "- Net pay: Rs. {$payroll->net_pay}\n";
        
        // Mark advances as deducted
        \App\Models\StaffAdvance::where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->where('deduct_month', $month)
            ->where('deduct_year', $year)
            ->update(['status' => 'deducted']);
        
        echo "Advances marked as deducted\n";
    } else {
        echo "No existing payroll found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
