<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil del usuario autenticado.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario autenticado.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Obtenemos los datos validados y los cargamos
        // en el usuario actualmente autenticado.
        $request->user()->fill($request->validated());

        // Si el correo electrónico cambió, solicitamos
        // nuevamente su verificación.
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Guardamos los cambios realizados.
        $request->user()->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Elimina definitivamente la cuenta del usuario autenticado.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Comprobamos que el usuario confirme su contraseña
        // antes de permitir la eliminación de la cuenta.
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        // Guardamos la instancia del usuario antes de cerrar
        // la sesión para poder eliminarlo posteriormente.
        $user = $request->user();

        // Cerramos la sesión del usuario.
        Auth::logout();

        // Eliminamos físicamente el registro.
        //
        // User utiliza SoftDeletes, por lo que delete() solamente
        // establecería deleted_at. El test de Laravel espera que
        // el registro deje de existir completamente.
        $user->forceDelete();

        // Invalidamos la sesión actual para evitar que pueda
        // reutilizarse después de eliminar la cuenta.
        $request->session()->invalidate();

        // Generamos un nuevo token CSRF.
        $request->session()->regenerateToken();

        // Volvemos a la página principal.
        return Redirect::to('/');
    }
}