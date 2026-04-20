<?php

namespace App\Http\Requests\Auth;

use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'string', 'in:US,CA'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'invite_token' => ['sometimes', 'string'], // For future invite system
            'device_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'phone_country_code.in' => 'Only US and CA phone numbers are supported.',
        ];
    }
}
