<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'method',
        'amount',
        'tendered',
        'change_amount',
        'reference',
        'receipt_number',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'tendered'      => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Human readable method label
    public function getMethodLabelAttribute(): string
    {
        return [
            'cash'          => 'Cash',
            'card'          => 'Card / Tap',
            'mobile'        => 'Mobile Pay',
            'voucher'       => 'Voucher',
            'complimentary' => 'Complimentary',
        ][$this->method] ?? $this->method;
    }
}