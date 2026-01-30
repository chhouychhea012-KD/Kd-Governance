<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Notifications\ResetPasswordNotification;

class AuthController extends Controller
{
    /**
     * Handle API login and return Sanctum token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required|string',
            'remember' => 'required|boolean',
        ]);

        $user = User::where('email', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 🔐 Expiration logic
        $expiresAt = $request->boolean('remember')
            ? Carbon::now()->addDays(30)
            : Carbon::now()->addDay();

        // Create Sanctum token
        $tokenResult = $user->createToken('api-token', ['*'], $expiresAt);
        $token = $tokenResult->plainTextToken;

        // Load roles & permissions
        $user->load(['roles.permissions', 'permissions']);
        $allPermissions = $user->getAllPermissions();

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'roles' => $user->roles,
            'permissions' => $allPermissions,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Logout and revoke current token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Delete the current access token
        $currentToken = $request->user()->currentAccessToken();
        
        if ($currentToken) {
            // Also delete from user_access_tokens table
            $tokenHash = hash('sha256', $currentToken->token);
            UserAccessToken::where('token', $tokenHash)->delete();
            
            // Delete from personal_access_tokens (Sanctum)
            $currentToken->delete();
        }

        Log::info('API Logout successful', [
            'user_id' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['roles.permissions', 'permissions']);
        $allPermissions = $user->getAllPermissions();
        $accessToken = $user->getToken();
        return response()->json([
            'user' => $user,
            'roles' => $user->roles,
            'team'=>$user->teams,
            'permissions' => $allPermissions,
            'access_token'=> $accessToken,
        ]);
    }

    /**
     * Send password reset email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate 6-digit verification code for better UX
        $token = strtoupper(Str::random(6));

        // Store token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Send email notification
        try {
            $user->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            // Log the error but continue - useful for debugging
            Log::error('Failed to send password reset notification: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send email. Please try again later.',
                'error' => 'Email service unavailable'
            ], 500);
        }

        return response()->json([
            'message' => 'Password reset verification code sent to your email',
            'email' => $request->email,
        ]);
    }

    /**
     * Reset password using token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if token exists and is valid (not expired)
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subHours(1)) // Token valid for 1 hour
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'message' => 'Invalid or expired reset token'
            ], 422);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }
}
