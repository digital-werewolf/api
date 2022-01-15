<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LockedActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('locked_actions')->insert([
            [
                'title' => 'sign-in',
                'description' => 'Lock sign in action.',
            ],
        ]);
    }
}
