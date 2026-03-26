<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'image', 'price',
        'cost_price', 'sku', 'type', 'is_available', 'is_popular',
        'is_instant', 'prep_time', 'sort_order', 'icon',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'cost_price'   => 'decimal:2',
        'is_available' => 'boolean',
        'is_popular'   => 'boolean',
        'is_instant'   => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Direct modifier pivot — includes per-item pricing columns.
     */
    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'menu_item_modifier')
            ->withPivot(['custom_price', 'pricing_type', 'increment_price']);
    }

    /**
     * Modifier groups attached to this item.
     * Eager-loads only active modifiers within each group.
     */
    public function modifierGroups()
    {
        return $this->belongsToMany(
            ModifierGroup::class,
            'menu_item_modifier_group'
        )->with(['modifiers' => fn ($q) => $q->where('is_active', true)]);
    }

    // ──────────────────────────────────────────────────────────────
    // Pricing helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Does this item have any modifier groups attached?
     */
    public function hasModifiers(): bool
    {
        return $this->modifierGroups()->exists();
    }

    /**
     * Compute the final price for this item given a set of selected modifier IDs.
     *
     * Rules:
     *  - Start from the item's base price.
     *  - For each selected modifier:
     *      • 'increment' pricing → add increment_price to the running total.
     *      • 'absolute'  pricing → add custom_price as a flat add-on.
     *
     * Both types now ADD to base price for consistent behaviour.
     * Absolute was previously documented as "replacing" base price — this was
     * incorrect for add-ons like Extra Cheese. It is now always additive.
     * If you need a "replacement" modifier (e.g. swap base item), handle that
     * in the order layer, not here.
     *
     * @param  int[]  $selectedModifierIds
     * @return float  Total price to charge for this item.
     */
    public function computeFinalPrice(array $selectedModifierIds): float
    {
        if (empty($selectedModifierIds)) {
            return (float) $this->price;
        }

        // Load modifiers with pivot data in one query
        $modifiers = $this->modifiers()
            ->whereIn('modifiers.id', $selectedModifierIds)
            ->get();

        $total = (float) $this->price;

        foreach ($modifiers as $modifier) {
            $pricingType    = $modifier->pivot->pricing_type    ?? 'absolute';
            $incrementPrice = (float) ($modifier->pivot->increment_price ?? 0);
            $customPrice    = (float) ($modifier->pivot->custom_price    ?? $modifier->price);

            if ($pricingType === 'increment') {
                $total += $incrementPrice;
            } else {
                // absolute — flat add-on on top of running total
                $total += $customPrice;
            }
        }

        return $total;
    }
}