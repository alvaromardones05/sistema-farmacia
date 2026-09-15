<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Procesa una nueva solicitud de registro.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validamos los datos que actualmente solicita
        // el formulario de registro.
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        // El esquema actual de usuarios exige RUT y apellidos.
        // Como el formulario público todavía no solicita estos datos,
        // utilizamos valores temporales para poder crear el usuario.
        //
        // El RUT se genera de forma única para evitar conflictos
        // con la restricción UNIQUE de la base de datos.
        $rutTemporal = 'TEMP-' . strtoupper(substr(
            str_replace('-', '', (string) \Illuminate\Support\Str::uuid()),
            0,
            8
        ));

        // Creamos el usuario con todos los campos obligatorios
        // definidos actualmente en la tabla users.
        $user = User::create([
            'rut' => $rutTemporal,
            'name' => $request->name,
            'apellidos' => 'Pendiente',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Disparamos el evento estándar de Laravel para informar
        // que se ha registrado un nuevo usuario.
        event(new Registered($user));

        // Iniciamos sesión automáticamente después del registro.
        Auth::login($user);

        // Redirigimos al dashboard después de autenticarse.
        return redirect(route('dashboard', absolute: false));
    }
}