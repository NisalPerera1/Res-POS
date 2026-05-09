<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'addon_name',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
        'is_custom_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_custom_price' => 'boolean',
    ];

    /**
     * Get the order item that owns the addon.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get formatted display name with quantity and unit
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->addon_name} ({$this->quantity}{$this->unit})";
    }

    /**
     * Get formatted unit display
     */
    public function getFormattedUnitAttribute(): string
    {
        $unitMap = [
            'grams' => 'g',
            'kg' => 'kg',
            'pieces' => 'pcs',
            'units' => 'units',
        ];

        return $unitMap[$this->unit] ?? $this->unit;
    }

    /**
     * Scope a query to only include custom priced addons.
     */
    public function scopeCustomPrice($query)
    {
        return $query->where('is_custom_price', true);
    }

    /**
     * Scope a query to only include fixed priced addons.
     */
    public function scopeFixedPrice($query)
    {
        return $query->where('is_custom_price', false);
    }
}
