<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlackPlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('black_players')->insert([
            [
                'player_id' => 3,
                'reason' => Str::random(20),
                'expired_at' => Carbon::createFromFormat('m/d/Y', '20/12/2099'),
                'created_at' => now(),
            ],
            [
                'player_id' => 5,
                'reason' => Str::random(20),
                'expired_at' => Carbon::createFromFormat('m/d/Y', '20/12/2099'),
                'created_at' => now(),
            ],
        ]);
    }
}
