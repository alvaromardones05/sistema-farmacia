<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_razon_social', 'like', "%{$buscar}%")
                    ->orWhere('rut', 'like', "%{$buscar}%")
                    ->orWhere('contacto_nombre', 'like', "%{$buscar}%");
            });
        }

        $proveedores = $query
            ->orderBy('nombre_razon_social')
            ->paginate(15)
            ->withQueryString();

        return view('compras.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('compras.proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rut' => 'required|string|max:12|unique:proveedores,rut',
            'nombre_razon_social' => 'required|string|max:200',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
        ]);

        $validated['activo'] = true;

        Proveedor::create($validated);

        return redirect()
            ->route('compras.proveedores.index')
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('compras.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'rut' => 'required|string|max:12|unique:proveedores,rut,' . $proveedor->id,
            'nombre_razon_social' => 'required|string|max:200',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
        ]);

        $proveedor->update($validated);

        return redirect()
            ->route('compras.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function cambiarEstado(Proveedor $proveedor)
    {
        $proveedor->update([
            'activo' => !$proveedor->activo,
        ]);

        return back()->with(
            'success',
            $proveedor->activo
                ? 'Proveedor activado correctamente.'
                : 'Proveedor desactivado correctamente.'
        );
    }
}