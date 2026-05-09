<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLeave extends Model
{
    protected $fillable = [
        'staff_id', 'date', 'type', 'half_day_period', 'reason', 'is_paid',
    ];

    protected $casts = [
        'date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    // Returns a deduction multiplier: full_day=1.0, half_day=0.5
    public function getDeductionMultiplier(): float
    {
        return $this->type === 'full_day' ? 1.0 : 0.5;
    }

    // Scopes
    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    public function scopeFullDay($query)
    {
        return $query->where('type', 'full_day');
    }

    public function scopeHalfDay($query)
    {
        return $query->where('type', 'half_day');
    }

    // Accessors
    public function getDeductionAmountAttribute()
    {
        return $this->type === 'full_day' ? $this->staff->daily_rate : $this->staff->daily_rate / 2;
    }
}
