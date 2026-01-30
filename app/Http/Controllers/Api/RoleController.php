<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        
        // Get the highest authority level of the current user (e.g., 2 for CEO)
        $currentUserLevel = $currentUser->roles()->min('level');

        if (is_null($currentUserLevel)) {
            return response()->json([], 200);
        }

        // Return roles that are at your level or lower (level number >= 2)
        return Role::where('level', '>=', $currentUserLevel)
            ->orderBy('level', 'asc')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return Role::create($request->all());
    }

    public function show(Role $role)
    {
        return $role;
    }

    public function update(Request $request, Role $role)
    {
        // Prevent updating super-admin role
        if ($role->slug === 'super-admin') {
            return response()->json(['error' => 'Cannot update super-admin role'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $role->update($request->all());
        return $role;
    }

    public function destroy($id)
    {
        // 1. Fetch the role or fail
        $role = Role::findOrFail($id);

        // Prevent deleting super-admin role
        if ($role->slug === 'super-admin') {
            return response()->json(['error' => 'Cannot delete super-admin role'], 403);
        }

        $currentUser = auth()->user();
        $currentUserHighestRole = $currentUser->roles()->orderBy('level', 'asc')->first();

        // 2. Hierarchy Check
        // Prevent deleting a role that is at or above your own authority level
        // (Example: A Level 2 user cannot delete a Level 2 or Level 1 role)
        if (!$currentUserHighestRole || $role->level <= $currentUserHighestRole->level) {
            return response()->json([
                'error' => 'You do not have permission to delete roles at or above your authority level'
            ], 403);
        }

        // 3. Prevent deleting if users are still assigned to this role
        // This avoids leaving users without any roles/permissions
        if ($role->users()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete role because it is currently assigned to users'
            ], 422);
        }

        // 4. Remove relationships from pivot tables to avoid Integrity Constraint Violation
        // This clears entries in role_permissions table
        $role->permissions()->detach();

        // 5. Delete the role
        $role->delete();

        return response()->noContent();
    }
}
