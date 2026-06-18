<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesVolume;

class SalesVolumeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Data Akumulatif Tahunan (2018 - 2020)
        $yearlyData = [
            ['year' => 2018, 'month' => null, 'quantity' => 400, 'is_forecast' => false],
            ['year' => 2019, 'month' => null, 'quantity' => 603, 'is_forecast' => false],
            ['year' => 2020, 'month' => null, 'quantity' => 1070, 'is_forecast' => false],
        ];

        // 2. Data Bulanan Aktual & Prediksi (2021 - 2027)
        $monthlyData = [
            // 2021
            ['year' => 2021, 'month' => 1, 'quantity' => 133, 'is_forecast' => false],
            ['year' => 2021, 'month' => 2, 'quantity' => 137, 'is_forecast' => false],
            ['year' => 2021, 'month' => 3, 'quantity' => 106, 'is_forecast' => false],
            ['year' => 2021, 'month' => 4, 'quantity' => 88, 'is_forecast' => false],
            ['year' => 2021, 'month' => 5, 'quantity' => 78, 'is_forecast' => false],
            ['year' => 2021, 'month' => 6, 'quantity' => 138, 'is_forecast' => false],
            ['year' => 2021, 'month' => 7, 'quantity' => 127, 'is_forecast' => false],
            ['year' => 2021, 'month' => 8, 'quantity' => 121, 'is_forecast' => false],
            ['year' => 2021, 'month' => 9, 'quantity' => 140, 'is_forecast' => false],
            ['year' => 2021, 'month' => 10, 'quantity' => 116, 'is_forecast' => false],
            ['year' => 2021, 'month' => 11, 'quantity' => 132, 'is_forecast' => false],
            ['year' => 2021, 'month' => 12, 'quantity' => 140, 'is_forecast' => false],

            // 2022
            ['year' => 2022, 'month' => 1, 'quantity' => 131, 'is_forecast' => false],
            ['year' => 2022, 'month' => 2, 'quantity' => 127, 'is_forecast' => false],
            ['year' => 2022, 'month' => 3, 'quantity' => 121, 'is_forecast' => false],
            ['year' => 2022, 'month' => 4, 'quantity' => 115, 'is_forecast' => false],
            ['year' => 2022, 'month' => 5, 'quantity' => 131, 'is_forecast' => false],
            ['year' => 2022, 'month' => 6, 'quantity' => 102, 'is_forecast' => false],
            ['year' => 2022, 'month' => 7, 'quantity' => 113, 'is_forecast' => false],
            ['year' => 2022, 'month' => 8, 'quantity' => 92, 'is_forecast' => false],
            ['year' => 2022, 'month' => 9, 'quantity' => 89, 'is_forecast' => false],
            ['year' => 2022, 'month' => 10, 'quantity' => 76, 'is_forecast' => false],
            ['year' => 2022, 'month' => 11, 'quantity' => 84, 'is_forecast' => false],
            ['year' => 2022, 'month' => 12, 'quantity' => 78, 'is_forecast' => false],

            // 2023
            ['year' => 2023, 'month' => 1, 'quantity' => 70, 'is_forecast' => false],
            ['year' => 2023, 'month' => 2, 'quantity' => 60, 'is_forecast' => false],
            ['year' => 2023, 'month' => 3, 'quantity' => 39, 'is_forecast' => false],
            ['year' => 2023, 'month' => 4, 'quantity' => 57, 'is_forecast' => false],
            ['year' => 2023, 'month' => 5, 'quantity' => 70, 'is_forecast' => false],
            ['year' => 2023, 'month' => 6, 'quantity' => 85, 'is_forecast' => false],
            ['year' => 2023, 'month' => 7, 'quantity' => 81, 'is_forecast' => false],
            ['year' => 2023, 'month' => 8, 'quantity' => 68, 'is_forecast' => false],
            ['year' => 2023, 'month' => 9, 'quantity' => 67, 'is_forecast' => false],
            ['year' => 2023, 'month' => 10, 'quantity' => 67, 'is_forecast' => false],
            ['year' => 2023, 'month' => 11, 'quantity' => 64, 'is_forecast' => false],
            ['year' => 2023, 'month' => 12, 'quantity' => 52, 'is_forecast' => false],

            // 2024
            ['year' => 2024, 'month' => 1, 'quantity' => 47, 'is_forecast' => false],
            ['year' => 2024, 'month' => 2, 'quantity' => 59, 'is_forecast' => false],
            ['year' => 2024, 'month' => 3, 'quantity' => 54, 'is_forecast' => false],
            ['year' => 2024, 'month' => 4, 'quantity' => 34, 'is_forecast' => false],
            ['year' => 2024, 'month' => 5, 'quantity' => 43, 'is_forecast' => false],
            ['year' => 2024, 'month' => 6, 'quantity' => 33, 'is_forecast' => false],
            ['year' => 2024, 'month' => 7, 'quantity' => 52, 'is_forecast' => false],
            ['year' => 2024, 'month' => 8, 'quantity' => 39, 'is_forecast' => false],
            ['year' => 2024, 'month' => 9, 'quantity' => 25, 'is_forecast' => false],
            ['year' => 2024, 'month' => 10, 'quantity' => 43, 'is_forecast' => false],
            ['year' => 2024, 'month' => 11, 'quantity' => 50, 'is_forecast' => false],
            ['year' => 2024, 'month' => 12, 'quantity' => 64, 'is_forecast' => false],

            // 2025
            ['year' => 2025, 'month' => 1, 'quantity' => 43, 'is_forecast' => false],
            ['year' => 2025, 'month' => 2, 'quantity' => 51, 'is_forecast' => false],
            ['year' => 2025, 'month' => 3, 'quantity' => 45, 'is_forecast' => false],
            ['year' => 2025, 'month' => 4, 'quantity' => 30, 'is_forecast' => false],
            ['year' => 2025, 'month' => 5, 'quantity' => 58, 'is_forecast' => false],
            ['year' => 2025, 'month' => 6, 'quantity' => 29, 'is_forecast' => false],
            ['year' => 2025, 'month' => 7, 'quantity' => 45, 'is_forecast' => false],
            ['year' => 2025, 'month' => 8, 'quantity' => 59, 'is_forecast' => false],
            ['year' => 2025, 'month' => 9, 'quantity' => 48, 'is_forecast' => false],
            ['year' => 2025, 'month' => 10, 'quantity' => 54, 'is_forecast' => false],
            ['year' => 2025, 'month' => 11, 'quantity' => 52, 'is_forecast' => false],
            ['year' => 2025, 'month' => 12, 'quantity' => 63, 'is_forecast' => false],

            // 2026 (Mulai Juni status is_forecast = true)
            ['year' => 2026, 'month' => 1, 'quantity' => 80, 'is_forecast' => false],
            ['year' => 2026, 'month' => 2, 'quantity' => 68, 'is_forecast' => false],
            ['year' => 2026, 'month' => 3, 'quantity' => 95, 'is_forecast' => false],
            ['year' => 2026, 'month' => 4, 'quantity' => 101, 'is_forecast' => false],
            ['year' => 2026, 'month' => 5, 'quantity' => 120, 'is_forecast' => false],
            ['year' => 2026, 'month' => 6, 'quantity' => 93, 'is_forecast' => true],
            ['year' => 2026, 'month' => 7, 'quantity' => 96, 'is_forecast' => true],
            ['year' => 2026, 'month' => 8, 'quantity' => 96, 'is_forecast' => true],
            ['year' => 2026, 'month' => 9, 'quantity' => 95, 'is_forecast' => true],
            ['year' => 2026, 'month' => 10, 'quantity' => 96, 'is_forecast' => true],
            ['year' => 2026, 'month' => 11, 'quantity' => 98, 'is_forecast' => true],
            ['year' => 2026, 'month' => 12, 'quantity' => 96, 'is_forecast' => true],

            // 2027 (Seluruhnya Prediksi)
            ['year' => 2027, 'month' => 1, 'quantity' => 86, 'is_forecast' => true],
            ['year' => 2027, 'month' => 2, 'quantity' => 88, 'is_forecast' => true],
            ['year' => 2027, 'month' => 3, 'quantity' => 87, 'is_forecast' => true],
            ['year' => 2027, 'month' => 4, 'quantity' => 92, 'is_forecast' => true],
            ['year' => 2027, 'month' => 5, 'quantity' => 98, 'is_forecast' => true],
            ['year' => 2027, 'month' => 6, 'quantity' => 88, 'is_forecast' => true],
            ['year' => 2027, 'month' => 7, 'quantity' => 89, 'is_forecast' => true],
            ['year' => 2027, 'month' => 8, 'quantity' => 89, 'is_forecast' => true],
            ['year' => 2027, 'month' => 9, 'quantity' => 91, 'is_forecast' => true],
            ['year' => 2027, 'month' => 10, 'quantity' => 90, 'is_forecast' => true],
            ['year' => 2027, 'month' => 11, 'quantity' => 90, 'is_forecast' => true],
            ['year' => 2027, 'month' => 12, 'quantity' => 88, 'is_forecast' => true],
        ];

        // Gabungkan dan Insert Data
        $allSalesData = array_merge($yearlyData, $monthlyData);

        foreach ($allSalesData as $data) {
            SalesVolume::create($data);
        }
    }
}