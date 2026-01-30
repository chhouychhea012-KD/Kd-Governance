<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Super Administrator with all permissions',"level"=>"1"],
            ['name' => 'CEO', 'slug' => 'ceo', 'description' => 'Chief Executive Officer',"level"=>"2"],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator',"level"=>"3"],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Manager',"level"=>"4"],
            ['name' => 'Employee', 'slug' => 'employee', 'description' => 'Employee',"level"=>"5"],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
