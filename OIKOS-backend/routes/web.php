<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\SearchController;
use Illuminate\Support\Facades\Route;

// Rotte pubbliche
Route::get('/', function () {
    return view('welcome');
});

// Rotte di autenticazione (Breeze)
require __DIR__ . '/auth.php';

// Rotte admin protette
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestione progetti
    Route::resource('projects', AdminProjectController::class);
    Route::patch('projects/{project}/toggle-status', [AdminProjectController::class, 'toggleStatus'])
        ->name('projects.toggle-status');

    // Gestione media
    Route::get('projects/{project}/media', [AdminMediaController::class, 'index'])
        ->name('projects.media.index');
    Route::post('projects/{project}/media', [AdminMediaController::class, 'store'])
        ->name('projects.media.store');
    Route::patch('media/{media}', [AdminMediaController::class, 'update'])
        ->name('media.update');
    Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])
        ->name('media.destroy');
    Route::patch('media/{media}/featured', [AdminMediaController::class, 'setFeatured'])
        ->name('media.featured');

    // API per ricerche e utilità
    Route::prefix('api')->group(function () {
        Route::get('search/projects', [SearchController::class, 'searchProjects'])
            ->name('api.search.projects');
        Route::get('stats', [SearchController::class, 'getStats'])
            ->name('api.stats');
        Route::get('media/{media}/info', [SearchController::class, 'getMediaInfo'])
            ->name('api.media.info');
        Route::post('validate-slug', [SearchController::class, 'validateSlug'])
            ->name('api.validate-slug');
        Route::get('suggestions', [SearchController::class, 'getSuggestions'])
            ->name('api.suggestions');
    });
});

// Registra il middleware nel bootstrap/app.php o Kernel.php
// Route::middleware(['admin'])->group(...)