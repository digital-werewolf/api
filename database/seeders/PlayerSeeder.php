<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('players')->insert([
            [
                'username' => 'player01',
                'email' => 'letranphong2k1@gmail.com',
                'password' => Hash::make('password'),
                'email_verified_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'player02',
                'email' => 'player02@gmail.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Player::factory()
            ->count(2)
            ->create();

        Player::factory()
            ->count(2)
            ->unverified()
            ->create();
    }
}
