<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'between:1,120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ];
    }
}
