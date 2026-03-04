<?php

namespace App\UseCase;

use App\Models\User;

class LogoutUseCase
{
    public function execute(User $user): void
    {
        auth('api')->logout();
    }
}
