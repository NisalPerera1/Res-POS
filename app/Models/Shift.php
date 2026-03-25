<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id', 'shift_date', 'clock_in', 'clock_out',
        'hours_worked', 'tips_collected', 'service_charge_earned',
        'notes', 'status',
    ];

    protected $casts = [
        'clock_in'              => 'datetime',
        'clock_out'             => 'datetime',
        'shift_date'            => 'date',
        'hours_worked'          => 'decimal:2',
        'tips_collected'        => 'decimal:2',
        'service_charge_earned' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedHoursAttribute(): string
    {
        if (!$this->hours_worked) return '—';
        $h = floor($this->hours_worked);
        $m = round(($this->hours_worked - $h) * 60);
        return "{$h}h {$m}m";
    }
}