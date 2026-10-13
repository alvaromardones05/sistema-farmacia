<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use App\Models\ConvenioCoberturaProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConvenioController extends Controller
{
    public function index()
    {
        $convenios = Convenio::withCount([
                'pacientes',
                'coberturas',
            ])
            ->orderBy('nombre')
            ->paginate(15);

        return view('convenios.index', compact('convenios'));
    }

    public function create()
    {
        return view('convenios.form', [
            'convenio' => null,
            'productos' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validarConvenio($request);

        $datos['activo'] = $request->boolean('activo');

        $convenio = Convenio::create($datos);

        return redirect()
            ->route('convenios.edit', $convenio)
            ->with(
                'success',
                'Convenio creado correctamente. Ahora puedes configurar sus coberturas.'
            );
    }

    public function edit(Convenio $convenio)
    {
        $convenio->load('coberturas.producto');

        $productos = Producto::activos()
            ->orderBy('nombre')
            ->get();

        return view(
            'convenios.form',
            compact('convenio', 'productos')
        );
    }

    public function update(Request $request, Convenio $convenio)
    {
        $datos = $this->validarConvenio($request, $convenio);

        $datos['activo'] = $request->boolean('activo');

        $convenio->update($datos);

        return back()->with(
            'success',
            'Convenio actualizado correctamente.'
        );
    }

    public function guardarCobertura(
        Request $request,
        Convenio $convenio
    ) {
        $datos = $request->validate([
            'producto_id' => [
                'required',
                'exists:productos,id',
                Rule::unique(
                    'convenio_cobertura_producto',
                    'producto_id'
                )->where(
                    fn ($query) =>
                    $query->where('convenio_id', $convenio->id)
                ),
            ],

            'copago_pct' => 'required|numeric|min:0|max:100',

            'copago_fijo' => 'nullable|numeric|min:0',

            'observaciones' => 'nullable|string|max:1000',
        ]);

        $convenio->coberturas()->create($datos);

        return back()->with(
            'success',
            'Cobertura agregada correctamente.'
        );
    }

    public function eliminarCobertura(
        Convenio $convenio,
        ConvenioCoberturaProducto $cobertura
    ) {
        if ($cobertura->convenio_id !== $convenio->id) {
            abort(404);
        }

        $cobertura->delete();

        return back()->with(
            'success',
            'Cobertura eliminada correctamente.'
        );
    }

    private function validarConvenio(
        Request $request,
        ?Convenio $convenio = null
    ): array {
        return $request->validate([
            'nombre' => 'required|string|max:150',

            'codigo_convenio' => [
                'required',
                'string',
                'max:50',

                Rule::unique('convenios', 'codigo_convenio')
                    ->ignore($convenio?->id),
            ],

            'tipo' => [
                'required',
                Rule::in([
                    'isapre',
                    'fonasa',
                    'ges',
                    'particular',
                    'otro',
                ]),
            ],

            'descuento_pct' => 'required|numeric|min:0|max:100',

            'observaciones' => 'nullable|string|max:1000',
        ]);
    }
}