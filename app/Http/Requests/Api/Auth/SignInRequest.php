<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SignInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'password' => ['required', 'string'],
            'remember' => ['required', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $credentials = $this->only('email', 'password');

        if (!Auth::attempt($credentials)) throw ValidationException::withMessages([
            'email' => "The provided credentials are incorrect.",
        ]);
    }

    public function authenticatedUser(): User
    {
        return Auth::user();
    }

    public function getTokenExpiration(): Carbon
    {
        return $this->boolean('remember') ? now()->addMonths(6) : now()->addHours(12);
    }
}
