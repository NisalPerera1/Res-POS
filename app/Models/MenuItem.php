<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'image', 'price',
        'cost_price', 'sku', 'type', 'is_available', 'is_popular', 'prep_time', 'sort_order'
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'cost_price'   => 'decimal:2',
        'is_available' => 'boolean',
        'is_popular'   => 'boolean',
    ];

    public function category()  { return $this->belongsTo(Category::class); }
    public function modifiers() { return $this->belongsToMany(Modifier::class, 'menu_item_modifier'); }
}