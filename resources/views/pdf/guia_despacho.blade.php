<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Despacho</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .table-products { width: 100%; border-collapse: collapse; }
        .table-products th, .table-products td { border: 1px solid #999; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>GUÍA DE DESPACHO</h2>
        <p>Documento simulado emitido por el Proveedor</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Proveedor:</strong> {{ $orden->proveedor->nombre_razon_social }}</td>
            <td><strong>N° Orden Compra:</strong> {{ $orden->numero_orden }}</td>
        </tr>
        <tr>
            <td><strong>Destino:</strong> Farmacia Central</td>
            <td><strong>Fecha de Emisión:</strong> {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="table-products">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Código</th>
                <th>Cantidad Despachada</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>{{ $detalle->producto->codigo_interno ?? $detalle->producto->codigo_barra }}</td>
                    <td>{{ $detalle->cantidad_solicitada }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right;">
        <p>Recibido conforme (Firma Bodeguero)<br><br><br>___________________________</p>
    </div>
</body>
</html>