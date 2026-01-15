<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            "name" => ['required', 'string', 'max:255'],
            "email" => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . currentUser()->id],
        ]);

        currentUser()->update([
            "name" => $request->name,
            "email" => $request->email,
        ]);

        return $this->success("User account updated successfully.");
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        currentUser()->update([
            'password' => $validated['password'],
        ]);

        return $this->success("Password updated successfully.");
    }

    public function resetTenant(): JsonResponse
    {
        $tenant = currentTenant();

        if (currentUser()->cannot('reset', $tenant)) return $this->error("This action is unauthorized.", 403);

        // Delete all related data

        return $this->success("Tenant reset successfully.");
    }
}
