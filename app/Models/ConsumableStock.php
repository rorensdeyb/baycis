<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumableStock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'stock_quantity',
        'reorder_level',
        'min_stock',
        'notes',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'min_stock' => 'integer',
    ];

    /**
     * Get the effective minimum stock threshold.
     */
    public function getMinStockThreshold(): int
    {
        return $this->min_stock ?? $this->reorder_level ?? 5;
    }

    /**
     * I-ISS.2: Check low stock against per-item threshold, falling back to reorder_level.
     */
    public function isLowStock(): bool
    {
        $threshold = $this->getMinStockThreshold();
        return $this->stock_quantity <= $threshold && $this->stock_quantity > 0;
    }

    /**
     * Check if the stock is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}
