<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .table-products { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-products th, .table-products td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .table-products th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ORDEN DE COMPRA</h2>
        <h3>{{ $orden->numero_orden }}</h3>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Proveedor:</strong> {{ $orden->proveedor->nombre_razon_social }}</td>
            <td><strong>RUT:</strong> {{ $orden->proveedor->rut }}</td>
        </tr>
        <tr>
            <td><strong>Fecha Emisión:</strong> {{ $orden->fecha_orden->format('d/m/Y H:i') }}</td>
            <td><strong>Entrega Esperada:</strong> {{ $orden->fecha_entrega_esperada ? $orden->fecha_entrega_esperada->format('d/m/Y') : 'Sin definir' }}</td>
        </tr>
        <tr>
            <td><strong>Responsable:</strong> {{ $orden->usuario->name }}</td>
            <td></td>
        </tr>
    </table>

    <table class="table-products">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Código</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($orden->detalles as $detalle)
                @php
                    $subtotal = $detalle->cantidad_solicitada * $detalle->precio_unitario;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>{{ $detalle->producto->codigo_interno ?? $detalle->producto->codigo_barra }}</td>
                    <td class="text-right">{{ $detalle->cantidad_solicitada }}</td>
                    <td class="text-right">${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total Orden</th>
                <th class="text-right">${{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    @if($orden->observaciones)
        <div style="margin-top: 20px;">
            <strong>Observaciones:</strong><br>
            {{ $orden->observaciones }}
        </div>
    @endif

    <div style="margin-top: 60px; text-align: center;">
        <p>_________________________________<br>Firma y Timbre Farmacia</p>
    </div>
</body>
</html>
