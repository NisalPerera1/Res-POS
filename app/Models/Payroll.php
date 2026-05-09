<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'staff_id', 'month', 'year',
        'base_salary', 'service_charge', 'bonus', 'overtime_pay', 'gross_pay',
        'leave_deductions', 'advance_deductions', 'other_deductions', 'total_deductions',
        'net_pay', 'status', 'paid_date', 'payment_method', 'notes',
        'working_days', 'days_worked', 'leaves_taken', 'half_leaves_taken',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'bonus' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'leave_deductions' => 'decimal:2',
        'advance_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function recompute(): void
    {
        $this->gross_pay      = $this->base_salary + $this->service_charge + $this->bonus + $this->overtime_pay;
        $this->total_deductions = $this->leave_deductions + $this->advance_deductions + $this->other_deductions;
        $this->net_pay        = max(0, $this->gross_pay - $this->total_deductions);
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
