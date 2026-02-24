<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Desmalezado',
                'description' => 'Trabajos profesionales de desmalezado en terrenos urbanos y rurales',
                'order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Limpieza de Terrenos',
                'description' => 'Limpieza general, preparación para construcción',
                'order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Roza',
                'description' => 'Servicios de roza para campos y zonas rurales',
                'order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Precios',
                'description' => 'Información sobre costos y presupuestos',
                'order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Prevención',
                'description' => 'Prevención de incendios y mantenimiento obligatorio',
                'order' => 5,
                'is_active' => true
            ],
            [
                'name' => 'Legal',
                'description' => 'Multas, ordenanzas y normativas',
                'order' => 6,
                'is_active' => true
            ],
            [
                'name' => 'Consejos',
                'description' => 'Tips y recomendaciones para propietarios',
                'order' => 7,
                'is_active' => true
            ],
            [
                'name' => 'Zonas',
                'description' => 'Trabajos realizados en diferentes localidades',
                'order' => 8,
                'is_active' => true
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}