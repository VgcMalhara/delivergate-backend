<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'price',
        'quantity'
    ];

    // Best practice: cast attributes to strict types
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];
}
