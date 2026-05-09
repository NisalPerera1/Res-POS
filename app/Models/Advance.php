<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'type',
        'amount',
        'remaining_balance',
        'monthly_deduction',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function processMonthlyDeduction()
    {
        if ($this->status !== 'Active' || $this->remaining_balance <= 0) {
            return 0;
        }

        $deduction = min($this->monthly_deduction, $this->remaining_balance);
        $this->remaining_balance -= $deduction;

        if ($this->remaining_balance <= 0) {
            $this->status = 'Completed';
            $this->remaining_balance = 0;
        }

        $this->save();

        return $deduction;
    }
}
