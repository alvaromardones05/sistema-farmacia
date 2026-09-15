<?php

namespace App\Http\Controllers;

use App\Models\AperturaCaja;
use App\Models\Convenio;
use App\Models\Lote;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    // ===================== LISTAR =====================

    /**
     * Muestra el listado de ventas.
     */
    public function index()
    {
        // Obtiene las ventas más recientes junto con sus relaciones.
        $ventas = Venta::with('paciente', 'usuario', 'detalles')
            ->latest()
            ->paginate(15);

        return view('ventas.index', [
            'ventas' => $ventas,
        ]);
    }

    // ===================== CREAR (FORM) =====================

    /**
     * Muestra el formulario para registrar una nueva venta.
     */
    public function create()
    {
        // Obtiene solamente los pacientes activos.
        $pacientes = Paciente::activos()->get();

        // Obtiene los convenios activos.
        $convenios = Convenio::where('activo', true)->get();

        // Obtiene los productos activos y sus lotes disponibles.
        $productos = Producto::activos()
            ->with([
                'lotes' => function ($query) {
                    // Ordena los lotes por fecha de vencimiento para facilitar
                    // el uso del lote que vence primero.
                    $query->where('estado', 'activo')
                        ->orderBy('fecha_vencimiento');
                }
            ])
            ->get();

        // Busca una apertura de caja que se encuentre actualmente abierta.
        $aperturaCaja = AperturaCaja::with('caja')
            ->where('estado', 'abierta')
            ->first();

        // Si no existe una caja abierta, no se puede realizar una venta.
        if (!$aperturaCaja) {
            return back()->withError('No hay caja abierta');
        }

        // Verifica que la caja asociada a la apertura también esté activa.
        if (!$aperturaCaja->caja || !$aperturaCaja->caja->activa) {
            return back()->withError('La caja asociada no está activa');
        }

        return view('ventas.create', [
            'pacientes' => $pacientes,
            'convenios' => $convenios,
            'productos' => $productos,
            'aperturaCaja' => $aperturaCaja,
        ]);
    }

    // ===================== GUARDAR =====================

    /**
     * Guarda una nueva venta y descuenta los productos del inventario.
     */
    public function store(Request $request)
    {
        // Valida los datos enviados desde el formulario.
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'apertura_caja_id' => 'required|exists:aperturas_caja,id',
            'convenio_id' => 'nullable|exists:convenios,id',

            'detalles' => 'required|array|min:1',

            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.descuento_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        /*
         * La venta y el movimiento de inventario deben realizarse
         * dentro de una misma transacción.
         *
         * Si algo falla, Laravel revierte todos los cambios realizados.
         */
        try {
            $venta = DB::transaction(function () use ($validated) {

                // Busca el paciente seleccionado.
                $paciente = Paciente::findOrFail($validated['paciente_id']);

                // Busca la apertura de caja seleccionada.
                $aperturaCaja = AperturaCaja::with('caja')
                    ->where('id', $validated['apertura_caja_id'])
                    ->where('estado', 'abierta')
                    ->first();

                // Comprueba que la apertura realmente esté abierta.
                if (!$aperturaCaja) {
                    throw new \Exception('La apertura de caja no está abierta.');
                }

                // Comprueba que la caja exista y esté activa.
                if (!$aperturaCaja->caja || !$aperturaCaja->caja->activa) {
                    throw new \Exception('La caja asociada no está activa.');
                }

                /*
                 * Actualmente el modelo Caja no tiene una relación con
                 * UbicacionAlmacen.
                 *
                 * Por eso utilizamos una ubicación de almacén activa.
                 */
                $ubicacion = UbicacionAlmacen::where('activo', true)->first();

                // No se puede descontar inventario si no existe una ubicación activa.
                if (!$ubicacion) {
                    throw new \Exception(
                        'No existe una ubicación de almacén activa.'
                    );
                }

                // Crea la venta inicialmente como pendiente.
                $venta = Venta::create([
                    'numero_venta' => Venta::generarNumeroVenta(),
                    'paciente_id' => $paciente->id,
                    'usuario_id' => auth()->id(),
                    'apertura_caja_id' => $aperturaCaja->id,
                    'convenio_id' => $validated['convenio_id'] ?? null,
                    'fecha_venta' => now(),
                    'estado' => 'pendiente',
                ]);

                // Recorre todos los productos incluidos en la venta.
                foreach ($validated['detalles'] as $detalle) {

                    // Busca el producto.
                    $producto = Producto::findOrFail($detalle['producto_id']);

                    // Busca el lote.
                    $lote = Lote::findOrFail($detalle['lote_id']);

                    // Verifica que el producto esté activo.
                    if (!$producto->activo) {
                        throw new \Exception(
                            "El producto {$producto->nombre} no está activo."
                        );
                    }

                    // Verifica que el lote esté activo.
                    if ($lote->estado !== 'activo') {
                        throw new \Exception(
                            "El lote seleccionado para {$producto->nombre} no está activo."
                        );
                    }

                    /*
                     * Comprueba que el lote corresponda al producto seleccionado.
                     * Esto evita vender un producto utilizando un lote incorrecto.
                     */
                    if ((int) $lote->producto_id !== (int) $producto->id) {
                        throw new \Exception(
                            "El lote seleccionado no corresponde al producto {$producto->nombre}."
                        );
                    }

                    /*
                     * Obtiene el precio vigente del producto.
                     *
                     * El método getPrecioVigente() recibe el ID de una
                     * lista de precios, no el ID de un convenio.
                     *
                     * Por eso no se utiliza convenio_id como argumento.
                     */
                    $precio = $producto->getPrecioVigente();

                    // Verifica que exista un precio vigente.
                    if (!$precio) {
                        throw new \Exception(
                            "No hay precio vigente para {$producto->nombre}."
                        );
                    }

                    // Obtiene el descuento indicado, utilizando 0 como valor predeterminado.
                    $descuentoPct = $detalle['descuento_pct'] ?? 0;

                    // Calcula el subtotal antes de aplicar el IVA.
                    $subtotal = $precio->precio_neto
                        * $detalle['cantidad']
                        * (1 - ($descuentoPct / 100));

                    // Calcula el IVA del 19%.
                    $impuesto = $subtotal * 0.19;

                    // Calcula el total del detalle.
                    $total = $subtotal + $impuesto;

                    /*
                     * Crea el detalle de la venta.
                     *
                     * La columna impuesto ya existe en la base de datos,
                     * por lo que puede almacenarse directamente.
                     */
                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $producto->id,
                        'lote_id' => $lote->id,
                        'receta_detalle_id' => null,
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $precio->precio_neto,
                        'descuento_pct' => $descuentoPct,
                        'subtotal' => $subtotal,
                        'impuesto' => $impuesto,
                        'total' => $total,
                    ]);

                    /*
                     * Descuenta las unidades del inventario.
                     *
                     * Si no existe stock suficiente, registrarSalida()
                     * genera una excepción y la transacción completa
                     * de la venta será revertida.
                     */
                    Stock::registrarSalida(
                        $producto,
                        $lote,
                        $ubicacion,
                        $detalle['cantidad'],
                        auth()->id(),
                        "Venta #{$venta->numero_venta}",
                        'Salida de inventario por venta'
                    );
                }

                // Calcula los totales generales de la venta.
                $venta->calcularTotales();

                // Marca la venta como completada después de procesar todos sus detalles.
                $venta->update([
                    'estado' => 'completada',
                ]);

                // Devuelve la venta creada a la transacción.
                return $venta;
            });

            // Redirige al detalle de la venta después de completarla correctamente.
            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Venta procesada correctamente.');

        } catch (\Throwable $e) {

            /*
             * Si ocurre cualquier error, la transacción se revierte
             * y se devuelve al formulario con el mensaje correspondiente.
             */
            return back()
                ->withInput()
                ->withError($e->getMessage());
        }
    }

    // ===================== VER =====================

    /**
     * Muestra la información de una venta.
     */
    public function show(Venta $venta)
    {
        // Carga las relaciones necesarias para mostrar el detalle.
        $venta->load(
            'paciente',
            'usuario',
            'detalles.producto',
            'pagos',
            'convenio'
        );

        return view('ventas.show', [
            'venta' => $venta,
        ]);
    }

    // ===================== ANULAR =====================

    /**
     * Anula una venta y devuelve los productos al inventario.
     */
    public function anular(Venta $venta, Request $request)
    {
        // Valida que se indique el motivo de la anulación.
        $request->validate([
            'motivo' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($venta, $request) {

                // Evita anular nuevamente una venta que ya fue anulada.
                if ($venta->estado === 'anulada') {
                    throw new \Exception('La venta ya se encuentra anulada.');
                }

                // Solo las ventas completadas pueden ser anuladas.
                if ($venta->estado !== 'completada') {
                    throw new \Exception(
                        'Solo se pueden anular ventas completadas.'
                    );
                }

                /*
                 * Obtiene una ubicación activa para devolver el stock.
                 *
                 * El esquema actual no guarda la ubicación de inventario
                 * utilizada originalmente en la venta.
                 */
                $ubicacion = UbicacionAlmacen::where('activo', true)->first();

                if (!$ubicacion) {
                    throw new \Exception(
                        'No existe una ubicación de almacén activa.'
                    );
                }

                // Carga los detalles de la venta.
                $venta->load('detalles.producto', 'detalles.lote');

                // Devuelve al inventario cada producto vendido.
                foreach ($venta->detalles as $detalle) {

                    // Verifica que existan producto y lote.
                    if (!$detalle->producto || !$detalle->lote) {
                        throw new \Exception(
                            'No se pudo encontrar el producto o lote de un detalle de la venta.'
                        );
                    }

                    /*
                     * Registra la entrada correspondiente a la anulación.
                     */
                    Stock::registrarEntrada(
                        $detalle->producto,
                        $detalle->lote,
                        $ubicacion,
                        $detalle->cantidad,
                        auth()->id(),
                        "Anulación #{$venta->numero_venta}",
                        $request->input('motivo')
                    );
                }

                // Cambia el estado de la venta a anulada.
                $venta->update([
                    'estado' => 'anulada',
                ]);
            });

            return redirect()
                ->route('ventas.show', $venta)
                ->with('success', 'Venta anulada correctamente.');

        } catch (\Throwable $e) {

            // Si ocurre un error, no se modifican los datos de la venta.
            return back()
                ->withError($e->getMessage());
        }
    }

    // ===================== REPORTE DIARIO =====================

    /**
     * Muestra las ventas completadas del día actual.
     */
    public function reporteDiario()
    {
        // Obtiene las ventas completadas realizadas durante el día.
        $ventas = Venta::whereDate('fecha_venta', today())
            ->completadas()
            ->with('paciente', 'usuario', 'detalles.producto')
            ->get();

        // Calcula el monto total vendido durante el día.
        $totalVentas = $ventas->sum('total');

        // Calcula la cantidad de ventas realizadas.
        $cantidadVentas = $ventas->count();

        return view('ventas.reporte-diario', [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'cantidadVentas' => $cantidadVentas,
        ]);
    }
}