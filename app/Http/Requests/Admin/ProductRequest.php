<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products')->ignore($productId),
            ],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products')->ignore($productId),
            ],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'track_inventory' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'pieces_per_package' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'exists:categories,id'],
            'size' => ['required', 'exists:attribute_values,id'],
            'colors' => ['required', 'array', 'min:1'],
            'colors.*' => ['required', 'exists:attribute_values,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['required', 'exists:product_images,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'price.regex' => 'Price must have maximum 2 decimal places.',
            'sale_price.regex' => 'Sale price must have maximum 2 decimal places.',
            'categories.required' => 'Please select at least one category.',
            'colors.required' => 'Please select at least one color.',
            'size.required' => 'Please select a size.',
            'pieces_per_package.min' => 'Pieces per package must be at least 1.',
            'images.*.image' => 'The file must be an image.',
            'images.*.mimes' => 'Images must be jpeg, jpg, png, or webp format.',
            'images.*.max' => 'Images may not be larger than 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?: \Illuminate\Support\Str::slug($this->title),
            'is_active' => $this->boolean('is_active'),
            'track_inventory' => $this->boolean('track_inventory'),
            'sort_order' => $this->sort_order ?? 0,
            'pieces_per_package' => $this->pieces_per_package ?? 1,
        ]);
    }

    public function getValidatedWithNullables(): array
    {
        $validated = $this->validated();
        
        // Convert sale_price to null if empty
        if (isset($validated['sale_price']) && $validated['sale_price'] === '') {
            $validated['sale_price'] = null;
        }
        
        return $validated;
    }
}