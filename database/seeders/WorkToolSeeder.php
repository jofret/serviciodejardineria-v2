<?php

namespace Database\Seeders;

use App\Models\WorkTool;
use Illuminate\Database\Seeder;

class WorkToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            'Con retiro',
            'Sin retiro',
            'Grúa',
            'Arnés',
            'Motosierra grande',
            'Motosierra chica',
            'Tractor',
            'Bobcat',
            'Cortacésped',
            'Motoguadañas',
            'Taladro',
            'Cortacerco',
            'Sogas',
            'Escalera',
        ];

        foreach ($tools as $order => $name) {
            WorkTool::firstOrCreate(['name' => $name], ['order' => $order]);
        }
    }
}
