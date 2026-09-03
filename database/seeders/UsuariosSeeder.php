<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'rut' => '11111111-1',
            'name' => 'Administrador',
            'apellidos' => 'Sistema',
            'email' => 'admin@farmacia.local',
            'telefono' => '912345678',
            'password' => Hash::make('admin123'),
            'activo' => true,
            'numero_registro_tecnico' => null,
        ]);
        $admin->assignRole('Administrador');

        // Vendedor
        $bodeguero = User::create([
            'rut' => '22222222-2',
            'name' => 'Juan',
            'apellidos' => 'Palanca',
            'email' => 'vendedor@farmacia.local',
            'telefono' => '987654321',
            'password' => Hash::make('bodeguero123'),
            'activo' => true,
        ]);
        $bodeguero->assignRole('Bodeguero');

        // Técnico Farmacéutico
        $tecnico = User::create([
            'rut' => '33333333-3',
            'name' => 'María',
            'apellidos' => 'Técnica',
            'email' => 'tecnica@farmacia.local',
            'telefono' => '912345679',
            'password' => Hash::make('tecnica123'),
            'activo' => true,
            'numero_registro_tecnico' => 'TF-123456',
        ]);
        $tecnico->assignRole('Técnico Farmacéutico');

        // Químico Farmacéutico
        $quimico = User::create([
            'rut' => '44444444-4',
            'name' => 'Carlos',
            'apellidos' => 'Químico',
            'email' => 'quimico@farmacia.local',
            'telefono' => '912345680',
            'password' => Hash::make('quimico123'),
            'activo' => true,
            'numero_registro_tecnico' => 'QF-654321',
        ]);
        $quimico->assignRole('Químico Farmacéutico');
    }
}