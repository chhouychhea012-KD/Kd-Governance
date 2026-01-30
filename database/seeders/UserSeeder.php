<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create users
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'phone_number' => '1234567890',
                'password' => Hash::make('password'),
                'team_id' => null, // Development Team
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'phone_number' => '1234567892',
                'password' => Hash::make('password'),
                'team_id' => null,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(['email' => $user['email']], $user);
        }
    }
}
