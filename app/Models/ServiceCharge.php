<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCharge extends Model
{
    protected $fillable = [
        'total_amount',
        'source',
        'month',
        'year',
        'distributed_amount',
        'remaining_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'distributed_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function distributions(): HasMany
    {
        return $this->hasMany(ServiceChargeDistribution::class);
    }

    // Scopes
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    // Accessors
    public function getAvailableAmountAttribute()
    {
        return $this->remaining_amount;
    }

    public function getDistributionPercentageAttribute()
    {
        if ($this->total_amount == 0) return 0;
        return ($this->distributed_amount / $this->total_amount) * 100;
    }
}
