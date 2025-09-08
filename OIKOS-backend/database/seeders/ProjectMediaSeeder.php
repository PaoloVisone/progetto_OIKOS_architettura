<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectMedia;

class ProjectMediaSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->warn('Nessun progetto trovato. Esegui prima ProjectSeeder.');
            return;
        }

        foreach ($projects as $project) {
            // Aggiungi 2 immagini demo
            for ($i = 1; $i <= 2; $i++) {
                ProjectMedia::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'filename'   => "project{$project->id}_img{$i}.jpg",
                    ],
                    [
                        'type'          => 'image',
                        'original_name' => "demo_img{$i}.jpg",
                        'mime_type'     => 'image/jpeg',
                        'file_size'     => 204800, // ~200KB
                        'path'          => "projects/images/project{$project->id}_img{$i}.jpg",
                        'thumbnail_path' => "projects/images/thumbs/project{$project->id}_img{$i}.jpg",
                        'width'         => 1200,
                        'height'        => 800,
                        'alt_text'      => "Immagine {$i} del progetto {$project->title}",
                        'description'   => "Descrizione immagine {$i} per il progetto demo.",
                        'is_featured'   => ($i === 1),
                        'sort_order'    => $i,
                    ]
                );
            }

            // Aggiungi 1 video demo
            ProjectMedia::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'filename'   => "project{$project->id}_video.mp4",
                ],
                [
                    'type'           => 'video',
                    'original_name'  => "demo_video.mp4",
                    'mime_type'      => 'video/mp4',
                    'file_size'      => 5242880, // ~5MB
                    'path'           => "projects/videos/project{$project->id}_video.mp4",
                    'compressed_path' => "projects/videos/compressed/project{$project->id}_video.mp4",
                    'thumbnail_path' => "projects/videos/thumbs/project{$project->id}_video.jpg",
                    'width'          => 1920,
                    'height'         => 1080,
                    'duration'       => 60,
                    'alt_text'       => "Video demo del progetto {$project->title}",
                    'description'    => "Video dimostrativo associato al progetto {$project->title}.",
                    'is_featured'    => false,
                    'sort_order'     => 99,
                ]
            );
        }
    }
}
