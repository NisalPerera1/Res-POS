<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = [
        'modifier_group_id',
        'group_name',
        'name',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────

    public function group()
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_modifier')
            ->withPivot('custom_price', 'pricing_type', 'increment_price');
    }

    // ──────────────────────────────────────────────────────────────
    // Pricing helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Fetch the pivot row for a given menu item (or null if not attached).
     */
    private function getPivotForMenuItem(int $menuItemId): ?object
    {
        return $this->menuItems()
            ->where('menu_item_id', $menuItemId)
            ->first()
            ?->pivot;
    }

    /**
     * Return the stored price for this modifier on a specific menu item.
     *
     * For absolute pricing  → returns custom_price (or fallback default price).
     * For increment pricing → returns the increment_price delta.
     * If no pivot exists    → returns the modifier's own default price.
     */
    public function getPriceForMenuItem(int $menuItemId): float
    {
        $pivot = $this->getPivotForMenuItem($menuItemId);

        if (! $pivot) {
            return (float) $this->price;
        }

        if ($pivot->pricing_type === 'increment') {
            return (float) ($pivot->increment_price ?? 0);
        }

        // absolute
        return (float) ($pivot->custom_price ?? $this->price);
    }

    /**
     * Return the increment delta for a specific menu item.
     *
     * Always returns the amount to ADD to the base price.
     *   - increment pricing → increment_price column
     *   - absolute pricing  → treats custom_price (or default price) as the add-on
     */
    public function getIncrementForMenuItem(int $menuItemId): float
    {
        $pivot = $this->getPivotForMenuItem($menuItemId);

        if (! $pivot) {
            return (float) $this->price;
        }

        if ($pivot->pricing_type === 'increment' && $pivot->increment_price !== null) {
            return (float) $pivot->increment_price;
        }

        return (float) ($pivot->custom_price ?? $this->price);
    }

    /**
     * Compute the FINAL item price when this modifier is applied.
     *
     * Pricing types:
     *   'increment' → $baseItemPrice + increment_price
     *                 e.g. Rice (Rs.1000) + Large (+200) = Rs.1200
     *   'absolute'  → base + custom_price (add-on, not a replacement)
     *                 e.g. Extra Cheese = Rs.150 added on top of item price
     *
     * @param  int   $menuItemId    The menu item this modifier belongs to.
     * @param  float $baseItemPrice The item's base price.
     * @return float                Final price to charge.
     */
    public function getFinalPriceForMenuItem(int $menuItemId, float $baseItemPrice = 0): float
    {
        $pivot = $this->getPivotForMenuItem($menuItemId);

        if (! $pivot) {
            // No pivot → treat default modifier price as an increment
            return $baseItemPrice + (float) $this->price;
        }

        if ($pivot->pricing_type === 'increment') {
            return $baseItemPrice + (float) ($pivot->increment_price ?? 0);
        }

        // absolute pricing — add-on on top of base
        return $baseItemPrice + (float) ($pivot->custom_price ?? $this->price);
    }
}