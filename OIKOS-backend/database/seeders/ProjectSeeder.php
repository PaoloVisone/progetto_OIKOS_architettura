<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Recupera una categoria di default (es. Residenziale)
        $defaultCategory = Category::first();

        if (!$defaultCategory) {
            $this->command->warn('Nessuna categoria trovata. Esegui prima il CategorySeeder.');
            return;
        }

        $projects = [
            [
                'title' => 'Villa Moderna',
                'description' => 'Una villa unifamiliare con design contemporaneo.',
                'long_description' => 'Questo progetto residenziale integra materiali sostenibili e ampie vetrate per massimizzare la luce naturale.',
                'client' => 'Famiglia Rossi',
                'location' => 'Milano',
                'project_date' => '2023-05-10',
                'area' => 320.50,
                'status' => 'published',
                'is_featured' => true,
                'tags' => json_encode(['residenziale', 'design moderno']),
                'featured_image' => 'projects/images/villa-moderna.jpg',
            ],
            [
                'title' => 'Complesso Commerciale Aurora',
                'description' => 'Spazio multifunzionale per uffici e negozi.',
                'long_description' => 'Progetto innovativo per un centro commerciale a basso impatto ambientale.',
                'client' => 'Aurora S.p.A.',
                'location' => 'Torino',
                'project_date' => '2022-09-15',
                'area' => 1500.00,
                'status' => 'published',
                'is_featured' => false,
                'tags' => json_encode(['commerciale', 'sostenibilità']),
                'featured_image' => 'projects/images/complesso-aurora.jpg',
            ],
        ];

        foreach ($projects as $index => $proj) {
            Project::firstOrCreate(
                ['slug' => Str::slug($proj['title'])],
                array_merge($proj, [
                    'sort_order' => $index,
                    // assegna la prima categoria trovata
                    'category_id' => $defaultCategory->id,
                ])
            );
        }
    }
}
