<?php
// ══════════════════════════════════════════
// app/Models/User.php  — REPLACE EXISTING
// ══════════════════════════════════════════
namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
 
class User extends Authenticatable
{
    use HasFactory, HasApiTokens;
 
    protected $fillable = [
        'name', 'employee_id', 'pin', 'role', 'avatar', 'color', 'preferences',
        'is_active', 'is_clocked_in', 'last_login_at',
        'phone', 'email', 'address', 'date_of_birth', 'join_date',
        'salary_type', 'base_salary', 'hourly_rate', 'daily_wage', 'half_day_wage', 'service_charge_pct',
        'bank_name', 'bank_account', 'notes', 'profile_image', 'emergency_contact',
    ];
 
    protected $hidden = ['pin'];
 
    protected $casts = [
        'is_active'          => 'boolean',
        'is_clocked_in'      => 'boolean',
        'last_login_at'      => 'datetime',
        'date_of_birth'      => 'date',
        'join_date'          => 'date',
        'base_salary'        => 'decimal:2',
        'hourly_rate'        => 'decimal:2',
        'daily_wage'         => 'decimal:2',
        'half_day_wage'      => 'decimal:2',
        'service_charge_pct' => 'decimal:2',
    ];
 
    // ── Relations ──────────────────────────────────────
    public function orders()       { return $this->hasMany(Order::class); }
    public function shifts()       { return $this->hasMany(Shift::class); }
    public function payrolls()     { return $this->hasMany(Payroll::class); }
    public function leaveRequests(){ return $this->hasMany(LeaveRequest::class); }
    public function payments()     { return $this->hasMany(Payment::class); }
    public function serviceChargeDistributions() { return $this->hasMany(ServiceChargeDistribution::class); }

    // Current active shift
    public function currentShift()
    {
        return $this->hasOne(Shift::class)
            ->where('status', 'active')
            ->latest();
    }

    // Today's shifts
    public function todayShifts()
    {
        return $this->hasMany(Shift::class)
            ->where('shift_date', now()->toDateString())
            ->orderBy('clock_in');
    }

    // Upcoming leave
    public function upcomingLeave()
    {
        return $this->hasOne(LeaveRequest::class)
            ->where('status', 'approved')
            ->where('from_date', '>=', now()->toDateString())
            ->orderBy('from_date');
    }
 
    // ── Role helpers ───────────────────────────────────
    public function isAdmin():    bool { return $this->role === 'admin'; }
    public function isManager():  bool { return in_array($this->role, ['admin','manager']); }
    public function isCashier():  bool { return $this->role === 'cashier'; }
    public function isWaiter():   bool { return $this->role === 'waiter'; }
    public function isKitchen():  bool { return $this->role === 'kitchen'; }
 
    // ── Computed ───────────────────────────────────────
    public function getRoleLabelAttribute(): string
    {
        return [
            'admin'     => 'Administrator',
            'manager'   => 'Manager',
            'cashier'   => 'Cashier',
            'waiter'    => 'Waiter',
            'kitchen'   => 'Kitchen Staff',
            'bartender' => 'Bartender',
            'delivery'  => 'Delivery',
        ][$this->role] ?? $this->role;
    }
 
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->join_date) return 0;
        return round($this->join_date->diffInDays(now()) / 365, 1);
    }
 
    // Generate next employee ID
    public static function generateEmployeeId(): string
    {
        $last = static::whereNotNull('employee_id')
            ->orderByDesc('id')->first();
        $num = $last ? ((int) substr($last->employee_id, 3)) + 1 : 1;
        return 'EMP' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    // ── Wage Calculation Methods ───────────────────────────────
    
    /**
     * Get daily wage based on salary type
     */
    public function getDailyWage(): float
    {
        if ($this->daily_wage) {
            return $this->daily_wage;
        }
        
        // Calculate from other salary types
        switch ($this->salary_type) {
            case 'monthly':
                return $this->base_salary ? round($this->base_salary / 30, 2) : 0;
            case 'hourly':
                return $this->hourly_rate ? round($this->hourly_rate * 8, 2) : 0; // 8 hours = 1 day
            default:
                return 0;
        }
    }

    /**
     * Get half-day wage
     */
    public function getHalfDayWage(): float
    {
        if ($this->half_day_wage) {
            return $this->half_day_wage;
        }
        
        // Default to 50% of daily wage
        return round($this->getDailyWage() * 0.5, 2);
    }

    /**
     * Calculate salary for a month based on worked days, half days, and leaves
     */
    public function calculateMonthlySalary($workedDays, $halfDays = 0, $leaveDays = 0): array
    {
        $dailyWage = $this->getDailyWage();
        $halfDayWage = $this->getHalfDayWage();
        
        $baseSalary = ($workedDays * $dailyWage) + ($halfDays * $halfDayWage);
        
        // Calculate deductions for leaves (if applicable)
        $leaveDeductions = 0;
        if ($leaveDays > 0) {
            // Some companies deduct salary for unpaid leaves
            $leaveDeductions = $leaveDays * $dailyWage;
        }
        
        $netSalary = $baseSalary - $leaveDeductions;
        
        return [
            'daily_wage' => $dailyWage,
            'half_day_wage' => $halfDayWage,
            'base_salary' => $baseSalary,
            'leave_deductions' => $leaveDeductions,
            'net_salary' => $netSalary,
            'worked_days' => $workedDays,
            'half_days' => $halfDays,
            'leave_days' => $leaveDays,
        ];
    }

    /**
     * Get service charge received for a specific month
     */
    public function getServiceChargeForMonth($year, $month): float
    {
        return $this->serviceChargeDistributions()
            ->whereHas('serviceCharge', function ($q) use ($year, $month) {
                $q->where('year', $year)->where('month', $month);
            })
            ->sum('amount');
    }

    /**
     * Get leave statistics for a month
     */
    public function getLeaveStatsForMonth($year, $month): array
    {
        $leaves = $this->leaveRequests()
            ->approved()
            ->forMonth($year, $month)
            ->get();
        
        $fullDays = $leaves->where('duration', 1.0)->count();
        $halfDays = $leaves->where('duration', 0.5)->count();
        $totalDays = $leaves->sum('duration');
        
        return [
            'full_days' => $fullDays,
            'half_days' => $halfDays,
            'total_days' => $totalDays,
            'leaves' => $leaves,
        ];
    }
}