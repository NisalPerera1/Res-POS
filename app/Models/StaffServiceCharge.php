<?php

// ─────────────────────────────────────────────────────
// app/Models/StaffServiceCharge.php
// ─────────────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffServiceCharge extends Model
{
    protected $fillable = [
        'staff_id', 'monthly_service_charge_id',
        'month', 'year', 'service_charge_pct', 'share_amount',
    ];

    protected $casts = [
        'service_charge_pct' => 'decimal:2',
        'share_amount' => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function monthlyServiceCharge(): BelongsTo
    {
        return $this->belongsTo(MonthlyServiceCharge::class, 'monthly_service_charge_id');
    }
}
