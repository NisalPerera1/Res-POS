<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id', 'approved_by', 'type',
        'from_date', 'to_date', 'days', 'duration', 'reason', 'status',
        'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'from_date'    => 'date',
        'to_date'      => 'date',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'duration'     => 'decimal:2', // 1.0 for full day, 0.5 for half day
    ];

    // Leave types
    const TYPE_FULL_DAY = 'full_day';
    const TYPE_HALF_DAY = 'half_day';
    const TYPE_MULTIPLE_DAYS = 'multiple_days';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes for easier querying
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('from_date', $year)
                    ->whereMonth('from_date', $month);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isHalfDay(): bool
    {
        return $this->duration == 0.5;
    }

    public function isFullDay(): bool
    {
        return $this->duration == 1.0;
    }

    public function isMultipleDays(): bool
    {
        return $this->days > 1;
    }

    public function getLeaveTypeLabel(): string
    {
        if ($this->isHalfDay()) {
            return 'Half Day';
        } elseif ($this->isMultipleDays()) {
            return "Multiple Days ({$this->days} days)";
        } else {
            return 'Full Day';
        }
    }

    public function getFormattedDuration(): string
    {
        if ($this->isHalfDay()) {
            return '0.5 day';
        } elseif ($this->isMultipleDays()) {
            return "{$this->days} days";
        } else {
            return '1 day';
        }
    }

    // Calculate leave days based on duration
    public static function calculateLeaveDays($fromDate, $toDate, $duration = null)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);
        
        if ($from->eq($to)) {
            // Same day leave
            return $duration ?? 1.0;
        } else {
            // Multiple days leave
            return $from->diffInDays($to) + 1;
        }
    }
}