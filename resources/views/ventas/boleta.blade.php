<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Boleta {{ $venta->documentoTributario->folio }}
    </title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #222;
            margin: 0;
            padding: 30px;
        }

        .boleta {
            max-width: 720px;
            margin: auto;
            background: white;
            padding: 32px;
            border: 1px solid #ddd;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .muted {
            color: #666;
        }

        .simulado {
            margin: 20px 0;
            padding: 10px;
            border: 1px dashed #777;
            text-align: center;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th,
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .totales {
            margin-top: 25px;
            margin-left: auto;
            width: 320px;
        }

        .totales div {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #222;
            padding-top: 10px !important;
        }

        .acciones {
            max-width: 720px;
            margin: 20px auto;
            text-align: center;
        }

        button {
            padding: 10px 20px;
            cursor: pointer;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .boleta {
                border: none;
            }

            .acciones {
                display: none;
            }
        }
    </style>

</head>

<body>

@php

    $documento = $venta->documentoTributario;
    $pago = $venta->pagos->first();

    $metodosPago = [
        'efectivo' => 'Efectivo',
        'debito' => 'Débito',
        'credito' => 'Crédito',
        'transferencia' => 'Transferencia',
        'cheque' => 'Cheque',
    ];

@endphp

<div class="boleta">

    <div class="text-center">

        <h2>
            {{ config('app.name', 'Sistema Farmacia') }}
        </h2>

        <strong>
            BOLETA ELECTRÓNICA
        </strong>

        <div class="muted">
            Folio {{ $documento->folio }}
        </div>

    </div>

    <div class="simulado">
        DOCUMENTO SIMULADO — SIN VALIDEZ TRIBUTARIA
    </div>

    @if ($venta->estado === 'anulada')

        <div class="simulado">
            VENTA ANULADA
        </div>

    @endif

    <p>
        <strong>Venta:</strong>
        {{ $venta->numero_venta }}
    </p>

    <p>
        <strong>Fecha:</strong>
        {{ $documento->fecha_emision->format('d/m/Y H:i') }}
    </p>

    <p>
        <strong>Cliente:</strong>

        @if ($venta->paciente)
            {{ $venta->paciente->nombreCompleto() }}
        @else
            Consumidor final
        @endif
    </p>

    <<p>
        <strong>Vendedor:</strong>
        {{ $venta->usuario?->name }} {{ $venta->usuario?->apellidos }}
    </p>

    <table>

        <thead>

            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio</th>
                <th>Total</th>
            </tr>

        </thead>

        <tbody>

            @foreach ($venta->detalles as $detalle)

                <tr>

                    <td>
                        {{ $detalle->producto?->nombre }}
                    </td>

                    <td>
                        {{ $detalle->cantidad }}
                    </td>

                    <td>
                        ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}
                    </td>

                    <td>
                        ${{ number_format($detalle->total, 0, ',', '.') }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

    <div class="totales">

        <div>
            <span>Subtotal:</span>
            <span>
                ${{ number_format($venta->subtotal, 0, ',', '.') }}
            </span>
        </div>

        <div>
            <span>Descuento:</span>
            <span>
                ${{ number_format($venta->descuento, 0, ',', '.') }}
            </span>
        </div>

        <div>
            <span>IVA 19%:</span>
            <span>
                ${{ number_format($venta->impuesto, 0, ',', '.') }}
            </span>
        </div>

        <div class="total">
            <span>Total:</span>
            <span>
                ${{ number_format($venta->total, 0, ',', '.') }}
            </span>
        </div>

    </div>

    <hr>

    <p>
        <strong>Método de pago:</strong>
        {{ $metodosPago[$pago?->metodo_pago] ?? 'No informado' }}
    </p>

    @if ($pago?->referencia_transaccion)

        <p>
            <strong>Referencia:</strong>
            {{ $pago->referencia_transaccion }}
        </p>

    @endif

    <p class="muted text-center">
        Documento generado por el sistema académico de farmacia.
        No existe conexión real con el SII.
    </p>

</div>

<div class="acciones">

    <button onclick="window.print()">
        Imprimir / Guardar PDF
    </button>

</div>

</body>

</html>