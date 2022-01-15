<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('locks')->insert([
            [
                'player_id' => 3,
                'action_id' => 1,
                'reason' => Str::random(20),
                'expired_at' => Carbon::createFromFormat('m/d/Y', '20/12/3000'),
                'created_at' => now(),
            ],
            [
                'player_id' => 5,
                'action_id' => 1,
                'reason' => Str::random(20),
                'expired_at' => Carbon::createFromFormat('m/d/Y', '20/12/3000'),
                'created_at' => now(),
            ],
        ]);
    }
}
