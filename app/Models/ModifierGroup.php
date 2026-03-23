<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = [
        'name', 'is_required', 'min_select',
        'max_select', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
        'min_select'  => 'integer',
        'max_select'  => 'integer',
        'sort_order'  => 'integer',
    ];

    public function modifiers()
    {
        return $this->hasMany(Modifier::class);
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_modifier_group');
    }
}