<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Residenziale',
                'description' => 'Progetti di abitazioni private e complessi residenziali',
            ],
            [
                'name' => 'Commerciale',
                'description' => 'Spazi commerciali, uffici e locali',
            ],
            [
                'name' => 'Interni',
                'description' => 'Interior design e arredamento',
            ],
            [
                'name' => 'Ristrutturazioni',
                'description' => 'Ristrutturazioni e restauri architettonici',
            ],
            [
                'name' => 'Concorsi',
                'description' => 'Progetti sviluppati per concorsi di architettura',
            ],
        ];

        foreach ($categories as $index => $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
