<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(RegisterUserRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'data'    => [
                'user' => UserResource::make($result['user']),
                ...$this->authService->tokenMeta($result['token']),
            ],
            'message' => 'User registered successfully.',
        ], 201);
    }
}
