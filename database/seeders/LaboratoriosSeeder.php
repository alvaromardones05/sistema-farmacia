<?php

namespace Database\Seeders;

use App\Models\Laboratorio;
use Illuminate\Database\Seeder;

class LaboratoriosSeeder extends Seeder
{
    public function run(): void
    {
        $laboratorios = [
            ['nombre' => 'Bayer', 'pais' => 'Alemania'],
            ['nombre' => 'Pfizer', 'pais' => 'Estados Unidos'],
            ['nombre' => 'Roche', 'pais' => 'Suiza'],
            ['nombre' => 'Novartis', 'pais' => 'Suiza'],
            ['nombre' => 'Laboratorio Andrómaco', 'pais' => 'Chile'],
            ['nombre' => 'Simulab', 'pais' => 'Chile'],
        ];

        foreach ($laboratorios as $lab) {
            Laboratorio::create($lab);
        }
    }
}