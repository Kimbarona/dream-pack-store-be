<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value && $categoryId && $value == $categoryId) {
                        $fail('A category cannot be its own parent.');
                    }
                    // Check for circular reference
                    if ($value && $categoryId) {
                        $childIds = $this->getChildCategoryIds($categoryId);
                        if (in_array($value, $childIds)) {
                            $fail('Cannot set a child category as parent.');
                        }
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('categories')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'parent_id.exists' => 'Selected parent category does not exist.',
            'sort_order.min' => 'Sort order must be 0 or greater.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?: \Illuminate\Support\Str::slug($this->name),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    private function getChildCategoryIds($categoryId): array
    {
        $childIds = [];
        $children = \App\Models\Category::where('parent_id', $categoryId)->get();
        
        foreach ($children as $child) {
            $childIds[] = $child->id;
            $childIds = array_merge($childIds, $this->getChildCategoryIds($child->id));
        }
        
        return $childIds;
    }
}