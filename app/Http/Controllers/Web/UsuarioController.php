<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
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
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rut' => 'required|string|max:12|unique:users,rut',
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'telefono' => 'nullable|string|max:20',
            'numero_registro_tecnico' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'rut' => $validated['rut'],
            'name' => $validated['name'],
            'apellidos' => $validated['apellidos'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'numero_registro_tecnico' => $validated['numero_registro_tecnico'] ?? null,
            'password' => bcrypt($validated['password']),
            'activo' => $request->boolean('activo'),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente');
    }

    public function show(User $usuario)
    {
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'rut' => 'required|string|max:12|unique:users,rut,' . $usuario->id,
            'name' => 'required|string|max:255',
            'apellidos' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:20',
            'numero_registro_tecnico' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario->update([
            'rut' => $validated['rut'],
            'name' => $validated['name'],
            'apellidos' => $validated['apellidos'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'numero_registro_tecnico' => $validated['numero_registro_tecnico'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);

        if (!empty($validated['password'])) {
            $usuario->update(['password' => $validated['password']]);
        }

        $usuario->syncRoles([$validated['role']]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        $usuario->delete();
 
        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario destruido exitosamente.');
    }
}