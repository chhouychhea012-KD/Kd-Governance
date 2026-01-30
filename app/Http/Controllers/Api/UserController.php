<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        
        // Get the highest authority role level of the current user (the smallest number)
        $currentUserLevel = $currentUser->roles()->min('level');

        // If the user has no roles, they shouldn't see anyone (or handle as guest)
        if (is_null($currentUserLevel)) {
            return response()->json([], 200);
        }

        // Filter users: only show users who have at least one role with level >= current user level
        return User::with(['team', 'subTeam', 'roles', 'permissions'])
            ->whereHas('roles', function ($query) use ($currentUserLevel) {
                $query->where('level', '>=', $currentUserLevel);
            })
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();
        $currentUserHighestRole = $currentUser->roles()->orderBy('level', 'asc')->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string',
            'team_id' => 'nullable|exists:teams,id',
            'role_id' => 'required|exists:roles,id',
            'sub_team_id' => 'nullable|exists:teams,id',
        ]);

        $targetRole = Role::findOrFail($request->role_id);

        // Hierarchy check: Cannot create user with a level higher than your own
        if (!$currentUserHighestRole || $targetRole->level < $currentUserHighestRole->level) {
            return response()->json(['error' => 'You do not have permission to create users with this role level'], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'team_id' => $request->team_id,
            'sub_team_id' => $request->sub_team_id,
        ]);

        $user->roles()->attach($targetRole->id);

        return $user->load(['team', 'roles', 'permissions']);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user->load(['team', 'roles', 'permissions']);
    }
    public function showByTeam($team_id)
    {
        return User::where('team_id', $team_id)
            ->orWhere('sub_team_id', $team_id)
            ->get();
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // 1. Find the target user first to resolve the "Undefined variable $user" error
        $user = User::findOrFail($id);
        
        $currentUser = $request->user();
        // Get the highest role (lowest level number) of the person doing the editing
        $currentUserHighestRole = $currentUser->roles()->orderBy('level', 'asc')->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'password' => 'nullable|string|min:8',
            'phone_number' => 'required|string',
            'team_id' => 'nullable|exists:teams,id',
            'role_id' => 'required|exists:roles,id',
            'sub_team_id' => 'nullable|exists:teams,id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $newRole = Role::findOrFail($request->role_id);

        // 2. Get the target user's current highest role for hierarchy check
        $targetUserHighestRole = $user->roles()->orderBy('level', 'asc')->first();
        
        // 3. Hierarchy checks
        if (!$currentUserHighestRole || 
            // A: Cannot edit a user who has a higher authority (lower level) than you
            ($targetUserHighestRole && $targetUserHighestRole->level < $currentUserHighestRole->level) ||
            // B: Cannot assign a role that has a higher authority (lower level) than your own
            ($newRole->level < $currentUserHighestRole->level)) {
            
            return response()->json([
                'error' => 'You do not have permission to manage users or roles at this authority level'
            ], 403);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'team_id' => $request->team_id,
            'sub_team_id' => $request->sub_team_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete('images/profile/' . $user->profile_image);
            }

            // Store new profile image
            $image = $request->file('profile_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('images/profile', $imageName, 'public');
            $data['profile_image'] = $imageName;
        }

        // Update the user details
        $user->update($data);

        // 4. Update the role (sync will remove old roles and add the new one)
        $user->roles()->sync([$newRole->id]);

        return $user->load(['team', 'roles', 'permissions']);
    }
    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'phone_number' => 'required|string',
            'team_id' => 'nullable|exists:teams,id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'team_id' => $request->team_id,
        ];

        if ($request->hasFile('profile_image')) {
            // 1. Define the destination path in the public folder
            $destinationPath = public_path('images/profile');

            // 2. Delete old image if it exists
            if ($user->profile_image) {
                $oldFile = $destinationPath . '/' . $user->profile_image;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // 3. Prepare new file
            $image = $request->file('profile_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // 4. Use move() to place it directly in public/images/profile
            $image->move($destinationPath, $imageName);
            
            $data['profile_image'] = $imageName;
        }

        $user->update($data);

        return response()->json($user->load(['team', 'roles']));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // 1. Fetch the user
        $user = User::findOrFail($id);

        $currentUser = Auth::user();
        $currentUserHighestRole = $currentUser->roles()->orderBy('level', 'asc')->first();
        $targetUserHighestRole = $user->roles()->orderBy('level', 'asc')->first();

        // 2. Hierarchy Check
        // Prevent deleting users at or above your own authority level
        if (!$currentUserHighestRole ||
            ($targetUserHighestRole && $targetUserHighestRole->level <= $currentUserHighestRole->level)) {

            return response()->json([
                'error' => 'You do not have permission to delete users at this authority level'
            ], 403);
        }

        // 3. Remove relationships first to avoid Foreign Key Integrity Violation
        $user->roles()->detach();
        $user->permissions()->detach(); // Do this if you have user_permissions table too

        // 4. Perform deletion
        $user->delete();

        return response()->noContent();
    }

    /**
     * Change password for the authenticated user.
     */
    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 1. Find the specific user by ID
        $user = User::findOrFail($id);
        
        // 2. Security Check: Ensure the logged-in user is either the owner or an admin
        $currentUser = $request->user();
        if ($currentUser->id !== $user->id && !$currentUser->hasRole('admin')) {
            return response()->json([
                'message' => 'You do not have permission to change this password.'
            ], 403);
        }

        // 3. Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // 4. Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
    // Reset Password for Admin
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user = User::findOrFail($id);
        $currentUser = $request->user();

        // Ensure admin can reset password
        $currentUserHighestRole = $currentUser->roles()->orderBy('level', 'asc')->first();
        $targetUserHighestRole = $user->roles()->orderBy('level', 'asc')->first();

        if (!$currentUserHighestRole || ($targetUserHighestRole && $targetUserHighestRole->level < $currentUserHighestRole->level)) {
            return response()->json(['error' => 'You do not have permission to reset this password'], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password reset successfully']);
    }

    /**
     * Filter users by team, sub_team, and levels.
     * Returns only basic user data (id, name, etc.) without nested relationships.
     */
    public function getByTeamAndLevels(Request $request, $team_id)
    {
        $query = User::where(function($query) use ($team_id) {
                $query->where('team_id', $team_id)
                      ->whereNull('sub_team_id');
            })
            ->orWhere('sub_team_id', $team_id);



        return $query->get();
    }
}
