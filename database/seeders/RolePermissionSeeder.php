<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, get the super-admin role and assign ALL permissions to it
        $superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            // Get all permissions
            $allPermissions = DB::table('permissions')->pluck('id')->toArray();
            
            // Delete existing super-admin permissions first
            DB::table('role_permissions')->where('role_id', $superAdminRole->id)->delete();
            
            // Assign all permissions to super-admin
            foreach ($allPermissions as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_id' => $superAdminRole->id,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'role_id' => $superAdminRole->id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Other roles with specific permissions
        $rolePermissions = [
            ['role_name' => 'admin', 'permissions' => ['Read User', 'Create User', 'Update User', 'Delete User', 'Read Role', 'Create Role', 'Update Role', 'Delete Role', 'Read Permission', 'Create Permission', 'Update Permission', 'Delete Permission', 'Read Role Permission', 'Create Role Permission', 'Update Role Permission', 'Delete Role Permission', 'Read Teams', 'Create Teams', 'Update Teams', 'Delete Teams', 'Read Question', 'Create Question', 'Update Question', 'Delete Question', 'Read Evaluation', 'Create Evaluation', 'Update Evaluation', 'Delete Evaluation', 'Read Setting', 'Create Setting', 'Update Setting', 'Delete Setting', 'Read Dashboard', 'Read Report']],
        ];

        foreach ($rolePermissions as $rp) {
            $role = DB::table('roles')->where('slug', $rp['role_name'])->first();
            if ($role) {
                foreach ($rp['permissions'] as $permName) {
                    $permission = DB::table('permissions')->where('name', $permName)->first();
                    if ($permission) {
                        DB::table('role_permissions')->updateOrInsert(
                            [
                                'role_id' => $role->id,
                                'permission_id' => $permission->id,
                            ],
                            [
                                'role_id' => $role->id,
                                'permission_id' => $permission->id,
                            ]
                        );
                    }
                }
            }
        }
    }
}
