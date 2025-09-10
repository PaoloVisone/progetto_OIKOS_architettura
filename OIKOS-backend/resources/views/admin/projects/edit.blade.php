@extends('layouts.admin')

@section('title', 'Modifica Progetto')

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
          <a href="{{ route('admin.projects.index') }}" class="hover:text-gray-700">Progetti</a>
          <span>/</span>
          <a href="{{ route('admin.projects.show', $project) }}" class="hover:text-gray-700">{{ $project->title }}</a>
          <span>/</span>
          <span class="text-gray-900">Modifica</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Modifica Progetto</h1>
        <p class="mt-1 text-sm text-gray-600">Aggiorna i dettagli del progetto</p>
      </div>
      <div class="flex items-center space-x-3">
        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary btn-sm">
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Torna al progetto
        </a>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg">
      <form action="{{ route('admin.projects.update', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
        @csrf
        @method('PUT')

        <!-- Title -->
        <div>
          <label for="title" class="form-label">Titolo *</label>
          <input type="text" name="title" id="title"
                 value="{{ old('title', $project->title) }}" required
                 class="form-input @error('title') form-input-error @enderror">
          @error('title') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <!-- Slug -->
        <div>
          <label for="slug" class="form-label">
            Slug <span class="text-gray-500">(lascia vuoto per rigenerarlo dal titolo)</span>
          </label>
          <div class="mt-1 flex rounded-md shadow-sm">
            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              {{ url('/') }}/progetti/
            </span>
            <input type="text" name="slug" id="slug"
                   value="{{ old('slug', $project->slug) }}"
                   class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-blue-500 focus:border-blue-500 border-gray-300 @error('slug') border-red-300 @enderror">
          </div>
          @error('slug') <p class="form-error">{{ $message }}</p> @enderror
          <p class="form-help">Solo minuscole, numeri e trattini.</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="form-label">Descrizione Breve</label>
          <textarea name="description" id="description" rows="3"
                    class="form-input @error('description') form-input-error @enderror"
                    placeholder="Anteprima nelle liste...">{{ old('description', $project->description) }}</textarea>
          @error('description') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <!-- Long Description -->
        <div>
          <label for="long_description" class="form-label">Descrizione Estesa</label>
          <textarea name="long_description" id="long_description" rows="10"
                    class="form-input @error('long_description') form-input-error @enderror"
                    placeholder="Contenuto dettagliato...">{{ old('long_description', $project->long_description) }}</textarea>
          @error('long_description') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <!-- Status -->
          <div>
            <label for="status" class="form-label">Stato *</label>
            <select name="status" id="status" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('status') border-red-300 @enderror">
              <option value="draft" {{ old('status', $project->status) === 'draft' ? 'selected' : '' }}>🟡 Bozza</option>
              <option value="published" {{ old('status', $project->status) === 'published' ? 'selected' : '' }}>🟢 Pubblicato</option>
              <option value="archived" {{ old('status', $project->status) === 'archived' ? 'selected' : '' }}>⚪ Archiviato</option>
            </select>
            @error('status') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <!-- Featured -->
          <div class="flex items-start">
            <div class="flex items-center h-5 mt-6">
              <input type="checkbox" name="is_featured" id="is_featured" value="1"
                     {{ old('is_featured', $project->is_featured) ? 'checked' : '' }}
                     class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
            </div>
            <div class="ml-3 text-sm mt-6">
              <label for="is_featured" class="font-medium text-gray-700">⭐ In evidenza</label>
              <p class="text-gray-500">Mostra questo progetto nella sezione in evidenza</p>
            </div>
          </div>

          <!-- Client -->
          <div>
            <label for="client" class="form-label">Cliente</label>
            <input type="text" name="client" id="client" value="{{ old('client', $project->client) }}"
                   class="form-input @error('client') form-input-error @enderror">
            @error('client') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <!-- Location -->
          <div>
            <label for="location" class="form-label">Località</label>
            <input type="text" name="location" id="location" value="{{ old('location', $project->location) }}"
                   class="form-input @error('location') form-input-error @enderror">
            @error('location') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <!-- Project Date -->
          <div>
            <label for="project_date" class="form-label">Data del Progetto</label>
            <input type="date" name="project_date" id="project_date"
                   value="{{ old('project_date', optional($project->project_date)->format('Y-m-d')) }}"
                   class="form-input @error('project_date') form-input-error @enderror">
            @error('project_date') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <!-- Area -->
          <div>
            <label for="area" class="form-label">Area (mq)</label>
            <input type="number" step="0.01" name="area" id="area"
                   value="{{ old('area', $project->area) }}"
                   class="form-input @error('area') form-input-error @enderror">
            @error('area') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <!-- Sort Order -->
          <div>
            <label for="sort_order" class="form-label">Ordine</label>
            <input type="number" name="sort_order" id="sort_order"
                   value="{{ old('sort_order', $project->sort_order) }}"
                   class="form-input @error('sort_order') form-input-error @enderror">
            @error('sort_order') <p class="form-error">{{ $message }}</p> @enderror
          </div>

          <!-- Category -->
          <div class="sm:col-span-2">
            <label for="category_id" class="form-label">Categoria *</label>
            <select name="category_id" id="category_id" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('category_id') border-red-300 @enderror">
              <option value="">-- Seleziona categoria --</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ (string)old('category_id', $project->category_id) === (string)$category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
            @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Tags -->
        <div>
          <label for="tags" class="form-label">Tags</label>
          <input type="text" name="tags" id="tags"
                 value="{{ old('tags', is_array($project->tags) ? implode(', ', $project->tags) : '') }}"
                 placeholder="es. design, architettura, interni"
                 class="form-input @error('tags') form-input-error @enderror">
          <p class="form-help">Separati da virgola</p>
          @error('tags') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <!-- Featured Image -->
        <div>
          <label for="featured_image" class="form-label">Immagine in evidenza</label>
          <input type="file" name="featured_image" id="featured_image" accept="image/*"
                 class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('featured_image') border-red-300 @enderror">
          @error('featured_image') <p class="form-error">{{ $message }}</p> @enderror

          @if($project->featured_image)
            <div class="mt-2">
              <img src="{{ Storage::url($project->featured_image) }}" alt="Featured" class="h-24 w-24 rounded object-cover">
            </div>
          @endif
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-6 border-t">
          <div class="flex items-center space-x-4">
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary">
              <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Annulla
            </a>
            <a href="{{ route('admin.projects.media.index', $project) }}" class="btn btn-secondary">
              <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"/>
              </svg>
              Gestisci Media
            </a>
          </div>

          <div class="flex items-center space-x-3">
            <button type="submit" name="status" value="draft" class="btn btn-secondary">Salva Bozza</button>
            <button type="submit" class="btn btn-primary">Aggiorna Progetto</button>
          </div>
        </div>
      </form>
    </div>

    <div class="mt-6 bg-gray-50 rounded-lg p-4">
      <h3 class="text-sm font-medium text-gray-900 mb-2">👀 Anteprima URL</h3>
      <div class="text-sm text-gray-600">
        <span class="font-mono bg-white px-2 py-1 rounded border">
          {{ url('/progetti') }}/<span id="slugPreview">{{ old('slug', $project->slug) }}</span>
        </span>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const titleInput = document.getElementById('title');
  const slugInput = document.getElementById('slug');
  const slugPreview = document.getElementById('slugPreview');
  let slugManuallyEdited = !!slugInput.value;

  titleInput.addEventListener('input', function(e) {
    if (!slugManuallyEdited) {
      const slug = generateSlug(e.target.value);
      slugInput.value = slug; slugPreview.textContent = slug || 'progetto-slug';
    }
  });

  slugInput.addEventListener('input', function(e) {
    slugManuallyEdited = true;
    slugPreview.textContent = e.target.value || 'progetto-slug';
  });

  function generateSlug(str) {
    return (str || '')
      .toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }
});
</script>
@endpush
@endsection
