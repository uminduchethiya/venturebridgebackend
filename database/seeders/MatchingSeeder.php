<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Matching;
use App\Models\User;


class MatchingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startups = User::where('type', 'startup')->get();
        $investors = User::where('type', 'investor')->get();

        foreach ($startups as $startup) {
            $investor = $investors->random();
            Matching::create([
                'startup_id' => $startup->id,
                'investor_id' => $investor->id,
                'status' => rand(0, 1) ? 'matched' : 'rejected',
            ]);
        }
    }
}
