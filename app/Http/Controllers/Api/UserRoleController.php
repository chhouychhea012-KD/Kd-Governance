<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserRole::with(['user', 'role'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        return UserRole::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(UserRole $userRole)
    {
        return $userRole->load(['user', 'role']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserRole $userRole)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $userRole->update($request->all());
        return $userRole;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserRole $userRole)
    {
        $userRole->delete();
        return response()->noContent();
    }
}
