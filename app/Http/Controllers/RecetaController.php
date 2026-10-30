<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecetaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'paciente' => 'nullable|string|max:100',
            'estado' => ['nullable', Rule::in(['pendiente', 'vigente', 'rechazada', 'utilizada', 'vencida', 'anulada'])],
            'fecha' => 'nullable|date',
        ]);
        $query = Receta::with(['paciente', 'medico'])->latest('id');
        if (!empty($filtros['paciente'])) {
            $buscar = $filtros['paciente'];
            $query->whereHas('paciente', fn ($q) => $q->where(function ($q) use ($buscar) {
                $q->where('rut', 'like', "%{$buscar}%")
                    ->orWhere('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%");
            }));
        }
        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] === 'vencida') {
                $query->where(fn ($q) => $q->where('estado', 'vencida')->orWhere(fn ($q) => $q
                    ->whereIn('estado', ['pendiente', 'vigente'])->whereDate('fecha_vencimiento', '<', today())));
            } else {
                $query->where('estado', $filtros['estado']);
                if (in_array($filtros['estado'], ['pendiente', 'vigente'])) {
                    $query->whereDate('fecha_vencimiento', '>=', today());
                }
            }
        }
        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha_emision', $filtros['fecha']);
        }
        return view('recetas.index', ['recetas' => $query->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('recetas.create', [
            'pacientes' => Paciente::activos()->orderBy('apellidos')->get(),
            'medicos' => Medico::activos()->orderBy('apellidos')->get(),
            'productos' => Producto::activos()->orderBy('nombre')->get(['id', 'nombre', 'unidad_medida', 'tipo_receta']),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => ['required', Rule::exists('pacientes', 'id')->where('activo', true)->whereNull('deleted_at')],
            'medico_id' => ['required', Rule::exists('medicos', 'id')->where('activo', true)->whereNull('deleted_at')],
            'fecha_emision' => 'required|date|before_or_equal:today',
            'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision|after_or_equal:today',
            'tipo' => 'required|in:simple,retenida,cheque',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|extensions:jpg,jpeg,png,pdf|max:5120',
            'observaciones' => 'nullable|string|max:5000',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => ['required', 'integer', 'distinct', Rule::exists('productos', 'id')->where('activo', true)->whereNull('deleted_at')],
            'detalles.*.cantidad_prescrita' => 'required|integer|min:1|max:2147483647',
            'detalles.*.instrucciones' => 'nullable|string|max:255',
        ], [
            'detalles.required' => 'Agrega al menos un medicamento a la receta.',
            'detalles.min' => 'Agrega al menos un medicamento a la receta.',
            'detalles.*.producto_id.required' => 'Selecciona un medicamento para cada detalle.',
            'detalles.*.producto_id.distinct' => 'Un medicamento no puede aparecer duplicado en la receta.',
            'detalles.*.cantidad_prescrita.required' => 'Indica la cantidad prescrita de cada medicamento.',
            'detalles.*.cantidad_prescrita.integer' => 'La cantidad prescrita debe ser un número entero.',
            'detalles.*.cantidad_prescrita.min' => 'La cantidad prescrita debe ser mayor a cero.',
        ]);
        $productos = Producto::whereIn('id', collect($datos['detalles'])->pluck('producto_id'))->get()->keyBy('id');
        foreach ($datos['detalles'] as $detalle) {
            $producto = $productos->get($detalle['producto_id']);
            if (!$producto || !$producto->activo) {
                throw ValidationException::withMessages(['detalles' => 'Uno de los productos ya no está disponible.']);
            }
            if ($producto->tipo_receta !== 'ninguna' && $producto->tipo_receta !== $datos['tipo']) {
                throw ValidationException::withMessages(['detalles' => "El tipo de receta no corresponde a {$producto->nombre}."]);
            }
        }

        $path = $request->file('archivo')->store('recetas', 'local');
        if (!$path) {
            return back()->withInput()->withErrors(['archivo' => 'No fue posible guardar el archivo.']);
        }
        try {
            $receta = DB::transaction(function () use ($datos, $productos, $path) {
                $receta = Receta::create([
                    'numero_receta' => (string) Str::ulid(),
                    'paciente_id' => $datos['paciente_id'], 'medico_id' => $datos['medico_id'],
                    'fecha_emision' => $datos['fecha_emision'], 'fecha_vencimiento' => $datos['fecha_vencimiento'],
                    'tipo' => $datos['tipo'], 'estado' => 'pendiente',
                    'archivo_path' => $path, 'observaciones' => $datos['observaciones'] ?? null,
                ]);
                $receta->detalles()->createMany(collect($datos['detalles'])->map(fn ($detalle) => [
                    'producto_id' => $detalle['producto_id'],
                    'cantidad_prescrita' => $detalle['cantidad_prescrita'], 'cantidad_dispensada' => 0,
                    'unidad_medida' => $productos->get($detalle['producto_id'])->unidad_medida,
                    'instrucciones' => $detalle['instrucciones'] ?? null, 'estado' => 'pendiente',
                ])->all());
                return $receta;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            report($e);
            return back()->withInput()->withErrors(['receta' => 'No fue posible registrar la receta.']);
        }
        return redirect()->route('recetas.show', $receta)->with('success', 'Receta registrada, pendiente de revisión.');
    }

    public function show(Receta $receta)
    {
        $receta->load(['paciente', 'medico', 'detalles.producto']);
        return view('recetas.show', compact('receta'));
    }

    public function archivo(Receta $receta)
    {
        $path = $receta->archivo_path;
        abort_unless($path && str_starts_with($path, 'recetas/') && !str_contains($path, '..')
            && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->response($path, 'receta-'.$receta->numero_receta.'.'.pathinfo($path, PATHINFO_EXTENSION), [
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function aprobar(Receta $receta)
    {
        return $this->resolver($receta, true);
    }

    public function rechazar(Request $request, Receta $receta)
    {
        $datos = $request->validate(['motivo' => 'required|string|max:1000']);
        return $this->resolver($receta, false, $datos['motivo']);
    }

    private function resolver(Receta $receta, bool $aprobar, ?string $motivo = null)
    {
        DB::transaction(function () use ($receta, $aprobar, $motivo) {
            $receta = Receta::whereKey($receta->id)->lockForUpdate()->firstOrFail();
            if ($receta->estado !== 'pendiente') {
                throw ValidationException::withMessages(['receta' => 'Esta receta ya fue revisada y no puede modificarse.']);
            }
            if ($aprobar) {
                $receta->load(['paciente', 'medico', 'detalles.producto']);
                if ($receta->estaVencida() || $receta->fecha_emision->startOfDay()->gt(today())
                    || !$receta->paciente?->activo || !$receta->medico?->activo
                    || !$receta->archivo_path || !Storage::disk('local')->exists($receta->archivo_path)
                    || $receta->detalles->isEmpty()
                    || $receta->detalles->contains(fn ($d) => !$d->producto?->activo || $d->cantidad_prescrita < 1
                        || ($d->producto->tipo_receta !== 'ninguna' && $d->producto->tipo_receta !== $receta->tipo))) {
                    throw ValidationException::withMessages(['receta' => 'Revisa fechas, archivo, paciente, médico y medicamentos antes de aprobar.']);
                }
            }
            $revision = ($aprobar ? 'Aprobada' : 'Rechazada').' por '.auth()->user()->name.' (usuario #'.auth()->id().') el '.now()->format('d/m/Y H:i');
            $receta->update([
                'estado' => $aprobar ? 'vigente' : 'rechazada',
                'observaciones' => trim(($receta->observaciones ?? '')."\n".$revision.($motivo ? ': '.$motivo : '')),
            ]);
        });
        return redirect()->route('recetas.show', $receta)->with('success', 'Receta '.($aprobar ? 'aprobada.' : 'rechazada.'));
    }
}
