<?php

namespace Database\Seeders;

use App\Models\FacultyRank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultyRankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            ['name' => 'Instructor I', 'level' => 1],
            ['name' => 'Instructor II', 'level' => 2],
            ['name' => 'Instructor III', 'level' => 3],
            ['name' => 'Assistant Professor I', 'level' => 4],
            ['name' => 'Assistant Professor II', 'level' => 5],
            ['name' => 'Assistant Professor III', 'level' => 6],
            ['name' => 'Assistant Professor IV', 'level' => 7],
            ['name' => 'Associate Professor I', 'level' => 8],
            ['name' => 'Associate Professor II', 'level' => 9],
            ['name' => 'Associate Professor III', 'level' => 10],
            ['name' => 'Associate Professor IV', 'level' => 11],
            ['name' => 'Associate Professor V', 'level' => 12],
            ['name' => 'Professor I', 'level' => 13],
            ['name' => 'Professor II', 'level' => 14],
            ['name' => 'Professor III', 'level' => 15],
            ['name' => 'Professor IV', 'level' => 16],
            ['name' => 'Professor V', 'level' => 17],
            ['name' => 'Professor VI', 'level' => 18],
        ];

        foreach ($ranks as $rank) {
            FacultyRank::firstOrCreate($rank);
        }
    }
}
