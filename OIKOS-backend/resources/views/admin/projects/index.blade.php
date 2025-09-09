@extends('layouts.admin')

@section('title', 'Progetti')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Progetti</h1>
                <p class="mt-1 text-sm text-gray-600">Gestisci tutti i tuoi progetti</p>
            </div>
            <a href="{{ route('admin.projects.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nuovo Progetto
            </a>
        </div>

        <!-- Projects Grid -->
        @if($projects->count() > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($projects as $project)
                        <li>
                            <div class="px-4 py-4 flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    <!-- Project Image -->
                                    <div class="flex-shrink-0">
                                        @if($project->media->where('is_featured', true)->first())
                                            <img class="h-16 w-16 rounded-lg object-cover" 
                                                 src="{{ Storage::url($project->media->where('is_featured', true)->first()->thumbnail_path) }}" 
                                                 alt="{{ $project->title }}">
                                        @else
                                            <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Project Info -->
                                    <div class="ml-4 min-w-0 flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h3 class="text-lg font-medium text-gray-900 truncate">
                                                {{ $project->title }}
                                            </h3>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($project->status === 'published') bg-green-100 text-green-800 @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $project->status === 'published' ? 'Pubblicato' : 'Bozza' }}
                                            </span>
                                            @if($project->featured)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    In evidenza
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ $project->media_count }} media</span>
                                            <span>•</span>
                                            <span>Creato {{ $project->created_at->diffForHumans() }}</span>
                                            <span>•</span>
                                            <span>Modificato {{ $project->updated_at->diffForHumans() }}</span>
                                        </div>
                                        @if($project->description)
                                            <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                                                {{ Str::limit($project->description, 100) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    <!-- Toggle Status -->
                                    <form action="{{ route('admin.projects.toggle-status', $project) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            @if($project->status === 'published')
                                                <svg class="-ml-0.5 mr-2 h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                                Bozza
                                            @else
                                                <svg class="-ml-0.5 mr-2 h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Pubblica
                                            @endif
                                        </button>
                                    </form>

                                    <!-- View -->
                                    <a href="{{ route('admin.projects.show', $project) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Visualizza
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('admin.projects.edit', $project) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Modifica
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Sei sicuro di voler eliminare questo progetto? Questa azione non può essere annullata.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Elimina
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $projects->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun progetto</h3>
                <p class="mt-1 text-sm text-gray-500">Inizia creando il tuo primo progetto.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.projects.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Crea il tuo primo progetto
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection