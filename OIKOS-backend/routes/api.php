<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ContactController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Qui puoi registrare le rotte API per la tua applicazione.
|
*/

// ===============================
// ROTTE PUBBLICHE (NO AUTENTICAZIONE)
// ===============================
Route::prefix('v1')->group(function () {

    // AUTH
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        // Route::post('forgot-password', [AuthController::class, 'forgotPassword']); // futuro
        // Route::post('reset-password', [AuthController::class, 'resetPassword']);   // futuro
    });

    // CONTATTI
    Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');

    // PROGETTI PUBBLICI
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.public.index');
    Route::get('projects/featured', [ProjectController::class, 'featured'])->name('projects.featured');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.public.show');

    // CATEGORIE PUBBLICHE
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.public.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.public.show');
});

// ===============================
// ROTTE PROTETTE (AUTENTICAZIONE SANCTUM)
// ===============================
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // AUTH
    Route::prefix('auth')->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
        Route::get('check', [AuthController::class, 'check'])->name('auth.check');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        Route::put('profile', [AuthController::class, 'updateProfile'])->name('auth.update-profile');
        Route::get('sessions', [AuthController::class, 'sessions'])->name('auth.sessions');
        Route::delete('sessions/{tokenId}', [AuthController::class, 'revokeSession'])->name('auth.revoke-session');
    });

    // ===============================
    // ADMIN ONLY
    // ===============================
    Route::middleware(['admin'])->group(function () {

        // PROGETTI (CRUD)
        Route::prefix('admin/projects')->name('admin.projects.')->group(function () {
            Route::post('', [ProjectController::class, 'store'])->name('store');
            Route::put('{project}', [ProjectController::class, 'update'])->name('update');
            Route::delete('{project}', [ProjectController::class, 'destroy'])->name('destroy');
            Route::patch('{project}/status', [ProjectController::class, 'updateStatus'])->name('update-status');
            Route::patch('{project}/featured', [ProjectController::class, 'toggleFeatured'])->name('toggle-featured');
        });

        // CATEGORIE (CRUD)
        Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
            Route::post('', [CategoryController::class, 'store'])->name('store');
            Route::put('{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('{category}', [CategoryController::class, 'destroy'])->name('destroy');
            Route::patch('{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('reorder', [CategoryController::class, 'reorder'])->name('reorder');
        });

        // MEDIA
        Route::prefix('admin/media')->name('admin.media.')->group(function () {
            Route::post('projects/{project}', [MediaController::class, 'store'])->name('upload');
            Route::get('{media}', [MediaController::class, 'show'])->name('show');
            Route::put('{media}', [MediaController::class, 'update'])->name('update');
            Route::delete('{media}', [MediaController::class, 'destroy'])->name('destroy');
            Route::post('projects/{project}/reorder', [MediaController::class, 'reorder'])->name('reorder');
        });

        // Lista media per progetto (anche per editor)
        Route::get('projects/{project}/media', [MediaController::class, 'index'])->name('project.media.index');

        // CONTATTI (ADMIN)
        Route::prefix('admin/contacts')->name('admin.contacts.')->group(function () {
            Route::get('', [ContactController::class, 'index'])->name('index');
            Route::get('stats', [ContactController::class, 'stats'])->name('stats');
            Route::get('{contact}', [ContactController::class, 'show'])->name('show');
            Route::patch('{contact}', [ContactController::class, 'update'])->name('update');
            Route::delete('{contact}', [ContactController::class, 'destroy'])->name('destroy');
            Route::patch('{contact}/read', [ContactController::class, 'markAsRead'])->name('mark-read');
            Route::patch('{contact}/replied', [ContactController::class, 'markAsReplied'])->name('mark-replied');
            Route::post('bulk-update', [ContactController::class, 'bulkUpdate'])->name('bulk-update');
        });
    });

    // ===============================
    // EDITOR (Admin + Editor)
    // ===============================
    Route::middleware(['admin:editor'])->group(function () {
        Route::prefix('editor')->name('editor.')->group(function () {
            Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
            Route::get('contacts/stats', [ContactController::class, 'stats'])->name('contacts.stats');
            Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
            Route::patch('contacts/{contact}/read', [ContactController::class, 'markAsRead'])->name('contacts.mark-read');
        });
    });
});

// ===============================
// HEALTH CHECK & FALLBACK
// ===============================
Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is running',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });

    Route::fallback(function () {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint non trovato'
        ], 404);
    });
});
