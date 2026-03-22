<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'name', 'unit', 'quantity', 'min_quantity', 'cost_per_unit', 'supplier'
    ];

    protected $casts = [
        'quantity'      => 'decimal:3',
        'min_quantity'  => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
    ];

    public function recipes() { return $this->hasMany(Recipe::class); }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    /**
     * Deduct inventory when order item is prepared
     */
    public function deduct(float $qty, ?int $orderId = null, ?int $userId = null): void
    {
        $before = $this->quantity;
        $this->decrement('quantity', $qty);

        \App\Models\InventoryLog::create([
            'inventory_id'    => $this->id,
            'type'            => 'deduction',
            'quantity'        => $qty,
            'quantity_before' => $before,
            'quantity_after'  => $this->quantity,
            'order_id'        => $orderId,
            'user_id'         => $userId,
        ]);
    }
}