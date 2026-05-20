<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sku',
        'name',
        'current_stock',
        'min_stock_threshold',
        'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'integer',
            'min_stock_threshold' => 'integer',
            'cost_price' => 'decimal:2',
        ];
    }

    /**
     * Check if product is low in stock.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock < $this->min_stock_threshold;
    }

    protected $appends = ['is_low_stock'];

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class)->orderBy('created_at', 'desc');
    }
}
