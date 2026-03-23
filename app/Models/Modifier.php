<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = [
        'modifier_group_id', 'group_name',
        'name', 'price', 'is_active'
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_modifier');
    }
}