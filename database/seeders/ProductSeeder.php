<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Baterai Original Huawei 1500mAh',  'category' => 'battery',   'purchase_price' => 75000,  'sell_price' => 120000, 'stock_rumah' => 5,  'stock_toko' => 2],
            ['name' => 'Baterai Original Huawei 3000mAh',  'category' => 'battery',   'purchase_price' => 85000,  'sell_price' => 135000, 'stock_rumah' => 5,  'stock_toko' => 2],
            ['name' => 'Baterai Original HKM 3000mAh',     'category' => 'battery',   'purchase_price' => 70000,  'sell_price' => 110000, 'stock_rumah' => 4,  'stock_toko' => 0],
            ['name' => 'Baterai Dummy Huawei',              'category' => 'battery',   'purchase_price' => 18000,  'sell_price' => 32000,  'stock_rumah' => 5,  'stock_toko' => 0],
            ['name' => 'Baterai Dummy Advan MicroUSB',      'category' => 'battery',   'purchase_price' => 12000,  'sell_price' => 22000,  'stock_rumah' => 10, 'stock_toko' => 10],
            ['name' => 'Baterai Dummy Advan Type-C',        'category' => 'battery',   'purchase_price' => 12000,  'sell_price' => 22000,  'stock_rumah' => 10, 'stock_toko' => 10],
            ['name' => 'Modem WiFi Telkomsel 4G',           'category' => 'mifi',      'purchase_price' => 200000, 'sell_price' => 285000, 'stock_rumah' => 5,  'stock_toko' => 5],
            ['name' => 'Modem WiFi E5586 HiFi Air',         'category' => 'mifi',      'purchase_price' => 340000, 'sell_price' => 480000, 'stock_rumah' => 1,  'stock_toko' => 0],
            ['name' => 'Modem WiFi E5586 Orbit',            'category' => 'mifi',      'purchase_price' => 310000, 'sell_price' => 450000, 'stock_rumah' => 1,  'stock_toko' => 0],
            ['name' => 'SIMcard HiFi 215GB',                'category' => 'simcard',   'purchase_price' => 50000,  'sell_price' => 75000,  'stock_rumah' => 1,  'stock_toko' => 1],
            ['name' => 'Modem 5G CPE',                      'category' => 'mifi',      'purchase_price' => 520000, 'sell_price' => 750000, 'stock_rumah' => 2,  'stock_toko' => 0],
            ['name' => 'Tutup 3D Print S577 Hitam',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 5,  'stock_toko' => 5],
            ['name' => 'Tutup 3D Print S577 Putih',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 5,  'stock_toko' => 5],
            ['name' => 'Tutup 3D Print S573 Hitam',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 5,  'stock_toko' => 0],
            ['name' => 'Tutup 3D Print S573 Putih',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 5,  'stock_toko' => 0],
            ['name' => 'Tutup 3D Print S576 Hitam',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 2,  'stock_toko' => 0],
            ['name' => 'Tutup 3D Print S576 Putih',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 2,  'stock_toko' => 0],
            ['name' => 'Tutup 3D Print S783 Hitam',         'category' => 'accessory', 'purchase_price' => 12000,  'sell_price' => 25000,  'stock_rumah' => 2,  'stock_toko' => 2],
            ['name' => 'Layar LCD Modem Huawei',            'category' => 'accessory', 'purchase_price' => 110000, 'sell_price' => 175000, 'stock_rumah' => 5,  'stock_toko' => 0],
            ['name' => 'Layar LCD Modem HKM',               'category' => 'accessory', 'purchase_price' => 90000,  'sell_price' => 145000, 'stock_rumah' => 2,  'stock_toko' => 0],
        ];

        foreach ($products as $data) {
            Product::create(array_merge($data, ['date_added' => now()->toDateString()]));
        }
    }
}