<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['menu_item_id', 'inventory_id', 'quantity_used'];
    protected $casts = ['quantity_used' => 'decimal:3'];

    public function menuItem()  { return $this->belongsTo(MenuItem::class); }
    public function inventory() { return $this->belongsTo(Inventory::class); }
}