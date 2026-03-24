<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class LogoutController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'data'    => null,
            'message' => 'Logged out successfully.',
        ]);
    }
}
