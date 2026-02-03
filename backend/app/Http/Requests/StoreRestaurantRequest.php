<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug',
            'address' => 'nullable|string',
            'logo_path' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Este slug ya está en uso. Por favor, elige otro.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert is_active to proper boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true
            ]);
        }
    }
}
