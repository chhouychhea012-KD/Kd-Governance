<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAccessToken;
use Illuminate\Http\Request;

class UserAccessTokenController extends Controller
{
    public function index()
    {
        return UserAccessToken::with(['user', 'tokenable'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'token' => 'required|string|unique:user_access_tokens,token',
        ]);

        return UserAccessToken::create($request->all());
    }

    public function show(UserAccessToken $userAccessToken)
    {
        return $userAccessToken->load('user');
    }

    public function update(Request $request, UserAccessToken $userAccessToken)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'token' => 'required|string|unique:user_access_tokens,token,' . $userAccessToken->id,
        ]);

        $userAccessToken->update($request->all());
        return $userAccessToken;
    }

    public function destroy(UserAccessToken $userAccessToken)
    {
        $userAccessToken->delete();
        return response()->noContent();
    }
}