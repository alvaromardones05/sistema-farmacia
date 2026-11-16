<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Recepción</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .header { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
        .top-info { width: 100%; margin-bottom: 15px; }
        .top-info td { padding: 3px; }
        .line { border-bottom: 1px solid #000; display: inline-block; min-width: 150px; }
        .table-products { width: 100%; border-collapse: collapse; }
        .table-products th, .table-products td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        .table-products th { font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">Acta de recepción de medicamentos y dispositivos médicos</div>

    <table class="top-info">
        <tr>
            <td width="50%">Proveedor: <span class="line">{{ $orden->proveedor->nombre_razon_social }}</span></td>
            <td width="50%">Guía de despacho N°: <span class="line">____________________</span></td>
        </tr>
        <tr>
            <td>Fecha de guía de despacho: <span class="line">____________________</span></td>
            <td>Fecha de recibido: <span class="line">____________________</span></td>
        </tr>
        <tr>
            <td>Transportista: <span class="line">_____________________</span></td>
            <td>N° orden de compra: <span class="line">{{ $orden->numero_orden }}</span></td>
        </tr>
    </table>

    <table class="table-products">
        <thead>
            <tr>
                <th width="4%">Frío<br>Si/No</th>
                <th width="10%">Código de barras</th>
                <th width="18%">Nombre genérico</th>
                <th width="12%">Marca comercial</th>
                <th width="10%">Lote del medicamento</th>
                <th width="10%">Fecha de vencimiento</th>
                <th width="8%">Registro sanitario</th>
                <th width="8%">Cantidad solicitada</th>
                <th width="8%">Cantidad recibida</th>
                <th width="4%">A/R</th>
                <th width="8%">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $detalle)
                <tr>
                    <td style="text-align:center;">{{ $detalle->producto->condicion_almacenamiento === 'refrigerado' ? 'Sí' : 'No' }}</td>
                    <td>{{ $detalle->producto->codigo_barra ?? '—' }}</td>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>—</td>
                    <td></td>
                    <td></td>
                    <td>{{ $detalle->producto->registro_isp ?? '—' }}</td>
                    <td style="text-align:center; font-weight:bold;">{{ $detalle->cantidad_solicitada }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
            @for($i = 0; $i < 5; $i++)
                <tr>
                    <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div style="margin-top: 40px; font-size: 11px;">
        Firma y nombre de quien recibe: _________________________________________________
    </div>
</body>
</html>