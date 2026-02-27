<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $token = auth()->guard('api')->login($user);
        return response()->json([
            "status" => "success",
            "message" => "User created successfully",
            "user" => $user,
            "auth" => [
                "token" => $token,
                "type" => "bearer"
            ]
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $credentials = $request->only('email', 'password');

        $token = auth('api')->attempt($credentials);
        if (!$token) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized"
            ], 401);
        }

        $user = auth('api')->user();
        return response()->json([
            "status" => "success",
            "message" => "User login successfully",
            "user" => $user,
            "auth" => [
                "token" => $token,
                "type" => "bearer"
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        auth('api')->logout();
        return response()->json([
            "status" => "success",
            "message" => "User successfully signed out"
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        return response()->json([
            "status" => "success",
            "message" => "Token successfully refreshed",
            "auth" => [
                "token" => auth()->guard('api')->refresh(),
                "type" => "bearer"
            ],
        ]);
    }
}
