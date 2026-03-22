<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'section', 'capacity', 'status', 'current_order_id', 
        'sort_order', 'customer_name', 'notes'
    ];

    protected $casts = [
        'capacity'   => 'integer',
        'sort_order' => 'integer',
    ];

    public function currentOrder()
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isFree(): bool     { return $this->status === 'free'; }
    public function isOccupied(): bool { return $this->status === 'occupied'; }
}