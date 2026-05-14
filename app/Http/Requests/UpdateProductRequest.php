<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'quantity' => 'sometimes|integer|min:0',
            'weight' => 'nullable|string|min:0',
            'woocommerce_category_id' => 'sometimes|array',
        ];
    }
}
