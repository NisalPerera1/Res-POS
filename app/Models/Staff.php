<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{

    protected $table = 'staff';

    protected $fillable = [
        'employee_id', 'name', 'phone', 'email', 'nic', 'address',
        'joined_date', 'role', 'salary_type', 'base_salary',
        'service_charge_pct', 'is_active', 'bank_name', 'bank_account',
        'emergency_contact_name', 'emergency_contact_phone', 'notes',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'base_salary' => 'decimal:2',
        'service_charge_pct' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ── Auto-generate employee_id on creation ──────────────────
    protected static function booted(): void
    {
        static::creating(function (Staff $staff) {
            if (empty($staff->employee_id)) {
                $last = static::max('id') ?? 0;
                $staff->employee_id = 'TDZ-' . str_pad($last + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────
    public function leaves(): HasMany
    {
        return $this->hasMany(StaffLeave::class, 'staff_id');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(StaffAdvance::class, 'staff_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'staff_id');
    }

    public function serviceCharges(): HasMany
    {
        return $this->hasMany(StaffServiceCharge::class, 'staff_id');
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ───────────────────────────────────────────────
    public function getLeavesForMonth(int $month, int $year): array
    {
        $leaves = $this->leaves()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $fullDays  = $leaves->where('type', 'full_day')->count();
        $halfDays  = $leaves->where('type', 'half_day')->count();
        $unpaidFull  = $leaves->where('type', 'full_day')->where('is_paid', false)->count();
        $unpaidHalf  = $leaves->where('type', 'half_day')->where('is_paid', false)->count();

        return compact('fullDays', 'halfDays', 'unpaidFull', 'unpaidHalf');
    }

    public function getAdvancesForMonth(int $month, int $year): float
    {
        return (float) $this->advances()
            ->whereIn('status', ['approved', 'deducted'])
            ->where('deduct_month', $month)
            ->where('deduct_year', $year)
            ->sum('amount');
    }

    public function getRoleLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->role));
    }
}
