<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'product_id', 'product_name', 'quantity',
        'purchase_price', 'sell_price', 'profit',
        'date', 'platform',
    ];

    protected $casts = [
        'date'           => 'date',
        'purchase_price' => 'integer',
        'sell_price'     => 'integer',
        'profit'         => 'integer',
        'quantity'       => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalRevenueAttribute(): int
    {
        return $this->sell_price * $this->quantity;
    }

    public static function platformColor(string $platform): string
    {
        return match ($platform) {
            'Tokopedia' => 'bg-green-50 text-green-700',
            'Shopee'    => 'bg-orange-50 text-orange-700',
            'Offline'   => 'bg-gray-50 text-gray-700',
            default     => 'bg-gray-50 text-gray-700',
        };
    }
}