<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\SignInRequest;
use App\Http\Requests\Api\Auth\SignUpRequest;
use App\Http\Resources\Api\TenantResource;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function signUp(SignUpRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $user->createTenant(default: true);

        return $this->success('Sign up successful.', 201);
    }

    public function signIn(SignInRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = $request->authenticatedUser();

        $token = $user->createToken(name: 'auth_token', expiresAt: $request->getTokenExpiration())->plainTextToken;

        return $this->success('Sign in successful.', 200, [
            'token' => [
                'accessToken' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => $request->getTokenExpiration()->getTimestamp(),
            ],
            'user' => new UserResource($user),
            'defaultTenant' => new TenantResource($user->defaultTenant),
        ]);
    }

    public function user(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    public function signOut(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success('Sign out successful.');
    }
}
