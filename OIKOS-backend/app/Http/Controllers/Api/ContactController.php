<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts (Admin only).
     * GET /api/contacts
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Contact::query();

            // Filtri
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->boolean('unread_only')) {
                $query->unread();
            }

            if ($request->boolean('new_only')) {
                $query->new();
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('subject', 'LIKE', "%{$search}%")
                        ->orWhere('message', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }

            // Ordinamento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $allowedSorts = ['created_at', 'name', 'subject', 'status'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->recent();
            }

            // Paginazione
            $perPage = min($request->get('per_page', 20), 50);
            $contacts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ContactResource::collection($contacts->items()),
                'pagination' => [
                    'current_page' => $contacts->currentPage(),
                    'per_page' => $contacts->perPage(),
                    'total' => $contacts->total(),
                    'last_page' => $contacts->lastPage(),
                    'from' => $contacts->firstItem(),
                    'to' => $contacts->lastItem(),
                ],
                'stats' => [
                    'total' => Contact::count(),
                    'new' => Contact::new()->count(),
                    'unread' => Contact::unread()->count(),
                    'replied' => Contact::where('status', 'replied')->count(),
                ],
                'filters' => [
                    'status' => $request->status,
                    'unread_only' => $request->boolean('unread_only'),
                    'search' => $request->search,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei messaggi',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a new contact message (Public).
     * POST /api/contacts
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            // Rate limiting semplice per IP
            $recentMessages = Contact::where('email', $request->email)
                ->where('created_at', '>', now()->subHours(1))
                ->count();

            if ($recentMessages >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Troppi messaggi inviati di recente. Riprova tra un\'ora.',
                ], 429);
            }

            $contact = Contact::create($request->validated());

            // TODO: Invia notifica email all'admin
            // $this->notifyAdmin($contact);

            return response()->json([
                'success' => true,
                'message' => 'Messaggio inviato con successo. Ti risponderemo al più presto!',
                'data' => [
                    'id' => $contact->id,
                    'reference_number' => 'MSG-' . str_pad($contact->id, 6, '0', STR_PAD_LEFT),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'invio del messaggio. Riprova più tardi.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display specific contact (Admin only).
     * GET /api/contacts/{contact}
     */
    public function show(Contact $contact): JsonResponse
    {
        try {
            // Marca come letto se non lo è già
            if (!$contact->is_read) {
                $contact->markAsRead();
            }

            return response()->json([
                'success' => true,
                'data' => new ContactResource($contact->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero del messaggio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update contact status/notes (Admin only).
     * PATCH /api/contacts/{contact}
     */
    public function update(Request $request, Contact $contact): JsonResponse
    {
        $request->validate([
            'status' => 'in:new,read,replied,archived',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $updateData = [];

            if ($request->has('status')) {
                $updateData['status'] = $request->status;

                // Aggiorna timestamp in base al nuovo stato
                if ($request->status === 'replied' && !$contact->replied_at) {
                    $updateData['replied_at'] = now();
                }
            }

            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            if (!empty($updateData)) {
                $contact->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Messaggio aggiornato con successo',
                'data' => new ContactResource($contact->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento del messaggio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Delete contact (Admin only).
     * DELETE /api/contacts/{contact}
     */
    public function destroy(Contact $contact): JsonResponse
    {
        try {
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Messaggio eliminato con successo'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'eliminazione del messaggio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark contact as read.
     * PATCH /api/contacts/{contact}/read
     */
    public function markAsRead(Contact $contact): JsonResponse
    {
        try {
            $contact->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Messaggio marcato come letto',
                'data' => [
                    'id' => $contact->id,
                    'status' => $contact->status,
                    'read_at' => $contact->read_at?->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento del messaggio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark contact as replied.
     * PATCH /api/contacts/{contact}/replied
     */
    public function markAsReplied(Contact $contact): JsonResponse
    {
        try {
            $contact->markAsReplied();

            return response()->json([
                'success' => true,
                'message' => 'Messaggio marcato come risposto',
                'data' => [
                    'id' => $contact->id,
                    'status' => $contact->status,
                    'replied_at' => $contact->replied_at?->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento del messaggio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk update contacts.
     * POST /api/contacts/bulk-update
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
            'action' => 'required|in:mark_read,mark_replied,archive,delete',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $contacts = Contact::whereIn('id', $request->contact_ids);
            $count = $contacts->count();

            switch ($request->action) {
                case 'mark_read':
                    $contacts->update([
                        'status' => 'read',
                        'read_at' => now()
                    ]);
                    $message = "{$count} messaggi marcati come letti";
                    break;

                case 'mark_replied':
                    $contacts->update([
                        'status' => 'replied',
                        'replied_at' => now()
                    ]);
                    $message = "{$count} messaggi marcati come risposti";
                    break;

                case 'archive':
                    $contacts->update(['status' => 'archived']);
                    $message = "{$count} messaggi archiviati";
                    break;

                case 'delete':
                    $contacts->delete();
                    $message = "{$count} messaggi eliminati";
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'operazione bulk',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get contact statistics.
     * GET /api/contacts/stats
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Contact::count(),
                'today' => Contact::whereDate('created_at', today())->count(),
                'this_week' => Contact::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => Contact::whereMonth('created_at', now()->month)->count(),
                'by_status' => [
                    'new' => Contact::where('status', 'new')->count(),
                    'read' => Contact::where('status', 'read')->count(),
                    'replied' => Contact::where('status', 'replied')->count(),
                    'archived' => Contact::where('status', 'archived')->count(),
                ],
                'unread' => Contact::unread()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle statistiche',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
