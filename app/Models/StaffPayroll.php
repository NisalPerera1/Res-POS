<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'year',
        'month',
        'base_salary',
        'service_charge',
        'leave_deductions',
        'bonus',
        'other_deductions',
        'gross_pay',
        'total_deductions',
        'net_pay',
        'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'leave_deductions' => 'decimal:2',
        'bonus' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    // Relationships
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return [
            'draft' => 'Draft',
            'approved' => 'Approved',
            'paid' => 'Paid',
        ][$this->status] ?? $this->status;
    }

    public function getFormattedBaseSalaryAttribute()
    {
        return 'Rs. ' . number_format($this->base_salary, 2);
    }

    public function getFormattedNetPayAttribute()
    {
        return 'Rs. ' . number_format($this->net_pay, 2);
    }

    public function getMonthYearAttribute()
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}
