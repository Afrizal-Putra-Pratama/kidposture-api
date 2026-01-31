<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pentingnya Postur Tubuh',
                'slug' => 'pentingnya-postur-tubuh',
                'description' => 'Artikel tentang pentingnya postur tubuh anak dan dampaknya',
                'order' => 1,
            ],
            [
                'name' => 'Stimulasi Motorik',
                'slug' => 'stimulasi-motorik',
                'description' => 'Panduan stimulasi motorik kasar dan halus anak',
                'order' => 2,
            ],
            [
                'name' => 'Integrasi Sensorik',
                'slug' => 'integrasi-sensorik',
                'description' => 'Informasi tentang perkembangan sensorik anak',
                'order' => 3,
            ],
            [
                'name' => 'Tips Keseharian',
                'slug' => 'tips-keseharian',
                'description' => 'Tips praktis untuk orang tua dalam kegiatan sehari-hari',
                'order' => 4,
            ],
            [
                'name' => 'Latihan Postur',
                'slug' => 'latihan-postur',
                'description' => 'Latihan sederhana untuk memperbaiki postur anak',
                'order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            ArticleCategory::create($category);
        }
    }
}

