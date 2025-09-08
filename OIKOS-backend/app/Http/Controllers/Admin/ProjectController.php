<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('media')
            ->withCount('media')
            ->latest()
            ->paginate(10);

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Progetto creato con successo!');
    }

    public function show(Project $project)
    {
        $project->load(['media' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Progetto aggiornato con successo!');
    }

    public function destroy(Project $project)
    {
        // Elimina tutti i media associati
        foreach ($project->media as $media) {
            app(\App\Services\MediaProcessingService::class)->deleteMediaFiles($media);
            $media->delete();
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
