<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     * GET /api/projects
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Project::with(['category', 'media', 'images', 'videos']);

            // Filtri
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('category_slug')) {
                $query->byCategory($request->category_slug);
            }

            if ($request->has('featured')) {
                $query->where('is_featured', $request->boolean('featured'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('client', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('tag')) {
                $query->withTag($request->tag);
            }

            // Ordinamento
            $sortBy = $request->get('sort_by', 'project_date');
            $sortDirection = $request->get('sort_direction', 'desc');

            $allowedSorts = ['project_date', 'created_at', 'title', 'sort_order'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginazione
            $perPage = min($request->get('per_page', 12), 50); // Max 50 per page
            $projects = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects->items()),
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'last_page' => $projects->lastPage(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ],
                'filters' => [
                    'status' => $request->status,
                    'category_slug' => $request->category_slug,
                    'featured' => $request->boolean('featured'),
                    'search' => $request->search,
                    'tag' => $request->tag,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei progetti',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created project.
     * POST /api/projects
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Crea il progetto
            $project = Project::create($request->validated());

            // Carica le relazioni
            $project->load(['category', 'media']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progetto creato con successo',
                'data' => new ProjectResource($project)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione del progetto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified project.
     * GET /api/projects/{project}
     */
    public function show(Request $request, $identifier): JsonResponse
    {
        try {
            // Trova il progetto per ID o slug
            $project = Project::with(['category', 'media', 'images', 'videos'])
                ->where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progetto non trovato'
                ], 404);
            }

            // Se la richiesta è pubblica, mostra solo progetti pubblicati
            if (!$request->user() || !$request->user()->isAdmin()) {
                if ($project->status !== 'published') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Progetto non disponibile'
                    ], 404);
                }
            }

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero del progetto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified project.
     * PUT/PATCH /api/projects/{project}
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Aggiorna il progetto
            $project->update($request->validated());

            // Ricarica le relazioni
            $project->load(['category', 'media']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progetto aggiornato con successo',
                'data' => new ProjectResource($project)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento del progetto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified project.
     * DELETE /api/projects/{project}
     */
    public function destroy(Project $project): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Elimina tutti i media associati (i file vengono eliminati nel model observer)
            $project->media()->delete();

            // Elimina il progetto
            $project->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progetto eliminato con successo'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'eliminazione del progetto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get featured projects.
     * GET /api/projects/featured
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 6), 12); // Max 12 featured projects

            $projects = Project::with(['category', 'media'])
                ->published()
                ->featured()
                ->recent()
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei progetti in evidenza',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update project status.
     * PATCH /api/projects/{project}/status
     */
    public function updateStatus(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        try {
            $project->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => "Stato del progetto aggiornato a '{$request->status}'",
                'data' => [
                    'id' => $project->id,
                    'status' => $project->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento dello stato',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle featured status.
     * PATCH /api/projects/{project}/featured
     */
    public function toggleFeatured(Project $project): JsonResponse
    {
        try {
            $project->update(['is_featured' => !$project->is_featured]);

            $message = $project->is_featured
                ? 'Progetto aggiunto ai progetti in evidenza'
                : 'Progetto rimosso dai progetti in evidenza';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $project->id,
                    'is_featured' => $project->is_featured
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento dello stato',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
