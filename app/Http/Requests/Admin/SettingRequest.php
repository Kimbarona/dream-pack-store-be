<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'site_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'remove_logo' => ['boolean'],
        ];

        // Banner validation
        if ($this->has('banners')) {
            foreach ($this->input('banners', []) as $index => $banner) {
                $rules["banners.{$index}.image"] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'];
                $rules["banners.{$index}.link"] = ['nullable', 'string', 'max:500'];
                $rules["banners.{$index}.sort_order"] = ['required', 'integer', 'min:0'];
            }
        }

        // Banner deletion validation
        if ($this->has('delete_banners')) {
            foreach ($this->input('delete_banners', []) as $index => $bannerId) {
                $rules["delete_banners.{$index}"] = ['required', 'integer'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'site_logo.image' => 'The logo must be an image.',
            'site_logo.mimes' => 'Logo must be jpeg, jpg, png, or webp format.',
            'site_logo.max' => 'Logo may not be larger than 2MB.',
            'slogan.max' => 'Slogan may not be greater than 255 characters.',
            'banners.*.image' => 'Banner images must be image files.',
            'banners.*.mimes' => 'Banner images must be jpeg, jpg, png, or webp format.',
            'banners.*.max' => 'Banner images may not be larger than 2MB.',
            'banners.*.link.max' => 'Banner links may not be greater than 500 characters.',
            'banners.*.sort_order.min' => 'Banner sort order must be 0 or greater.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }
}