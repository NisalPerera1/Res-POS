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
        'name', 'employee_id', 'pin', 'role', 'avatar', 'color',
        'is_active', 'is_clocked_in', 'last_login_at',
        'phone', 'email', 'address', 'date_of_birth', 'join_date',
        'salary_type', 'base_salary', 'hourly_rate', 'service_charge_pct',
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
        'service_charge_pct' => 'decimal:2',
    ];
 
    // ── Relations ──────────────────────────────────────
    public function orders()       { return $this->hasMany(Order::class); }
    public function shifts()       { return $this->hasMany(Shift::class); }
    public function payrolls()     { return $this->hasMany(Payroll::class); }
    public function leaveRequests(){ return $this->hasMany(LeaveRequest::class); }
    public function payments()     { return $this->hasMany(Payment::class); }

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
}