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
            'phone' => '1227343176',
            'type' => 1,
        ]);

        User::create([
            'name' => 'ميشيل',
            'email' => 'misho@gmail.com',
            'password' => bcrypt('123456'),
            'membership_code' => 'E1C1F1NR3',
            'phone' => '01278783887',
            'type' => 2,
        ]);

        User::create([
            'name' => 'مارك',
            'email' => 'mark@gmail.com',
            'password' => bcrypt('123456'),
            'membership_code' => 'E1C1F1NR3',
            'phone' => '01208486948',
            'type' => 2,
        ]);
    }
}
