<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'menu_item_id', 'item_name', 'unit_price',
        'quantity', 'total_price', 'modifiers', 'notes', 'status', 
         'kot_round',      // ← make sure this is here
        'kot_sent_at',    // ← and this
        'is_void'
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'modifiers'   => 'array',
        'is_void'     => 'boolean',   // ← cast to bool so 0/1/"0"/"1" all work
        'kot_sent_at' => 'datetime',
        'kot_round'   => 'integer',
    ];

    public function order()    { return $this->belongsTo(Order::class); }
    public function menuItem() { return $this->belongsTo(MenuItem::class); }
    public function addons()   { return $this->hasMany(OrderItemAddon::class); }

    /**
     * Get the total price including addons
     */
    public function getTotalWithAddonsAttribute(): float
    {
        $addonsTotal = $this->addons->sum('total_price');
        return (float) $this->total_price + $addonsTotal;
    }

    /**
     * Get formatted addons display for cart
     */
    public function getAddonsDisplayAttribute(): string
    {
        if ($this->addons->isEmpty()) {
            return '';
        }

        return $this->addons->map(function ($addon) {
            return "+{$addon->addon_name} ({$addon->quantity}{$addon->formatted_unit})";
        })->implode(', ');
    }
}
