<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'slug')->ignore($project->id)
            ],
            'description' => 'required|string|max:1000',
            'long_description' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'project_date' => 'nullable|date|before_or_equal:today',
            'area' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Il titolo del progetto è obbligatorio.',
            'title.max' => 'Il titolo non può superare i 255 caratteri.',
            'slug.required' => 'Lo slug è obbligatorio.',
            'slug.unique' => 'Questo slug è già utilizzato da un altro progetto.',
            'slug.max' => 'Lo slug non può superare i 255 caratteri.',
            'description.required' => 'La descrizione breve è obbligatoria.',
            'description.max' => 'La descrizione breve non può superare i 1000 caratteri.',
            'client.max' => 'Il nome del cliente non può superare i 255 caratteri.',
            'location.max' => 'La località non può superare i 255 caratteri.',
            'project_date.date' => 'Inserisci una data valida.',
            'project_date.before_or_equal' => 'La data del progetto non può essere nel futuro.',
            'area.numeric' => 'La superficie deve essere un numero.',
            'area.min' => 'La superficie non può essere negativa.',
            'area.max' => 'La superficie non può superare i 999.999,99 m².',
            'status.required' => 'Lo stato di pubblicazione è obbligatorio.',
            'status.in' => 'Lo stato selezionato non è valido.',
            'category_id.required' => 'La categoria è obbligatoria.',
            'category_id.exists' => 'La categoria selezionata non esiste.',
            'tags.max' => 'I tags non possono superare i 500 caratteri.',
            'sort_order.integer' => 'L\'ordine deve essere un numero intero.',
            'sort_order.min' => 'L\'ordine non può essere negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'titolo',
            'slug' => 'slug',
            'description' => 'descrizione breve',
            'long_description' => 'descrizione dettagliata',
            'client' => 'cliente',
            'location' => 'località',
            'project_date' => 'data progetto',
            'area' => 'superficie',
            'status' => 'stato',
            'is_featured' => 'in evidenza',
            'sort_order' => 'ordine',
            'category_id' => 'categoria',
            'tags' => 'tags',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-genera slug se vuoto (solo se diverso da quello attuale)
        if (empty($this->slug) && !empty($this->title)) {
            $this->merge([
                'slug' => Str::slug($this->title)
            ]);
        }

        // Normalizza i campi
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'sort_order' => $this->integer('sort_order', 0),
            'title' => $this->string('title')->trim(),
            'slug' => $this->filled('slug') ? Str::slug($this->slug) : null,
            'client' => $this->filled('client') ? $this->string('client')->trim() : null,
            'location' => $this->filled('location') ? $this->string('location')->trim() : null,
            'description' => $this->string('description')->trim(),
            'long_description' => $this->filled('long_description') ? $this->string('long_description')->trim() : null,
        ]);

        // Converti tags da stringa a array JSON
        if ($this->filled('tags')) {
            $tags = array_map('trim', explode(',', $this->tags));
            $tags = array_filter($tags, fn($tag) => !empty($tag)); // Rimuovi tag vuoti
            $tags = array_unique($tags); // Rimuovi duplicati
            $this->merge([
                'tags' => !empty($tags) ? $tags : null
            ]);
        } else {
            $this->merge(['tags' => null]);
        }
    }
}
