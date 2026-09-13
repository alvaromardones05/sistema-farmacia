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
            'email' => 'admin@farmacia',
            'telefono' => '912345678',
            'password' => Hash::make('admin123'),
            'activo' => true,
            'numero_registro_tecnico' => null,
        ]);
        $admin->assignRole('Administrador');
}}