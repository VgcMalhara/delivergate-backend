<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by Policies in the Controller
        return true;
    }

    public function rules(): array
    {
        // Get the customer ID from the route to ignore it in the unique check
        $customerId = $this->route('customer')->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customerId,
            // Strict regex validation: Allows optional '+' and 9 to 15 digits
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
