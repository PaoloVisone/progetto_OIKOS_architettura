<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Search projects for admin
     */
    public function searchProjects(Request $request)
    {
        $query = $request->get('q', '');
        $status = $request->get('status');
        $featured = $request->get('featured');

        $projects = Project::query()
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($featured !== null, function ($q) use ($featured) {
                $q->where('featured', $featured === 'true');
            })
            ->with(['media' => function ($query) {
                $query->where('is_featured', true)->limit(1);
            }])
            ->withCount('media')
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'projects' => $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'slug' => $project->slug,
                    'description' => $project->description ? Str::limit($project->description, 100) : null,
                    'status' => $project->status,
                    'featured' => $project->featured,
                    'media_count' => $project->media_count,
                    'featured_image' => $project->media->first()
                        ? Storage::url($project->media->first()->thumbnail_path ?? $project->media->first()->path)
                        : null,
                    'created_at' => $project->created_at->diffForHumans(),
                    'updated_at' => $project->updated_at->diffForHumans(),
                    'urls' => [
                        'show' => route('admin.projects.show', $project),
                        'edit' => route('admin.projects.edit', $project),
                        'media' => route('admin.projects.media.index', $project),
                    ]
                ];
            })
        ]);
    }

    /**
     * Get project stats for admin
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'all'); // all, today, week, month, year

        $query = Project::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('created_at', '>=', now()->subYear());
                break;
        }

        $stats = [
            'total_projects' => $query->count(),
            'published_projects' => (clone $query)->where('status', 'published')->count(),
            'draft_projects' => (clone $query)->where('status', 'draft')->count(),
            'featured_projects' => (clone $query)->where('featured', true)->count(),
            'total_media' => ProjectMedia::count(),
            'total_images' => ProjectMedia::where('type', 'image')->count(),
            'total_videos' => ProjectMedia::where('type', 'video')->count(),
            'storage_used' => $this->formatBytes(ProjectMedia::sum('file_size')),
        ];

        // Chart data per gli ultimi 30 giorni
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'projects' => Project::whereDate('created_at', $date)->count(),
                'media' => ProjectMedia::whereDate('created_at', $date)->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'chart_data' => $chartData,
            'period' => $period
        ]);
    }

    /**
     * Get media info for a specific media ID
     */
    public function getMediaInfo(ProjectMedia $media)
    {
        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'type' => $media->type,
                'filename' => $media->filename,
                'original_name' => $media->original_name,
                'alt_text' => $media->alt_text,
                'description' => $media->description,
                'is_featured' => $media->is_featured,
                'file_size' => $this->formatBytes($media->file_size),
                'dimensions' => $media->width && $media->height
                    ? "{$media->width}x{$media->height}"
                    : null,
                'duration' => $media->duration,
                'mime_type' => $media->mime_type,
                'url' => Storage::url($media->path),
                'thumbnail_url' => $media->thumbnail_path
                    ? Storage::url($media->thumbnail_path)
                    : Storage::url($media->path),
                'created_at' => $media->created_at->format('d/m/Y H:i'),
                'updated_at' => $media->updated_at->format('d/m/Y H:i'),
                'project' => [
                    'id' => $media->project->id,
                    'title' => $media->project->title,
                    'url' => route('admin.projects.show', $media->project)
                ]
            ]
        ]);
    }

    /**
     * Validate slug availability
     */
    public function validateSlug(Request $request)
    {
        $slug = $request->get('slug');
        $projectId = $request->get('project_id');

        if (empty($slug)) {
            return response()->json([
                'success' => false,
                'message' => 'Slug richiesto'
            ]);
        }

        $query = Project::where('slug', $slug);

        if ($projectId) {
            $query->where('id', '!=', $projectId);
        }

        $exists = $query->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Questo slug è già in uso' : 'Slug disponibile',
            'suggestion' => $exists ? $this->generateUniqueSlug($slug) : null
        ]);
    }

    /**
     * Generate suggestions for project titles
     */
    public function getSuggestions(Request $request)
    {
        $type = $request->get('type', 'title'); // title, description, tags
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = [];

        switch ($type) {
            case 'title':
                $suggestions = Project::where('title', 'LIKE', "%{$query}%")
                    ->pluck('title')
                    ->unique()
                    ->take(5)
                    ->values();
                break;
            case 'description':
                $suggestions = Project::where('description', 'LIKE', "%{$query}%")
                    ->whereNotNull('description')
                    ->pluck('description')
                    ->map(function ($desc) use ($query) {
                        // Estrai frasi che contengono la query
                        $sentences = explode('.', $desc);
                        return collect($sentences)
                            ->filter(function ($sentence) use ($query) {
                                return stripos($sentence, $query) !== false;
                            })
                            ->map(function ($sentence) {
                                return trim($sentence) . '.';
                            })
                            ->first();
                    })
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->values();
                break;
        }

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 1)
    {
        if ($bytes === 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($slug)
    {
        $originalSlug = $slug;
        $counter = 1;

        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
