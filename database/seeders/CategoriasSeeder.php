<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Analgésicos',
            'Antibióticos',
            'Antiinflamatorios',
            'Antiácidos',
            'Vitaminas',
            'Suplementos',
            'Cosméticos',
            'Dermatología',
        ];

        foreach ($categorias as $nombre) {
            Categoria::create([
                'nombre' => $nombre,
                'activo' => true,
            ]);
        }
    }
}