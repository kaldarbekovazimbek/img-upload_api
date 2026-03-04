<?php

namespace App\UseCase;

use App\Models\User;

class RefreshTokenUseCase
{
    public function execute(User $user): string
    {
        return auth()->guard('api')->refresh();
    }
}
