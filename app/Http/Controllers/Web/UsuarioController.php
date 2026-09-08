<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('roles')->paginate(15);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('usuarios.crear', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $user): View
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('usuarios.editar', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'required|array',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }
}