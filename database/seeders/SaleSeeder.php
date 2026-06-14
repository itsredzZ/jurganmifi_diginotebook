<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $modem   = Product::where('name', 'Modem WiFi Telkomsel 4G')->first();
        $battery = Product::where('name', 'Baterai Original Huawei 1500mAh')->first();
        $tutup   = Product::where('name', 'Tutup 3D Print S577 Hitam')->first();

        $seeds = [
            ['product' => $modem,   'quantity' => 2, 'platform' => 'Tokopedia', 'date' => now()->subDay()->toDateString()],
            ['product' => $battery, 'quantity' => 3, 'platform' => 'Shopee',    'date' => now()->subDay()->toDateString()],
            ['product' => $tutup,   'quantity' => 5, 'platform' => 'Tokopedia', 'date' => now()->subDays(2)->toDateString()],
        ];

        foreach ($seeds as $s) {
            if (! $s['product']) continue;
            $p = $s['product'];
            Sale::create([
                'product_id'     => $p->id,
                'product_name'   => $p->name,
                'quantity'       => $s['quantity'],
                'purchase_price' => $p->purchase_price,
                'sell_price'     => $p->sell_price,
                'profit'         => ($p->sell_price - $p->purchase_price) * $s['quantity'],
                'date'           => $s['date'],
                'platform'       => $s['platform'],
            ]);
        }
    }
}