<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with([
            'media' => fn($q) => $q->where('is_featured', true)->take(1),
        ])
            ->withCount('media')
            ->latest()
            ->paginate(10);

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.projects.create', compact('categories'));
    }

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();

        // Tags: "a, b, c" -> ["a","b","c"]
        if (!empty($data['tags'])) {
            $data['tags'] = collect(explode(',', $data['tags']))
                ->map(fn($t) => trim($t))
                ->filter()
                ->values()
                ->all();
        }

        // Upload featured image (opzionale)
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('projects/images', 'public');
        }

        $project = Project::create($data);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Progetto creato con successo!');
    }

    public function show(Project $project)
    {
        $project->load([
            'category',
            'media' => fn($q) => $q->orderBy('sort_order'),
        ]);
        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.projects.edit', compact('project', 'categories'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();

        // Tags: "a, b, c" -> ["a","b","c"]
        if (array_key_exists('tags', $data)) {
            $data['tags'] = !empty($data['tags'])
                ? collect(explode(',', $data['tags']))
                ->map(fn($t) => trim($t))
                ->filter()
                ->values()
                ->all()
                : [];
        }

        // Sostituisci featured image (se caricata)
        if ($request->hasFile('featured_image')) {
            // elimina la precedente se esiste
            if ($project->featured_image) {
                Storage::disk('public')->delete($project->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')
                ->store('projects/images', 'public');
        }

        $project->update($data);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Progetto aggiornato con successo!');
    }

    public function destroy(Project $project)
    {
        foreach ($project->media as $media) {
            app(\App\Services\MediaProcessingService::class)->deleteMediaFiles($media);
            $media->delete();
        }

        // elimina featured image del progetto (se salvata)
        if ($project->featured_image) {
            Storage::disk('public')->delete($project->featured_image);
        }

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Progetto eliminato con successo!');
    }

    public function toggleStatus(Project $project)
    {
        $newStatus = $project->status === 'published' ? 'draft' : 'published';
        $project->update(['status' => $newStatus]);

        $message = $newStatus === 'published'
            ? 'Progetto pubblicato!'
            : 'Progetto salvato come bozza!';

        return back()->with('success', $message);
    }
}
