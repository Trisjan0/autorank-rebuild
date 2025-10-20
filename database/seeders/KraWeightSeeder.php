<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\KraWeight;

class KraWeightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the KRA weights for the four faculty rank categories
        $weights = [
            [
                'rank_category' => 'Category 1',
                'kra1_weight' => 60,
                'kra2_weight' => 10,
                'kra3_weight' => 20,
                'kra4_weight' => 10,
            ],
            [
                'rank_category' => 'Category 2',
                'kra1_weight' => 50,
                'kra2_weight' => 15,
                'kra3_weight' => 20,
                'kra4_weight' => 15,
            ],
            [
                'rank_category' => 'Category 3',
                'kra1_weight' => 40,
                'kra2_weight' => 20,
                'kra3_weight' => 20,
                'kra4_weight' => 20,
            ],
            [
                'rank_category' => 'Category 4',
                'kra1_weight' => 30,
                'kra2_weight' => 30,
                'kra3_weight' => 20,
                'kra4_weight' => 20,
            ],
        ];

        foreach ($weights as $weight) {
            KraWeight::updateOrCreate(
                ['rank_category' => $weight['rank_category']],
                $weight
            );
        }
    }
}
