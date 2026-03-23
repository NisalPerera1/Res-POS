<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'image', 'price',
        'cost_price', 'sku', 'type', 'is_available', 'is_popular',
        'is_instant', 'prep_time', 'sort_order'
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'cost_price'   => 'decimal:2',
        'is_available' => 'boolean',
        'is_popular'   => 'boolean',
        'is_instant'   => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Old pivot (modifier_id direct)
    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'menu_item_modifier');
    }

    // New: modifier groups attached to this item
    public function modifierGroups()
    {
        return $this->belongsToMany(
            ModifierGroup::class,
            'menu_item_modifier_group'
        )->with(['modifiers' => fn($q) => $q->where('is_active', true)]);
    }

    public function hasModifiers(): bool
    {
        return $this->modifierGroups()->exists();
    }
}