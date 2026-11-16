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
            'email' => 'admin@email.cl',
            'telefono' => '912345678',
            'password' => Hash::make('admin123'),
            'activo' => true,
            'numero_registro_tecnico' => null,
        ]);
        $admin->assignRole('Administrador');


        $admin = User::create([
            'rut' => '20132938-2',
            'name' => 'Norberto',
            'apellidos' => 'Scoponi',
            'email' => 'tecnico@email.cl',
            'telefono' => '912345678',
            'password' => Hash::make('tecnico123'),
            'activo' => true,
            'numero_registro_tecnico' => null,
        ]);
        $admin->assignRole('Técnico Farmacéutico');


        $admin = User::create([
            'rut' => '19283018-4',
            'name' => 'Justino',
            'apellidos' => 'García',
            'email' => 'quimico@email.cl',
            'telefono' => '912345678',
            'password' => Hash::make('quimico123'),
            'activo' => true,
            'numero_registro_tecnico' => null,
        ]);
        $admin->assignRole('Químico Farmacéutico');


        $admin = User::create([
            'rut' => '21820193-1',
            'name' => 'Zalo',
            'apellidos' => 'Hernández',
            'email' => 'bodeguero@email.cl',
            'telefono' => '912345678',
            'password' => Hash::make('bodeguero123'),
            'activo' => true,
            'numero_registro_tecnico' => null,
        ]);
        $admin->assignRole('Bodeguero');

        
}}