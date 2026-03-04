<?php

namespace App\UseCase;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUseCase
{
    public function execute(array $data): array
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        $token = auth()->guard('api')->login($user);

        return ['user' => $user, 'token' => $token];
    }
}
