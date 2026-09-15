<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory para generar usuarios de prueba.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Contraseña que utilizarán los usuarios creados por el factory.
     */
    protected static ?string $password = null;

    /**
     * Define los valores por defecto para un usuario de prueba.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Nombre del usuario.
            'name' => fake()->name(),

            // RUT ficticio para cumplir con el campo obligatorio
            // definido en la tabla users.
            'rut' => fake()->unique()->numerify('########-#'),

            // Apellidos obligatorios según la estructura actual
            // de la tabla users.
            'apellidos' => fake()->lastName(),

            // Correo electrónico único para evitar conflictos
            // entre usuarios creados durante los tests.
            'email' => fake()->unique()->safeEmail(),

            // Los usuarios creados por defecto tendrán el correo
            // marcado como verificado.
            'email_verified_at' => now(),

            // Contraseña utilizada por defecto en los tests.
            'password' => static::$password ??= Hash::make('password'),

            // Token utilizado por la autenticación "remember me".
            'remember_token' => Str::random(10),

            // Campos adicionales de la tabla users.
            'telefono' => null,
            'numero_registro_tecnico' => null,
            'activo' => true,
            'must_change_password' => false,
            'last_login_at' => null,
        ];
    }

    /**
     * Indica que el correo electrónico del usuario
     * debe quedar sin verificar.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}