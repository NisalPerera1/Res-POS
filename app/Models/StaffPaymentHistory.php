<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StaffPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'payroll_id',
        'year',
        'month',
        'gross_pay',
        'total_deductions',
        'net_pay',
        'base_salary',
        'overtime_pay',
        'service_charge_share',
        'tips_collected',
        'bonus',
        'allowances',
        'advance_deductions',
        'leave_deductions',
        'other_deductions',
        'tax_deductions',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'gross_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'service_charge_share' => 'decimal:2',
        'tips_collected' => 'decimal:2',
        'bonus' => 'decimal:2',
        'allowances' => 'decimal:2',
        'advance_deductions' => 'decimal:2',
        'leave_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'tax_deductions' => 'decimal:2',
        'payment_date' => 'date',
        'payment_date' => 'date',
    ];

    // Relationships
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(StaffPayroll::class, 'payroll_id');
    }

    // Scopes
    public function scopeForMonth($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Accessors
    public function getFormattedGrossPayAttribute()
    {
        return 'Rs. ' . number_format($this->gross_pay, 2);
    }

    public function getFormattedNetPayAttribute()
    {
        return 'Rs. ' . number_format($this->net_pay, 2);
    }

    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('M d, Y') : 'N/A';
    }

    public function getMonthYearAttribute()
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    public function getPaymentMethodLabelAttribute()
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'other' => 'Other'
        ][$this->payment_method] ?? $this->payment_method;
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled'
        ][$this->status] ?? $this->status;
    }

    // Helper Methods
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'payment_date' => now(),
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" . $reason : $reason) : $this->notes,
        ]);
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" . $reason : $reason) : $this->notes,
        ]);
    }

    public static function getTotalPaidByMonth($year, $month)
    {
        return self::forMonth($year, $month)
            ->completed()
            ->sum('net_pay');
    }

    public static function getTotalPaidByStaff($staffId, $year = null, $month = null)
    {
        $query = self::forStaff($staffId)->completed();
        
        if ($year) {
            $query->where('year', $year);
        }
        
        if ($month) {
            $query->where('month', $month);
        }
        
        return $query->sum('net_pay');
    }

    public static function getPaymentSummaryByMonth($year, $month)
    {
        return self::forMonth($year, $month)
            ->completed()
            ->with('staff')
            ->get()
            ->map(function ($payment) {
                return [
                    'staff_name' => $payment->staff->name,
                    'employee_id' => $payment->staff->employee_id,
                    'net_pay' => $payment->net_pay,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method_label,
                ];
            });
    }
}
