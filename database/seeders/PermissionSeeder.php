<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users
            ['name' => 'Read User', 'slug' => 'read-user', 'group_name' => 'User Management', 'description' => 'Read users'],
            ['name' => 'Create User', 'slug' => 'create-user', 'group_name' => 'User Management', 'description' => 'Create users'],
            ['name' => 'Update User', 'slug' => 'update-user', 'group_name' => 'User Management', 'description' => 'Update users'],
            ['name' => 'Delete User', 'slug' => 'delete-user', 'group_name' => 'User Management', 'description' => 'Delete users'],
            // Roles
            ['name' => 'Read Role', 'slug' => 'read-role', 'group_name' => 'Role Management', 'description' => 'Read roles'],
            ['name' => 'Create Role', 'slug' => 'create-role', 'group_name' => 'Role Management', 'description' => 'Create roles'],
            ['name' => 'Update Role', 'slug' => 'update-role', 'group_name' => 'Role Management', 'description' => 'Update roles'],
            ['name' => 'Delete Role', 'slug' => 'delete-role', 'group_name' => 'Role Management', 'description' => 'Delete roles'],
            // Permissions
            ['name' => 'Read Permission', 'slug' => 'read-permission', 'group_name' => 'Permission Management', 'description' => 'Read permissions'],
            ['name' => 'Create Permission', 'slug' => 'create-permission', 'group_name' => 'Permission Management', 'description' => 'Create permissions'],
            ['name' => 'Update Permission', 'slug' => 'update-permission', 'group_name' => 'Permission Management', 'description' => 'Update permissions'],
            ['name' => 'Delete Permission', 'slug' => 'delete-permission', 'group_name' => 'Permission Management', 'description' => 'Delete permissions'],
            // Role Permissions
            ['name' => 'Read Role Permission', 'slug' => 'read-role-permission', 'group_name' => 'Role Permission Management', 'description' => 'Read role permissions'],
            ['name' => 'Create Role Permission', 'slug' => 'create-role-permission', 'group_name' => 'Role Permission Management', 'description' => 'Create role permissions'],
            ['name' => 'Update Role Permission', 'slug' => 'update-role-permission', 'group_name' => 'Role Permission Management', 'description' => 'Update role permissions'],
            ['name' => 'Delete Role Permission', 'slug' => 'delete-role-permission', 'group_name' => 'Role Permission Management', 'description' => 'Delete role permissions'],
            // Teams
            ['name' => 'Read Teams', 'slug' => 'read-teams', 'group_name' => 'Team Management', 'description' => 'Read teams'],
            ['name' => 'Create Teams', 'slug' => 'create-teams', 'group_name' => 'Team Management', 'description' => 'Create teams'],
            ['name' => 'Update Teams', 'slug' => 'update-teams', 'group_name' => 'Team Management', 'description' => 'Update teams'],
            ['name' => 'Delete Teams', 'slug' => 'delete-teams', 'group_name' => 'Team Management', 'description' => 'Delete teams'],
            // Questions
            ['name' => 'Read Question', 'slug' => 'read-question', 'group_name' => 'Question Management', 'description' => 'Read questions'],
            ['name' => 'Create Question', 'slug' => 'create-question', 'group_name' => 'Question Management', 'description' => 'Create questions'],
            ['name' => 'Update Question', 'slug' => 'update-question', 'group_name' => 'Question Management', 'description' => 'Update questions'],
            ['name' => 'Delete Question', 'slug' => 'delete-question', 'group_name' => 'Question Management', 'description' => 'Delete questions'],
            // Evaluations
            ['name' => 'Read Evaluation', 'slug' => 'read-evaluation', 'group_name' => 'Evaluation', 'description' => 'Read evaluations'],
            ['name' => 'Create Evaluation', 'slug' => 'create-evaluation', 'group_name' => 'Evaluation', 'description' => 'Create evaluations'],
            ['name' => 'Update Evaluation', 'slug' => 'update-evaluation', 'group_name' => 'Evaluation', 'description' => 'Update evaluations'],
            ['name' => 'Delete Evaluation', 'slug' => 'delete-evaluation', 'group_name' => 'Evaluation', 'description' => 'Delete evaluations'],
            // Settings
            ['name' => 'Read Setting', 'slug' => 'read-settings', 'group_name' => 'Settings', 'description' => 'Read settings'],
            ['name' => 'Create Setting', 'slug' => 'create-settings', 'group_name' => 'Settings', 'description' => 'Create settings'],
            ['name' => 'Update Setting', 'slug' => 'update-settings', 'group_name' => 'Settings', 'description' => 'Update settings'],
            ['name' => 'Delete Setting', 'slug' => 'delete-settings', 'group_name' => 'Settings', 'description' => 'Delete settings'],
            // Dashboard
            ['name' => 'Read Dashboard', 'slug' => 'read-dashboard', 'group_name' => 'Dashboard', 'description' => 'Access dashboard'],
            // Reports
            ['name' => 'Read Report', 'slug' => 'read-report', 'group_name' => 'Reporting', 'description' => 'Read reports'],
            // Improvements
            ['name' => 'Read Improvement', 'slug' => 'read-improvements', 'group_name' => 'Evaluation', 'description' => 'Read improvements'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
