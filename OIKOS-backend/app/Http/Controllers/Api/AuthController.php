<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Login user and create token.
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        try {
            // Trova l'utente
            $user = User::where('email', $request->email)->first();

            // Verifica credenziali
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Le credenziali fornite non sono corrette.'],
                ]);
            }

            // Verifica se l'utente è attivo
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account disabilitato. Contatta l\'amministratore.',
                ], 403);
            }

            // Elimina i token esistenti se non è "ricordami"
            if (!$request->boolean('remember')) {
                $user->tokens()->delete();
            }

            // Crea un nuovo token
            $tokenName = 'api-token-' . now()->timestamp;
            $abilities = $this->getUserAbilities($user);
            $token = $user->createToken($tokenName, $abilities);

            // Aggiorna ultimo login
            $user->updateLastLogin();

            return response()->json([
                'success' => true,
                'message' => 'Login effettuato con successo',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_in' => config('sanctum.expiration') ? config('sanctum.expiration') * 60 : null,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credenziali non valide',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il login',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get authenticated user info.
     * GET /api/auth/user
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utente non autenticato'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati utente',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Logout user (revoke current token).
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Elimina il token corrente
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout effettuato con successo'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il logout',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Change user password.
     * POST /api/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = $request->user();

            // Verifica password corrente
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La password corrente non è corretta',
                    'errors' => ['current_password' => ['Password corrente non valida']]
                ], 422);
            }

            // Aggiorna password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password cambiata con successo'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il cambio password',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update user profile.
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Gestione avatar
            if ($request->hasFile('avatar')) {
                // Elimina vecchio avatar se esiste
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Salva nuovo avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $updateData['avatar'] = $avatarPath;
            }

            $user->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profilo aggiornato con successo',
                'data' => new UserResource($user->fresh())
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento del profilo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get user's active sessions/tokens.
     * GET /api/auth/sessions
     */
    public function sessions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();

            $sessions = $tokens->map(function ($token) use ($request) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'is_current' => $token->id === $request->user()->currentAccessToken()->id,
                    'created_at' => $token->created_at->format('Y-m-d H:i:s'),
                    'last_used_at' => $token->last_used_at?->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $sessions,
                'total' => $sessions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle sessioni',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Revoke a specific token/session.
     * DELETE /api/auth/sessions/{tokenId}
     */
    public function revokeSession(Request $request, $tokenId): JsonResponse
    {
        try {
            $user = $request->user();
            $currentTokenId = $user->currentAccessToken()->id;

            // Non permettere di eliminare il token corrente
            if ($tokenId == $currentTokenId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non puoi eliminare la sessione corrente. Usa logout invece.'
                ], 422);
            }

            $deleted = $user->tokens()->where('id', $tokenId)->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sessione non trovata'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sessione revocata con successo'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella revoca della sessione',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Check if current token is valid.
     * GET /api/auth/check
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user || !$user->is_active) {
                return response()->json([
                    'success' => false,
                    'authenticated' => false,
                    'message' => 'Token non valido o account disabilitato'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'authenticated' => true,
                'user' => new UserResource($user),
                'token_info' => [
                    'name' => $user->currentAccessToken()->name,
                    'abilities' => $user->currentAccessToken()->abilities,
                    'created_at' => $user->currentAccessToken()->created_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'Errore nella verifica del token',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 401);
        }
    }

    /**
     * Get user abilities based on role.
     */
    private function getUserAbilities(User $user): array
    {
        $abilities = ['read-profile', 'update-profile'];

        switch ($user->role) {
            case 'admin':
                $abilities = array_merge($abilities, [
                    'manage-users',
                    'manage-projects',
                    'manage-categories',
                    'manage-media',
                    'manage-contacts',
                    'delete-any',
                    'view-admin-panel'
                ]);
                break;

            case 'editor':
                $abilities = array_merge($abilities, [
                    'manage-projects',
                    'manage-categories',
                    'manage-media',
                    'view-contacts',
                    'view-admin-panel'
                ]);
                break;

            case 'user':
            default:
                // Solo abilità base già definite
                break;
        }

        return $abilities;
    }

    /**
     * Logout from all devices (revoke all tokens).
     * POST /api/auth/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            // Elimina tutti i token dell'utente
            $tokensDeleted = $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout effettuato da tutti i dispositivi',
                'tokens_revoked' => $tokensDeleted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il logout',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
