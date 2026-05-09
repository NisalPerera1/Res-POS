<?php

// ─────────────────────────────────────────────────────
// app/Models/MonthlyServiceCharge.php
// ─────────────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyServiceCharge extends Model
{
    protected $fillable = ['month', 'year', 'total_amount'];

    protected $casts = ['total_amount' => 'decimal:2'];

    public function distributions()
    {
        return $this->hasMany(StaffServiceCharge::class, 'monthly_service_charge_id');
    }
}
