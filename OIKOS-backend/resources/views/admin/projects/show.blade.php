@extends('layouts.admin')

@section('title', $project->title)

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('admin.projects.index') }}" class="hover:text-gray-700">Progetti</a>
                    <span>/</span>
                    <span class="text-gray-900">{{ $project->title }}</span>
                </nav>

                <div class="flex items-center flex-wrap gap-2">
                    <div class="flex items-center space-x-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $project->title }}</h1>

                        @php
                            $isFeatured = $project->is_featured ?? $project->featured ?? false;
                        @endphp
    
                        <span class="badge
                            @if($project->status === 'published') badge-success
                            @elseif($project->status === 'archived') badge-warning
                            @else badge-warning @endif">
                            {{ $project->status === 'published' ? 'Pubblicato' : ($project->status === 'archived' ? 'Archiviato' : 'Bozza') }}
                        </span>
    
                        @if($isFeatured)
                            <span class="badge badge-info">In evidenza</span>
                        @endif
    
                        @if(optional($project->category)->name)
                            <span class="badge">{{ $project->category->name }}</span>
                        @endif>
                 </div>
                </div>

                <p class="mt-1 text-sm text-gray-600">
                    Creato {{ $project->created_at->format('d/m/Y H:i') }} •
                    Aggiornato {{ $project->updated_at->diffForHumans() }}
                </p>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Toggle Status -->
                <form action="{{ route('admin.projects.toggle-status', $project) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-secondary btn-sm">
                        @if($project->status === 'published')
                            <svg class="-ml-1 mr-2 h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            Salva come bozza
                        @else
                            <svg class="-ml-1 mr-2 h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pubblica
                        @endif
                    </button>
                </form>

                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-secondary btn-sm">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Modifica
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Project Details -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium text-gray-900">Dettagli Progetto</h2>
                    </div>
                    <div class="card-body">
                        <!-- Slug -->
                        <div class="mb-4">
                            <label class="form-label">Slug</label>
                            <p class="text-sm text-gray-600 font-mono bg-gray-50 px-3 py-2 rounded">{{ $project->slug }}</p>
                        </div>

                        <!-- Descrizione breve -->
                        @if($project->description)
                            <div class="mb-4">
                                <label class="form-label">Descrizione</label>
                                <p class="text-sm text-gray-700">{{ $project->description }}</p>
                            </div>
                        @endif

                        <!-- Contenuto lungo -->
                        @if(!empty($project->long_description ?? $project->content))
                            <div class="mb-4">
                                <label class="form-label">Contenuto</label>
                                <div class="prose prose-sm max-w-none text-gray-700">
                                    {!! nl2br(e($project->long_description ?? $project->content)) !!}
                                </div>
                            </div>
                        @endif

                        <!-- Metadati dalla tabella projects -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-4">
                            @if(optional($project->category)->name)
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Categoria</label>
                                    <p class="text-sm text-gray-900">{{ $project->category->name }}</p>
                                </div>
                            @endif

                            @if($project->client)
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Cliente</label>
                                    <p class="text-sm text-gray-900">{{ $project->client }}</p>
                                </div>
                            @endif

                            @if($project->location)
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Luogo</label>
                                    <p class="text-sm text-gray-900">{{ $project->location }}</p>
                                </div>
                            @endif

                            @if($project->project_date)
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Data Progetto</label>
                                    <p class="text-sm text-gray-900">
                                        {{ \Illuminate\Support\Carbon::parse($project->project_date)->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif

                            @if(!is_null($project->area))
                                <div>
                                    <label class="text-xs font-medium text-gray-500">Area</label>
                                    <p class="text-sm text-gray-900">{{ number_format($project->area, 2, ',', '.') }} m²</p>
                                </div>
                            @endif

                            <div>
                                <label class="text-xs font-medium text-gray-500">Ordinamento</label>
                                <p class="text-sm text-gray-900">{{ $project->sort_order }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">In evidenza</label>
                                <p class="text-sm text-gray-900">{{ $isFeatured ? 'Sì' : 'No' }}</p>
                            </div>

                            @php
                                $tags = is_array($project->tags)
                                    ? $project->tags
                                    : (empty($project->tags) ? [] : (json_decode($project->tags, true) ?: []));
                            @endphp
                            @if(!empty($tags))
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-medium text-gray-500">Tag</label>
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        @foreach($tags as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($project->featured_image)
                            <div class="border-t pt-4 mt-4">
                                <label class="text-xs font-medium text-gray-500 block mb-2">Immagine di copertina</label>
                                <img src="{{ Storage::url($project->featured_image) }}" alt="Featured image"
                                     class="rounded-lg border max-h-64 object-cover">
                            </div>
                        @endif

                        <!-- SEO (se li usi) -->
                        @if($project->meta_title || $project->meta_description)
                            <div class="border-t pt-4 mt-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-3">SEO</h3>

                                @if($project->meta_title)
                                    <div class="mb-2">
                                        <label class="text-xs font-medium text-gray-500">Meta Title</label>
                                        <p class="text-sm text-gray-700">{{ $project->meta_title }}</p>
                                    </div>
                                @endif

                                @if($project->meta_description)
                                    <div>
                                        <label class="text-xs font-medium text-gray-500">Meta Description</label>
                                        <p class="text-sm text-gray-700">{{ $project->meta_description }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Media Section -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">
                            Media ({{ $project->media->count() }})
                        </h2>
                         <a href="{{ route('admin.projects.media.index', $project) }}" 
                           class="btn btn-primary btn-sm">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Gestisci Media
                        </a>
                    </div>
                    <div class="card-body">
                        @if($project->media->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                @foreach($project->media->take(8) as $media)
                                    <div class="relative aspect-square group">
                                        @if($media->type === 'image')
                                            <img src="{{ Storage::url($media->thumbnail_path ?? $media->path) }}"
                                                 alt="{{ $media->alt_text }}"
                                                 class="w-full h-full object-cover rounded-lg">
                                        @else
                                            <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif

                                        @if($media->is_featured)
                                            <div class="absolute top-2 left-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Evidenza
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($project->media->count() > 8)
                                <div class="mt-4 text-center">
                                    <a href="{{ route('admin.projects.media.index', $project) }}"
                                       class="text-sm text-blue-600 hover:text-blue-500">
                                        Visualizza tutti i {{ $project->media->count() }} media →
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun media</h3>
                                <p class="mt-1 text-sm text-gray-500">Inizia caricando immagini o video.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.projects.media.index', $project) }}" class="btn btn-primary btn-sm">
                                        Carica Media
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-900">Azioni</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <a href="{{ route('admin.projects.edit', $project) }}" 
                           class="btn btn-secondary w-full justify-center">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Modifica Progetto
                        </a>

                         <a href="{{ route('admin.projects.media.index', $project) }}" 
                           class="btn btn-secondary w-full justify-center">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Gestisci Media
                        </a>

                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST"
                              onsubmit="return confirm('Sei sicuro di voler eliminare questo progetto? Questa azione non può essere annullata.')">
                            @csrf
                             @method('DELETE')
                            <button type="submit" class="btn btn-danger w-full justify-center">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Elimina Progetto
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Stats -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-medium text-gray-900">Statistiche</h3>
                    </div>
                    <div class="card-body">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Media totali</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $project->media->count() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Immagini</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $project->media->where('type', 'image')->count() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Video</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $project->media->where('type', 'video')->count() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Dimensione totale</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ number_format($project->media->sum('file_size') / 1024 / 1024, 1) }} MB
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
