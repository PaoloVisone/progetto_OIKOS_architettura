<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Valuta di mettere una policy/controllo ruolo qui
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:projects,slug',
            'description'      => 'required|string|max:1000',
            'long_description' => 'nullable|string',
            'client'           => 'nullable|string|max:255',
            'location'         => 'nullable|string|max:255',
            'project_date'     => 'nullable|date|before_or_equal:today',
            'area'             => 'nullable|numeric|min:0|max:999999.99',
            'status'           => 'required|in:draft,published,archived',
            'is_featured'      => 'sometimes|boolean',
            'sort_order'       => 'nullable|integer|min:0',
            'category_id'      => 'required|exists:categories,id',
            'tags'             => 'nullable|string|max:500',
            'featured_image'   => 'nullable|image|max:2048',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Slug auto se mancante
        if (empty($this->slug) && !empty($this->title)) {
            $this->merge(['slug' => Str::slug($this->title)]);
        }

        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }
    }
}
