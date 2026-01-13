<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create($validated);

        $user->createDefaultTenant();

        return response()->json(['message' => 'Sign up successful.'], 201);
    }

    public function signIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) return response()->json(['message' => 'The provided credentials are incorrect.'], 401);

        /** @var User $user */
        $user = Auth::user();

        $tokenExpiration = $validated['remember'] ? now()->addMonths(6) : now()->addHours(12);

        $token = $user->createToken('Mobile Token', ['*'], $tokenExpiration);

        return response()->json([
            'accessToken' => $token->plainTextToken,
            'tokenType' => 'Bearer',
            'expiresAt' => $tokenExpiration->toDateTimeString(),
        ]);
    }

    public function signOut(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Sign out successful.']);
    }
}
