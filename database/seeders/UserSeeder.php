<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'ابونا مينا',
            'email' => 'abouna.mina@gmail.com',
            'password' => bcrypt('123456'),
            'membership_code' => 'E1C1F1NR1',
            'phone' => '01012345678',
            'type' => 1,
        ]);

        User::create([
            'name' => 'ميشيل',
            'email' => 'misho@gmail.com',
            'password' => bcrypt('123456'),
            'membership_code' => 'E1C1F2NR1',
            'phone' => '01278783887',
            'type' => 2,
        ]);
    }
}
