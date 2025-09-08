<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Mostra tutti i media di un progetto.
     */
    public function index(Project $project)
    {
        $media = $project->media()->orderBy('sort_order')->get();

        return response()->json([
            'media' => $media,
            'total' => $media->count()
        ]);
    }

    /**
     * Carica nuovi file media.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240',
        ]);

        $uploadedMedia = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('projects/' . $project->id, 'public');

            $media = $project->media()->create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => str_starts_with($file->getMimeType(), 'image') ? 'image' : 'video',
                'size' => $file->getSize(),
                'sort_order' => $project->media()->count()
            ]);

            $uploadedMedia[] = $media;
        }

        return response()->json([
            'message' => 'File caricati con successo',
            'media' => $uploadedMedia
        ], 201);
    }

    /**
     * Mostra un singolo media.
     */
    public function show(ProjectMedia $media)
    {
        return response()->json(['media' => $media]);
    }

    /**
     * Aggiorna le informazioni del media.
     */
    public function update(Request $request, ProjectMedia $media)
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
        ]);

        // Se viene impostato come featured, rimuovi da tutti gli altri
        if (isset($validated['is_featured']) && $validated['is_featured']) {
            $media->project->media()->update(['is_featured' => false]);
        }

        $media->update($validated);

        return response()->json([
            'message' => 'Media aggiornato',
            'media' => $media
        ]);
    }

    /**
     * Elimina un media.
     */
    public function destroy(ProjectMedia $media)
    {
        // Elimina il file fisico
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json(['message' => 'Media eliminato']);
    }

    /**
     * Cambia l'ordine dei media.
     */
    public function reorder(Request $request, Project $project)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:project_media,id'
        ]);

        foreach ($request->media_ids as $index => $mediaId) {
            $project->media()->where('id', $mediaId)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Ordine aggiornato']);
    }
}
