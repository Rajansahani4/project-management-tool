<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(LoginUserRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->email, $request->password);

        if (! $token) {
            return response()->json([
                'data'    => null,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json([
            'data'    => $this->authService->tokenMeta($token),
            'message' => 'Login successful.',
        ]);
    }
}
