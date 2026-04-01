<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'table_id', 'user_id', 'type', 'status',
        'guests', 'notes', 'subtotal', 'tax_rate', 'tax_amount',
        'discount_amount', 'total', 'payment_status', 'kot_sent_at', 'completed_at',
        'customer_name', 'order_type', 'customer_notes'
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'kot_sent_at'     => 'datetime',
        'completed_at'    => 'datetime',
    ];

    public function table()      { return $this->belongsTo(Table::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }
    public function payments()   { return $this->hasMany(Payment::class); }

    public function activeItems()
    {
        return $this->hasMany(OrderItem::class)->where('is_void', false);
    }
    
    public function pendingItems()
    {
        return $this->hasMany(OrderItem::class)->where('is_void', false)->where('status', 'pending');
    }

    public function recalculate(float $taxRate = 10.0): void
    {
        // Use 0% tax for direct orders (no table_id)
        if ($this->table_id === null) {
            $taxRate = 0.0;
        }
        
        $subtotal = $this->activeItems->sum('total_price');
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        $this->update([
            'subtotal'   => $subtotal,
            'tax_rate'   => $taxRate,
            'tax_amount' => $taxAmount,
            'total'      => $subtotal + $taxAmount - $this->discount_amount,
        ]);
    }

    public static function generateOrderNumber(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $num  = $last ? ((int) substr($last->order_number, 4)) + 1 : 1;
        return 'ORD-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}