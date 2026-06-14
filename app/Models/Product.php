<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'category', 'purchase_price', 'sell_price',
        'stock_rumah', 'stock_toko', 'date_added',
    ];

    protected $casts = [
        'date_added'     => 'date',
        'purchase_price' => 'integer',
        'sell_price'     => 'integer',
        'stock_rumah'    => 'integer',
        'stock_toko'     => 'integer',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stock_rumah + $this->stock_toko;
    }

    public function getProfitPerUnitAttribute(): int
    {
        return $this->sell_price - $this->purchase_price;
    }

    public function getMarginPercentAttribute(): float
    {
        if ($this->sell_price === 0) return 0;
        return round(($this->profit_per_unit / $this->sell_price) * 100, 1);
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'mifi'      => 'Modem/MiFi',
            'router'    => 'Router',
            'battery'   => 'Baterai',
            'simcard'   => 'SIMcard',
            'accessory' => 'Aksesori',
            default     => ucfirst($category),
        };
    }

    public static function categoryColor(string $category): string
    {
        return match ($category) {
            'mifi'      => 'bg-blue-50 text-blue-700',
            'router'    => 'bg-purple-50 text-purple-700',
            'battery'   => 'bg-green-50 text-green-700',
            'simcard'   => 'bg-orange-50 text-orange-700',
            'accessory' => 'bg-gray-50 text-gray-700',
            default     => 'bg-gray-50 text-gray-700',
        };
    }
}