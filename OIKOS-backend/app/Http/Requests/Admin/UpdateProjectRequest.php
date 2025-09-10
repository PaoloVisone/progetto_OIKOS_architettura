<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Valuta policy/ruolo qui se necessario
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project'); // model binding {project}

        return [
            'title'            => 'required|string|max:255',
            'slug'             => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('projects', 'slug')->ignore($project?->id),
            ],
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
