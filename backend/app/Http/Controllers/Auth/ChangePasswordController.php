<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class ChangePasswordController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            user:            $request->user(),
            currentPassword: $request->string('current_password')->toString(),
            newPassword:     $request->string('password')->toString(),
        );

        return response()->json([
            'data'    => null,
            'message' => 'Password changed successfully.',
        ]);
    }
}
