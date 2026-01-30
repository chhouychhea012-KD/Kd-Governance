<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRoles = [
            ['email' => 'superadmin@example.com', 'role_name' => 'super-admin'],
            ['email' => 'ceo@example.com', 'role_name' => 'ceo'],
            ['email' => 'admin@example.com', 'role_name' => 'admin'],
        ];

        foreach ($userRoles as $ur) {
            $user = DB::table('users')->where('email', $ur['email'])->first();
            $role = DB::table('roles')->where('slug', $ur['role_name'])->first();
            if ($user && $role) {
                DB::table('user_roles')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                    ]
                );
            }
        }
    }
}
