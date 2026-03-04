<?php

namespace App\Http\Controllers;

use App\Enums\ApiCode;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\UseCase\LoginUseCase;
use App\UseCase\LogoutUseCase;
use App\UseCase\RefreshTokenUseCase;
use App\UseCase\RegisterUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUseCase $registerUseCase,
        private readonly LoginUseCase $loginUseCase,
        private readonly LogoutUseCase $logoutUseCase,
        private readonly RefreshTokenUseCase $refreshTokenUseCase,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        ['user' => $user, 'token' => $token] = $this->registerUseCase->execute($data);

        return ApiResponse::success([
            'user' => $user,
            'auth' => ['token' => $token, 'type' => 'bearer'],
        ], 'User created successfully.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->loginUseCase->execute($data['email'], $data['password']);

        if (!$result) {
            return ApiResponse::error(ApiCode::INVALID_CREDENTIALS, 'Invalid email or password.', 401);
        }

        return ApiResponse::success([
            'user' => $result['user'],
            'auth' => ['token' => $result['token'], 'type' => 'bearer'],
        ], 'Logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->logoutUseCase->execute($request->user());

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = $this->refreshTokenUseCase->execute($request->user());

        return ApiResponse::success([
            'auth' => ['token' => $token, 'type' => 'bearer'],
        ], 'Token refreshed successfully.');
    }
}
