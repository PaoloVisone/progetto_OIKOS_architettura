<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectMediaRequest;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Services\MediaProcessingService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private MediaProcessingService $mediaService
    ) {}

    /**
     * Display media for a project
     */
    public function index(Project $project)
    {
        $media = $project->media()->orderBy('sort_order')->get();

        return view('admin.projects.media.index', compact('project', 'media'));
    }

    /**
     * Store new media files
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,gif,mp4,mov,avi,webm|max:20480', // 20MB
            'alt_texts' => 'array',
            'alt_texts.*' => 'nullable|string|max:255',
            'descriptions' => 'array',
            'descriptions.*' => 'nullable|string|max:500',
            // (opzionale) types per ogni file: image|video
            'types' => 'array',
            'types.*' => 'in:image,video',
            // (opzionale) featured / sort
            'is_featured' => 'array',
            'is_featured.*' => 'boolean',
            'sort_orders' => 'array',
            'sort_orders.*' => 'integer|min:0',
        ]);

        try {
            $files = $request->file('files', []);
            $items = [];

            foreach ($files as $i => $file) {
                $items[] = [
                    'file'        => $file,
                    'type'        => $request->input("types.$i") ?? (
                        str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image'
                    ),
                    'alt'         => $request->input("alt_texts.$i"),
                    'description' => $request->input("descriptions.$i"),
                    'is_featured' => (bool) $request->input("is_featured.$i", false),
                    'sort_order'  => (int) ($request->input("sort_orders.$i", $i)),
                ];
            }

            //  (Project $project, array $items, string $disk='public')
            $uploadedMedia = $this->mediaService->processMultipleFiles($project, $items);

            $message = count($uploadedMedia) . ' file caricati con successo';

            if ($request->expectsJson()) {

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'media' => $uploadedMedia, // o ProjectMediaResource::collection(collect($uploadedMedia))
                ]);
            }

            return redirect()
                ->route('admin.projects.media.index', $project)
                ->with('success', $message);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore nel caricamento: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Errore nel caricamento: ' . $e->getMessage());
        }
    }

    /**
     * Update media information
     */
    public function update(Request $request, ProjectMedia $media)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
        ]);

        try {
            // Se sta impostando come featured, rimuovi featured dagli altri
            if ($request->boolean('is_featured') && !$media->is_featured) {
                $media->project->media()
                    ->where('id', '!=', $media->id)
                    ->update(['is_featured' => false]);
            }

            $media->update([
                'alt_text' => $request->alt_text,
                'description' => $request->description,
                'is_featured' => $request->boolean('is_featured'),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Media aggiornato con successo',
                    'media' => $media->fresh()
                ]);
            }

            return back()->with('success', 'Media aggiornato con successo');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore nell\'aggiornamento: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Errore nell\'aggiornamento: ' . $e->getMessage());
        }
    }

    /**
     * Delete media
     */
    public function destroy(ProjectMedia $media)
    {
        try {
            $projectId = $media->project_id;
            $this->mediaService->deleteMediaFiles($media);
            $media->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Media eliminato con successo'
                ]);
            }

            return redirect()
                ->route('admin.projects.media.index', $projectId)
                ->with('success', 'Media eliminato con successo');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore nell\'eliminazione: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Errore nell\'eliminazione: ' . $e->getMessage());
        }
    }

    /**
     * Set media as featured
     */
    public function setFeatured(ProjectMedia $media)
    {
        try {
            // Rimuovi featured dagli altri media del progetto
            $media->project->media()
                ->where('id', '!=', $media->id)
                ->update(['is_featured' => false]);

            // Imposta questo come featured
            $media->update(['is_featured' => true]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Media impostato come immagine in evidenza',
                    'media' => $media->fresh()
                ]);
            }

            return back()->with('success', 'Media impostato come immagine in evidenza');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }
}
