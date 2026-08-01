<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->assignRole('pemohon');

        return self::created(new UserResource($user), 'Registrasi berhasil');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!auth()->attempt($credentials)) {
            return self::error('Email atau password salah', 401);
        }

        $user = auth()->user();
        $user->load('roles');
        $token = $user->createToken('auth_token')->plainTextToken;

        return self::success([
            'user' => new UserResource($user),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'unread_notifications_count' => $user->unreadNotifications()->count(),
            'token' => $token,
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return self::success(null, 'Logout berhasil');
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        return self::success([
            'user' => new UserResource($user),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'unread_notifications_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
