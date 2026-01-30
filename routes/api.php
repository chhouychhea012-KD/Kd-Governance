<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UserAccessTokenController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GeneralSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('stats', [DashboardController::class, 'stats']);
    Route::get('chart-data', [DashboardController::class, 'chartData']);
    Route::get('teams-users', [DashboardController::class, 'teamsWithUsers']);
});

// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    /* ===================== TEAMS ===================== */
    Route::get('teams', [TeamController::class, 'index'])->middleware('permission:read-teams');
    Route::get('teams/parent', [TeamController::class, 'getParentTeams']);
    Route::get('teams/parent-with-children', [TeamController::class, 'getParentTeamsWithChildren']);
    Route::get('teams/{id}/sub-teams', [TeamController::class, 'getSubTeams']);
    Route::post('teams', [TeamController::class, 'store'])->middleware('permission:create-teams');
    Route::get('teams/{id}', [TeamController::class, 'show'])->middleware('permission:read-teams');
    Route::put('teams/{id}', [TeamController::class, 'update'])->middleware('permission:update-teams');
    Route::delete('teams/{id}', [TeamController::class, 'destroy'])->middleware('permission:delete-teams');

    /* ===================== USERS ===================== */
    Route::middleware('permission:read-user')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::get('users/team/{team_id}', [UserController::class, 'showByTeam']);
        Route::get('users/team/{team_id}/levels', [UserController::class, 'getByTeamAndLevels']);
    });
    
    Route::post('users', [UserController::class, 'store'])->middleware('permission:create-user');
    Route::put('users/{id}', [UserController::class, 'update'])->middleware('permission:update-user');
    Route::put('users/profile/{id}', [UserController::class, 'updateProfile'])->middleware('permission:update-user');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->middleware('permission:delete-user');
    Route::put('users/change-password/{id}', [UserController::class, 'changePassword']);
    Route::post('/users/reset-password/{id}', [UserController::class, 'resetPassword']);

    /* ===================== ROLES ===================== */
    Route::middleware('permission:read-role')->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{id}', [RoleController::class, 'show']);
    });
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:create-role');
    Route::put('roles/{id}', [RoleController::class, 'update'])->middleware('permission:update-role');
    Route::delete('roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:delete-role');

    /* ===================== PERMISSIONS ===================== */
    Route::middleware('permission:read-permission')->group(function () {
        // Route::get('permissions', [PermissionController::class, 'index']);
        Route::get('permissions/{id}', [PermissionController::class, 'show']);
    });
    
    Route::get('permissions', [PermissionController::class, 'getPermissionByUser']);
    Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:create-permission');
    Route::put('permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:update-permission');
    Route::delete('permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:delete-permission');

    /* ===================== USER ROLES ===================== */
    Route::get('user-roles', [UserRoleController::class, 'index']);
    Route::post('user-roles', [UserRoleController::class, 'store']);
    Route::get('user-roles/{id}', [UserRoleController::class, 'show']);
    Route::put('user-roles/{id}', [UserRoleController::class, 'update']);
    Route::delete('user-roles/{id}', [UserRoleController::class, 'destroy']);

    /* ===================== ROLE PERMISSIONS ===================== */
    Route::middleware('permission:read-role-permission')->group(function () {
        Route::get('role-permissions', [RolePermissionController::class, 'index']);
        Route::get('role-permissions/{id}', [RolePermissionController::class, 'show']);
    });
    Route::post('role-permissions', [RolePermissionController::class, 'store'])->middleware('permission:create-role-permission');
    Route::put('role-permissions/{id}', [RolePermissionController::class, 'update'])->middleware('permission:update-role-permission');
    Route::delete('role-permissions/{id}', [RolePermissionController::class, 'destroy'])->middleware('permission:delete-role-permission');
    Route::get('roles/{role_id}/permissions', [RolePermissionController::class, 'getByRole']);
    Route::post('roles/{role_id}/permissions/sync', [RolePermissionController::class, 'syncPermissions']);

    /* ===================== USER ACCESS TOKENS (SANCTUM) ===================== */
    Route::get('user-access-tokens', [UserAccessTokenController::class, 'index']);
    Route::post('user-access-tokens', [UserAccessTokenController::class, 'store']);
    Route::get('user-access-tokens/{id}', [UserAccessTokenController::class, 'show']);
    Route::delete('user-access-tokens/{id}', [UserAccessTokenController::class, 'destroy']);

    /* ===================== GENERAL SETTINGS ===================== */
    Route::middleware('permission:read-settings')->group(function () {
        Route::get('general-settings', [GeneralSettingController::class, 'index']);
        Route::post('general-settings', [GeneralSettingController::class, 'store'])->middleware('permission:create-settings');
        Route::get('general-settings/{generalSetting}', [GeneralSettingController::class, 'show']);
        Route::put('general-settings/{generalSetting}', [GeneralSettingController::class, 'update'])->middleware('permission:update-settings');
        Route::delete('general-settings/{generalSetting}', [GeneralSettingController::class, 'destroy'])->middleware('permission:delete-settings');
        Route::get('general-settings/target-score', [GeneralSettingController::class, 'getTargetScore']);
        Route::put('general-settings/target-score', [GeneralSettingController::class, 'updateTargetScore'])->middleware('permission:update-settings');
    });
});
