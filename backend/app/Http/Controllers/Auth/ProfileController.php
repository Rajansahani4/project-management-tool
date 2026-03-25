<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(
            user: $request->user(),
            name: $request->string('name')->toString(),
        );

        return response()->json([
            'data'    => UserResource::make($user),
            'message' => 'Profile updated successfully.',
        ]);
    }
}
