<?php

namespace App\Http\Requests\Api\Teanant;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'roles' => ['required', 'array'],
            'roles.*' => [
                'required',
                function ($value, $fail) {
                    if (!currentTenant()->ownedRoles()->where('id', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                },
            ],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', Rule::in(Permission::values())],
        ];
    }
}
