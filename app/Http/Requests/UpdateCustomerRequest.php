<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the customer ID from the route to ignore it in the unique check
        $customerId = $this->route('customer')->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customerId,
            'phone' => 'nullable|string|max:20',
        ];
    }
}
