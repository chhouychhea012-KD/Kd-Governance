<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // public function index()
    // {
    //     return Permission::all();
    // }
    // public function getPermissionByUser()
    // {
    //     return auth()->user()->getAllPermissions();
    // }
    public function getPermissionByUser()
    {
        $user = auth()->user();

        if ($user->hasRole('super-admin')) {
            return Permission::all();
        }

        return $user->getAllPermissions();
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions',
            'group_name' => 'required|string|max:255',
        ]);

        return Permission::create($request->all());
    }

    public function show(Permission $permission)
    {
        return $permission;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $id,
            'group_name' => 'required|string|max:255',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update($request->all());
        return $permission;
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->noContent();
    }
}