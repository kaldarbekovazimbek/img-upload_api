<?php

namespace App\Http\Controllers;

use App\Enums\ApiCode;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $request->validated();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $token = auth()->guard('api')->login($user);

        return ApiResponse::success([
            'user' => $user,
            'auth' => ['token' => $token, 'type' => 'bearer'],
        ], 'User created successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->validated();

        $token = auth('api')->attempt($request->only('email', 'password'));

        if (!$token) {
            return ApiResponse::error(ApiCode::INVALID_CREDENTIALS, 'Invalid email or password.', 401);
        }

        return ApiResponse::success([
            'user' => auth('api')->user(),
            'auth' => ['token' => $token, 'type' => 'bearer'],
        ], 'Logged in successfully.');
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function refresh(): JsonResponse
    {
        return ApiResponse::success([
            'auth' => ['token' => auth()->guard('api')->refresh(), 'type' => 'bearer'],
        ], 'Token refreshed successfully.');
    }
}
