<?php

namespace App\UseCase;

use App\Models\User;

class LoginUseCase
{
    public function execute(string $email, string $password): ?array
    {
        $token = auth('api')->attempt(['email' => $email, 'password' => $password]);

        if (!$token) {
            return null;
        }

        $user = User::where('email', $email)->first();

        return ['user' => $user, 'token' => $token];
    }
}
