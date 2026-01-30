<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return RolePermission::with(['role', 'permission'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        // Check if role is super-admin
        $role = DB::table('roles')->where('id', $request->role_id)->first();
        if ($role && $role->slug === 'super-admin') {
            return response()->json(['error' => 'Cannot manually add permissions to super-admin role'], 403);
        }

        return RolePermission::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return RolePermission::with(['role', 'permission'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rolePermission = RolePermission::findOrFail($id);

        // Check if role is super-admin
        $role = DB::table('roles')->where('id', $rolePermission->role_id)->first();
        if ($role && $role->slug === 'super-admin') {
            return response()->json(['error' => 'Cannot update permissions for super-admin role'], 403);
        }

        $request->validate([
            'role_id' => 'sometimes|required|exists:roles,id',
            'permission_id' => 'sometimes|required|exists:permissions,id',
        ]);

        $rolePermission->update($request->all());

        return response()->json($rolePermission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rolePermission = RolePermission::findOrFail($id);

        // Check if role is super-admin
        $role = DB::table('roles')->where('id', $rolePermission->role_id)->first();
        if ($role && $role->slug === 'super-admin') {
            return response()->json(['error' => 'Cannot delete permissions for super-admin role'], 403);
        }

        $rolePermission->delete();

        return response()->noContent();
    }

    /**
     * Get permissions by role.
     */
    public function getByRole($roleId)
    {
        return RolePermission::with('permission')
            ->where('role_id', $roleId)
            ->get();
    }

    /**
     * Sync permissions for a role.
     */
    public function syncPermissions(Request $request, $roleId)
    {
        // Check if role is super-admin
        $role = DB::table('roles')->where('id', $roleId)->first();
        if ($role && $role->slug === 'super-admin') {
            return response()->json(['error' => 'Cannot modify permissions for super-admin role'], 403);
        }

        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $rolePermission = RolePermission::where('role_id', $roleId)->delete();

        $permissions = [];
        foreach ($request->permission_ids as $permissionId) {
            $permissions[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RolePermission::insert($permissions);

        return response()->json(['message' => 'Permissions synced successfully']);
    }
}
