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
                'icon' => '🧍',
                'description' => 'Artikel tentang pentingnya postur tubuh anak dan dampaknya',
                'order' => 1,
            ],
            [
                'name' => 'Stimulasi Motorik',
                'slug' => 'stimulasi-motorik',
                'icon' => '🤸',
                'description' => 'Panduan stimulasi motorik kasar dan halus anak',
                'order' => 2,
            ],
            [
                'name' => 'Integrasi Sensorik',
                'slug' => 'integrasi-sensorik',
                'icon' => '🧠',
                'description' => 'Informasi tentang perkembangan sensorik anak',
                'order' => 3,
            ],
            [
                'name' => 'Tips Keseharian',
                'slug' => 'tips-keseharian',
                'icon' => '💡',
                'description' => 'Tips praktis untuk orang tua dalam kegiatan sehari-hari',
                'order' => 4,
            ],
            [
                'name' => 'Latihan Postur',
                'slug' => 'latihan-postur',
                'icon' => '🏋️',
                'description' => 'Latihan sederhana untuk memperbaiki postur anak',
                'order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            ArticleCategory::create($category);
        }
    }
}
