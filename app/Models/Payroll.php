<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'year', 'month',
        'days_worked', 'hours_worked',
        'base_salary', 'service_charge_share', 'tips',
        'bonus', 'deductions', 'gross_pay', 'net_pay',
        'status', 'notes', 'paid_at',
    ];

    protected $casts = [
        'base_salary'          => 'decimal:2',
        'service_charge_share' => 'decimal:2',
        'tips'                 => 'decimal:2',
        'bonus'                => 'decimal:2',
        'deductions'           => 'decimal:2',
        'gross_pay'            => 'decimal:2',
        'net_pay'              => 'decimal:2',
        'hours_worked'         => 'decimal:2',
        'paid_at'              => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMonthLabelAttribute(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}