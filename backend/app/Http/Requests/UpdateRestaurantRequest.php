<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $restaurantId = $this->route('restaurant');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:restaurants,slug,' . $restaurantId,
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'preparation_time_minutes' => 'nullable|integer|min:1|max:120',
            'is_active' => 'sometimes|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'color_name' => 'nullable|string|size:7',
            'color_address' => 'nullable|string|size:7',
            'color_btn_bg' => 'nullable|string|size:7',
            'color_btn_text' => 'nullable|string|size:7',
            'color_cat_title' => 'nullable|string|size:7',
            'color_prod_title' => 'nullable|string|size:7',
            'color_price' => 'nullable|string|size:7',
            'color_card_bg' => 'nullable|string|size:7',
            'color_bg' => 'nullable|string|size:7',
            'whatsapp' => 'nullable|string|max:20',
            'instagram' => 'nullable|string|max:50',
            'facebook' => 'nullable|string|max:50',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Este slug ya está en uso. Por favor, elige otro.',
        ];
    }
}
