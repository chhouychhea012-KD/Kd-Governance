<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;

// Login Routes
Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login')->middleware('guest');

Route::get('/forgot-password', function () {
    return Inertia::render('ForgotPassword');
})->name('forgot-password')->middleware('guest');

Route::get('/reset-password', function () {
    return Inertia::render('ResetPassword');
})->name('reset-password')->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard - Everyone can access
    Route::get('/', function () {
        return redirect('/dashboard');
    });

    Route::get('/dashboard', function () {
        // Log::info('App name: ' . config('app.name'));
        return Inertia::render('Dashboard', [
            'app_name' => config('app.name'),
        ]);
    })->middleware('permission:read-dashboard')->name('dashboard');

    // Teams Management
    Route::get('/teams', function () {
        return Inertia::render('Teams');
    })->middleware('permission:read-teams');

    // Users Management
    Route::get('/users', function () {
        return Inertia::render('Users');
    })->middleware('permission:read-user');

    // Roles Management
    Route::get('/roles', function () {
        return Inertia::render('Roles');
    })->middleware('permission:read-role');

    // Permissions Management
    Route::get('/permissions', function () {
        return Inertia::render('Permissions');
    })->middleware('permission:read-permission');

    // Role Permissions Management
    Route::get('/role-permissions', function () {
        return Inertia::render('RolePermissions');
    })->middleware('permission:read-role-permission');

    // Settings Management
    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->middleware('permission:read-setting');

    // Profile Management
    Route::get('/profile', function () {
        return Inertia::render('Profile');
    })->name('profile');

    Route::get('/profile/update', function () {
        return Inertia::render('UpdateProfile');
    })->name('profile.update');

    Route::get('/profile/change-password', function () {
        return Inertia::render('ChangePassword');
    })->name('profile.change-password');
});

// Error Pages
Route::get('/403', function () {
    return Inertia::render('Errors/403');
})->name('403');

Route::get('/404', function () {
    return Inertia::render('Errors/404');
})->name('404');

// Fallback for 404
Route::fallback(function () {
    return Inertia::render('Errors/404');
});
// require __DIR__.'/api.php';
