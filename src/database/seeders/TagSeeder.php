<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Ubicaciones
            ['name' => 'Pilar'],
            ['name' => 'Campana'],
            ['name' => 'Escobar'],
            ['name' => 'Moreno'],
            ['name' => 'San Miguel'],
            ['name' => 'Tigre'],
            ['name' => 'Zona Norte'],
            
            // Tipos de trabajo
            ['name' => 'Terreno baldío'],
            ['name' => 'Construcción'],
            ['name' => 'Campo'],
            ['name' => 'Quinta'],
            ['name' => 'Lote'],
            
            // Maquinaria
            ['name' => 'Desmalezadora'],
            ['name' => 'Tractor'],
            ['name' => 'Retroexcavadora'],
            ['name' => 'Motosierra'],
            
            // Temas
            ['name' => 'Incendios'],
            ['name' => 'Multas'],
            ['name' => 'Mantenimiento'],
            ['name' => 'Urgente'],
            ['name' => 'Presupuesto'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}