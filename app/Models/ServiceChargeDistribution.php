<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceChargeDistribution extends Model
{
    protected $fillable = [
        'staff_id',
        'amount',
        'percentage',
        'distribution_date',
        'month',
        'year',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'distribution_date' => 'date',
    ];

    // Relationships
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    // Scopes
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }
}
