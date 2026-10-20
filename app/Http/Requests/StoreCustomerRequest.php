<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // We return true here because authorization is already handled by Policies in the Controller
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
        ];
    }

    // Custom error messages for better API responses
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be a valid format containing 9 to 15 digits (e.g., 0771234567 or +94771234567).',
        ];
    }
}
