@extends('layouts.admin')

@section('title', 'Crea Progetto')

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Crea Nuovo Progetto</h1>
        <p class="mt-1 text-sm text-gray-600">Compila i dettagli del progetto</p>
      </div>
      <a href="{{ route('admin.projects.index') }}"
         class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Torna indietro
      </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
      <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
        @csrf

        <!-- Title -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700">Titolo *</label>
          <input type="text" name="title" id="title" value="{{ old('title') }}" required
                 class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-300 @enderror">
          @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Slug -->
        <div>
          <label for="slug" class="block text-sm font-medium text-gray-700">
            Slug <span class="text-gray-500">(verrà generato automaticamente se vuoto)</span>
          </label>
          <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                 class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-300 @enderror">
          @error('slug') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Descrizione Breve</label>
          <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
          @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Long Description -->
        <div>
          <label for="long_description" class="block text-sm font-medium text-gray-700">Descrizione Estesa</label>
          <textarea name="long_description" id="long_description" rows="6"
                    class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('long_description') border-red-300 @enderror">{{ old('long_description') }}</textarea>
          @error('long_description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Content (se usi un campo separato, altrimenti rimuovi) -->
        <div>
          <label for="content" class="block text-sm font-medium text-gray-700">Contenuto Completo</label>
          <textarea name="content" id="content" rows="10"
                    class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('content') border-red-300 @enderror">{{ old('content') }}</textarea>
          @error('content') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Stato *</label>
            <select name="status" id="status" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 sm:text-sm rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-300 @enderror">
              <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Bozza</option>
              <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Pubblicato</option>
              <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archiviato</option>
            </select>
            @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <!-- Featured -->
          <div class="flex items-start">
            <div class="flex items-center h-5 mt-6">
              <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                     class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            </div>
            <div class="ml-3 text-sm mt-6">
              <label for="is_featured" class="font-medium text-gray-700">Progetto in evidenza</label>
              <p class="text-gray-500">Mostra questo progetto nella sezione in evidenza</p>
            </div>
          </div>

          <!-- Client -->
          <div>
            <label for="client" class="block text-sm font-medium text-gray-700">Cliente</label>
            <input type="text" name="client" id="client" value="{{ old('client') }}"
                   class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('client') border-red-300 @enderror">
            @error('client') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <!-- Location -->
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Località</label>
            <input type="text" name="location" id="location" value="{{ old('location') }}"
                   class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('location') border-red-300 @enderror">
            @error('location') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <!-- Project Date -->
          <div>
            <label for="project_date" class="block text-sm font-medium text-gray-700">Data del Progetto</label>
            <input type="date" name="project_date" id="project_date" value="{{ old('project_date') }}"
                   class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('project_date') border-red-300 @enderror">
            @error('project_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <!-- Area -->
          <div>
            <label for="area" class="block text-sm font-medium text-gray-700">Area (mq)</label>
            <input type="number" step="0.01" name="area" id="area" value="{{ old('area') }}"
                   class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('area') border-red-300 @enderror">
            @error('area') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <!-- Sort Order -->
          <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordine</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                   class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('sort_order') border-red-300 @enderror">
            @error('sort_order') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <!-- Category -->
          <div class="sm:col-span-2">
            <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria *</label>
            <select name="category_id" id="category_id" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 sm:text-sm rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-300 @enderror">
              <option value="">-- Seleziona categoria --</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
            @error('category_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Tags -->
        <div>
          <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
          <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
                 placeholder="es. design, architettura, interni"
                 class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('tags') border-red-300 @enderror">
          <p class="mt-1 text-xs text-gray-500">Separati da virgola</p>
          @error('tags') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Featured Image -->
        <div>
          <label for="featured_image" class="block text-sm font-medium text-gray-700">Immagine in evidenza</label>
          <input type="file" name="featured_image" id="featured_image" accept="image/*"
                 class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('featured_image') border-red-300 @enderror">
          @error('featured_image') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- SEO -->
        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">SEO</h3>
          <div class="mb-4">
            <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" maxlength="255"
                   class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('meta_title') border-red-300 @enderror">
            @error('meta_title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div>
            <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
            <textarea name="meta_description" id="meta_description" rows="2" maxlength="255"
                      class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('meta_description') border-red-300 @enderror">{{ old('meta_description') }}</textarea>
            @error('meta_description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
          <a href="{{ route('admin.projects.index') }}"
             class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Annulla
          </a>
          <button type="submit"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Crea Progetto
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Auto-generate slug from title
  document.getElementById('title').addEventListener('input', function (e) {
    const title = e.target.value;
    const slugField = document.getElementById('slug');
    if (slugField && !slugField.dataset.manual) {
      const slug = title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');
      slugField.value = slug;
    }
  });

  // Mark slug as manually edited
  document.getElementById('slug').addEventListener('input', function (e) {
    e.target.dataset.manual = 'true';
  });
</script>
@endpush
@endsection
