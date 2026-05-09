<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'active_from',
        'active_to',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    protected $casts = [
        'active_from' => 'datetime',
        'active_to' => 'datetime',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
