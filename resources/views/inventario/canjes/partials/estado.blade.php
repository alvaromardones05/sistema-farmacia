@php
    $mapaEstados = [
        'pendiente'  => ['warning text-dark', 'Pendiente'],
        'aprobado'   => ['primary', 'Aprobado'],
        'en_proceso' => ['info text-dark', 'En proceso'],
        'rechazado'  => ['danger', 'Rechazado'],
        'completado' => ['success', 'Completado'],
    ];
    [$claseEstado, $textoEstado] = $mapaEstados[$estado] ?? ['secondary', ucfirst($estado)];
@endphp
<span class="badge bg-{{ $claseEstado }}">{{ $textoEstado }}</span>
