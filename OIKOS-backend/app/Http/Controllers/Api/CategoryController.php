<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     * GET /api/categories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::query();

            // Include conteggio progetti se richiesto
            if ($request->boolean('with_count')) {
                $query->withCount('projects');
            }

            // Include progetti se richiesto
            if ($request->boolean('with_projects')) {
                $query->with(['projects' => function ($q) use ($request) {
                    $q->published()->recent();
                    if ($request->has('projects_limit')) {
                        $q->limit($request->projects_limit);
                    }
                }]);
            }

            // Filtri
            if ($request->has('active_only')) {
                $query->active();
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Ordinamento
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');

            if ($sortBy === 'projects_count') {
                $query->withCount('projects')->orderBy('projects_count', $sortDirection);
            } else {
                $query->ordered();
            }

            // Paginazione o tutti
            if ($request->boolean('paginate')) {
                $perPage = min($request->get('per_page', 15), 50);
                $categories = $query->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => CategoryResource::collection($categories->items()),
                    'pagination' => [
                        'current_page' => $categories->currentPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'last_page' => $categories->lastPage(),
                    ]
                ]);
            } else {
                $categories = $query->get();

                return response()->json([
                    'success' => true,
                    'data' => CategoryResource::collection($categories)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle categorie',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created category.
     * POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->get('sort_order', 0),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria creata con successo',
                'data' => new CategoryResource($category)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione della categoria',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified category.
     * GET /api/categories/{category}
     */
    public function show(Request $request, $identifier): JsonResponse
    {
        try {
            $query = Category::query();

            // Include progetti se richiesto
            if ($request->boolean('with_projects')) {
                $query->with(['projects' => function ($q) use ($request) {
                    // Solo progetti pubblicati per utenti non admin
                    if (!$request->user() || !$request->user()->isAdmin()) {
                        $q->published();
                    }

                    $q->recent();

                    if ($request->has('projects_limit')) {
                        $q->limit($request->projects_limit);
                    }
                }]);
            }

            // Trova per ID o slug
            $category = $query->where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria non trovata'
                ], 404);
            }

            // Se richiesta pubblica, verifica che sia attiva
            if ((!$request->user() || !$request->user()->isAdmin()) && !$category->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria non disponibile'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new CategoryResource($category)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero della categoria',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified category.
     * PUT/PATCH /api/categories/{category}
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)
            ],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'sort_order' => $request->get('sort_order', $category->sort_order),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria aggiornata con successo',
                'data' => new CategoryResource($category->fresh())
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento della categoria',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     * DELETE /api/categories/{category}
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            // Controlla se ci sono progetti associati
            $projectsCount = $category->projects()->count();

            if ($projectsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossibile eliminare la categoria. Ci sono {$projectsCount} progetti associati.",
                    'projects_count' => $projectsCount
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoria eliminata con successo'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'eliminazione della categoria',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Toggle category active status.
     * PATCH /api/categories/{category}/toggle-status
     */
    public function toggleStatus(Category $category): JsonResponse
    {
        try {
            $category->update(['is_active' => !$category->is_active]);

            $message = $category->is_active
                ? 'Categoria attivata con successo'
                : 'Categoria disattivata con successo';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $category->id,
                    'is_active' => $category->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel cambio di stato',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reorder categories.
     * POST /api/categories/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->categories as $categoryData) {
                Category::where('id', $categoryData['id'])
                    ->update(['sort_order' => $categoryData['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordine delle categorie aggiornato con successo'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento dell\'ordine',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
