<?php

namespace Database\Seeders;

use App\Models\PromotionCycle;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionCycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = Carbon::now()->year;

        // Determine the name of the cycle that should be active by default.
        $currentCycleName = $currentYear . '-' . ($currentYear + 1);

        $cyclesToCreate = [];

        // Generate a range of cycles, from 2 years ago to 3 years in the future
        for ($i = -2; $i <= 3; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            $cycleName = "{$startYear}-{$endYear}";

            $cyclesToCreate[] = [
                'name' => $cycleName,
                'is_active' => ($cycleName === $currentCycleName),
            ];
        }

        foreach ($cyclesToCreate as $cycle) {
            PromotionCycle::firstOrCreate(
                ['name' => $cycle['name']],
                ['is_active' => $cycle['is_active']]
            );
        }
    }
}
