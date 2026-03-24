<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): array
    {
        $user  = User::create($data);
        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password): ?string
    {
        return Auth::guard('api')->attempt(['email' => $email, 'password' => $password]) ?: null;
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function tokenMeta(string $token): array
    {
        return [
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }
}
