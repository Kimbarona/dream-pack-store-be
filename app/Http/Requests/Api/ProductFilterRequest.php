<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:price_asc,price_desc,newest,manual'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'attributes.size' => ['nullable', 'string', 'exists:attribute_values,slug'],
            'attributes.color' => ['nullable', 'string'],
            'in_stock' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.exists' => 'Selected category does not exist.',
            'sort.in' => 'Sort option is invalid.',
            'per_page.max' => 'Cannot show more than 100 items per page.',
            'price_min.numeric' => 'Minimum price must be a number.',
            'price_max.numeric' => 'Maximum price must be a number.',
            'attributes.size.exists' => 'Selected size does not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'in_stock' => $this->boolean('in_stock'),
        ]);
    }

    public function getValidatedWithColors(): array
    {
        $validated = parent::validated();
        
        // Convert colors string to array if comma-separated
        if (isset($validated['attributes.color'])) {
            $colors = explode(',', $validated['attributes.color']);
            $validated['attributes.color'] = array_map('trim', $colors);
        }
        
        return $validated;
    }
}