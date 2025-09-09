@extends('layouts.admin')

@section('title', 'Media - ' . $project->title)

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('admin.projects.index') }}" class="hover:text-gray-700">Progetti</a>
                    <span>/</span>
                    <a href="{{ route('admin.projects.show', $project) }}" class="hover:text-gray-700">{{ $project->title }}</a>
                    <span>/</span>
                    <span class="text-gray-900">Media</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Media - {{ $project->title }}</h1>
                <p class="mt-1 text-sm text-gray-600">Gestisci immagini e video del progetto</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.projects.show', $project) }}" 
                   class="btn btn-secondary btn-sm">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Torna al progetto
                </a>
                <button type="button" onclick="openUploadModal()" 
                        class="btn btn-primary btn-sm">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Carica Media
                </button>
            </div>
        </div>

        <!-- Media Grid -->
        @if($media->count() > 0)
            <div class="media-grid">
                @foreach($media as $mediaItem)
                    <div class="media-item" data-media-id="{{ $mediaItem->id }}">
                        <!-- Media Preview -->
                        <div class="aspect-square">
                            @if($mediaItem->type === 'image')
                                <img src="{{ Storage::url($mediaItem->thumbnail_path ?? $mediaItem->path) }}" 
                                     alt="{{ $mediaItem->alt_text }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Media Overlay -->
                        <div class="media-overlay">
                            <div class="media-actions">
                                <!-- Set Featured -->
                                @if(!$mediaItem->is_featured)
                                    <button type="button" onclick="setFeatured({{ $mediaItem->id }})"
                                            class="p-2 bg-white rounded-full shadow hover:bg-gray-50" 
                                            title="Imposta come evidenza">
                                        <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </button>
                                @endif

                                <!-- Edit -->
                                <button type="button" onclick="editMedia({{ $mediaItem->id }})"
                                        class="p-2 bg-white rounded-full shadow hover:bg-gray-50" 
                                        title="Modifica">
                                    <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <!-- Delete -->
                                <button type="button" onclick="deleteMedia({{ $mediaItem->id }})"
                                        class="p-2 bg-white rounded-full shadow hover:bg-red-50" 
                                        title="Elimina">
                                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Featured Badge -->
                        @if($mediaItem->is_featured)
                            <div class="absolute top-2 left-2">
                                <span class="badge badge-info">
                                    <svg class="-ml-0.5 mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    Evidenza
                                </span>
                            </div>
                        @endif

                        <!-- Media Info -->
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-2">
                            <p class="text-white text-xs truncate">{{ $mediaItem->original_name }}</p>
                            <p class="text-gray-300 text-xs">{{ ucfirst($mediaItem->type) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun media</h3>
                <p class="mt-1 text-sm text-gray-500">Inizia caricando immagini o video per questo progetto.</p>
                <div class="mt-6">
                    <button type="button" onclick="openUploadModal()" 
                            class="btn btn-primary">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Carica i tuoi primi media
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Carica Media</h3>
                <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Upload Form -->
            <form id="uploadForm" action="{{ route('admin.projects.media.store', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- File Drop Area -->
                <div id="dropArea" class="upload-area mb-4">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <div class="mt-4">
                            <label for="fileInput" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                    Clicca per caricare o trascina i file qui
                                </span>
                                <input id="fileInput" name="files[]" type="file" multiple 
                                       accept="image/*,video/*" class="hidden">
                            </label>
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG, WebP, GIF, MP4, MOV fino a 20MB ciascuno</p>
                        </div>
                    </div>
                </div>

                <!-- Selected Files -->
                <div id="selectedFiles" class="hidden mb-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">File selezionati:</h4>
                    <div id="filesList" class="space-y-2"></div>
                </div>

                <!-- Progress Bar -->
                <div id="uploadProgress" class="hidden mb-4">
                    <div class="progress-bar">
                        <div id="progressFill" class="progress-fill" style="width: 0%"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Caricamento in corso...</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUploadModal()" 
                            class="btn btn-secondary">
                        Annulla
                    </button>
                    <button type="submit" id="uploadBtn" disabled
                            class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        Carica Media
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedFiles = [];

// Modal functions
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    resetUploadForm();
}

function resetUploadForm() {
    selectedFiles = [];
    document.getElementById('fileInput').value = '';
    document.getElementById('selectedFiles').classList.add('hidden');
    document.getElementById('uploadProgress').classList.add('hidden');
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('filesList').innerHTML = '';
}

// File handling
document.getElementById('fileInput').addEventListener('change', handleFileSelect);

function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    selectedFiles = files;
    displaySelectedFiles();
}

function displaySelectedFiles() {
    const filesList = document.getElementById('filesList');
    const selectedFilesDiv = document.getElementById('selectedFiles');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (selectedFiles.length === 0) {
        selectedFilesDiv.classList.add('hidden');
        uploadBtn.disabled = true;
        return;
    }
    
    selectedFilesDiv.classList.remove('hidden');
    uploadBtn.disabled = false;
    
    filesList.innerHTML = selectedFiles.map((file, index) => `
        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm text-gray-900">${file.name}</span>
                <span class="text-xs text-gray-500">(${formatFileSize(file.size)})</span>
            </div>
            <button type="button" onclick="removeFile(${index})" 
                    class="text-red-500 hover:text-red-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `).join('');
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    
    // Update file input
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('fileInput').files = dt.files;
    
    displaySelectedFiles();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Upload form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const progressDiv = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    
    progressDiv.classList.remove('hidden');
    document.getElementById('uploadBtn').disabled = true;
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressFill.style.width = percentComplete + '%';
        }
    });
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + response.message);
                    document.getElementById('uploadBtn').disabled = false;
                    progressDiv.classList.add('hidden');
                }
            } else {
                alert('Errore nel caricamento');
                document.getElementById('uploadBtn').disabled = false;
                progressDiv.classList.add('hidden');
            }
        }
    };
    
    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    xhr.send(formData);
});

// Drag and drop
const dropArea = document.getElementById('dropArea');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropArea.classList.add('upload-area-active');
}

function unhighlight(e) {
    dropArea.classList.remove('upload-area-active');
}

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    document.getElementById('fileInput').files = files;
    handleFileSelect({ target: { files: files } });
}

// Media actions
function setFeatured(mediaId) {
    if (!confirm('Impostare questo media come immagine in evidenza?')) {
        return;
    }
    
    fetch(`/admin/media/${mediaId}/featured`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore nella richiesta');
    });
}

function editMedia(mediaId) {
    // TODO: Implementare modal di modifica
    alert('Funzionalità in arrivo');
}

function deleteMedia(mediaId) {
    if (!confirm('Sei sicuro di voler eliminare questo media? Questa azione non può essere annullata.')) {
        return;
    }
    
    fetch(`/admin/media/${mediaId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-media-id="${mediaId}"]`).remove();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore nella richiesta');
    });
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
    }
});

// Close modal on outside click
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});
</script>
@endpush
@endsection