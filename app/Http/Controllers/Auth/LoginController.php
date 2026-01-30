<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        // Prepare credentials (mapping username to email)
        $credentials = [
            'email' => $request->username,
            'password' => $request->password,
        ];

        // Log::info('Login attempt', ['username' => $request->username]);

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();

            // Log::info('Login successful', [
            //     'user_id' => Auth::id(),
            //     'session_id' => session()->getId()
            // ]);

            // Always return redirect for Inertia requests
            return redirect()->intended('/dashboard');
        }

        // Log::warning('Login failed - invalid credentials', [
        //     'username' => $request->username
        // ]);

        // Throw validation exception with error message
        throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();
        
        // Log::info('Logout attempt', ['user_id' => $userId]);

        // Log out the user
        Auth::logout();
        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Log::info('Logout successful', ['user_id' => $userId]);

        // Return JSON response for AJAX/Inertia requests
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logout successful',
                'redirect' => '/login'
            ]);
        }

        // Fallback to redirect for traditional requests
        return redirect('/login');
    }

    /**
     * Show the login form.
     *
     * @return \Inertia\Response
     */
    public function showLoginForm()
    {
        return inertia('Login');
    }
}
