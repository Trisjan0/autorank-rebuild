<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ScoreCapsSeeder extends Seeder
{
    public function run(): void
    {
        $caps = [
            // KRA I
            ['key' => 'kra_1_total_cap', 'value' => '100'],
            ['key' => 'kra_1_a_cap', 'value' => '60'],
            ['key' => 'kra_1_b_cap', 'value' => '30'],
            ['key' => 'kra_1_c_cap', 'value' => '10'],

            // KRA II
            ['key' => 'kra_2_total_cap', 'value' => '100'],
            ['key' => 'kra_2_a_cap', 'value' => '100'],
            ['key' => 'kra_2_a_3_1_cap', 'value' => '40'], // Sub: Local Citations
            ['key' => 'kra_2_a_3_2_cap', 'value' => '60'], // Sub: International Citations
            ['key' => 'kra_2_b_cap', 'value' => '100'],
            ['key' => 'kra_2_b_1_2_1_cap', 'value' => '20'], // Sub: Local Patented
            ['key' => 'kra_2_b_1_2_2_cap', 'value' => '30'], // Sub: International Patented
            ['key' => 'kra_2_c_cap', 'value' => '100'],

            // KRA III
            ['key' => 'kra_3_total_cap', 'value' => '100'],
            ['key' => 'kra_3_a_cap', 'value' => '30'],
            ['key' => 'kra_3_b_cap', 'value' => '50'],
            ['key' => 'kra_3_c_cap', 'value' => '20'],
            ['key' => 'kra_3_d_cap', 'value' => '20'],

            // KRA IV
            ['key' => 'kra_4_total_cap', 'value' => '100'],
            ['key' => 'kra_4_a_cap', 'value' => '20'],
            ['key' => 'kra_4_b_cap', 'value' => '60'],
            ['key' => 'kra_4_b_2_cap', 'value' => '10'], // Sub: Participation
            ['key' => 'kra_4_b_3_cap', 'value' => '10'], // Sub: Paper Presentation
            ['key' => 'kra_4_c_cap', 'value' => '20'],
            ['key' => 'kra_4_d_cap', 'value' => '20'],
        ];

        foreach ($caps as $cap) {
            Setting::updateOrCreate(
                ['key' => $cap['key']],
                ['value' => $cap['value']]
            );
        }
    }
}
