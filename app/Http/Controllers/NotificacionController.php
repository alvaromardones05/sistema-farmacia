<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificacionController extends Controller
{
    /**
     * Cantidad de no leídas y las últimas 10, para la campana del menú.
     */
    public function index(): JsonResponse
    {
        $usuario = auth()->user();

        return response()->json([
            'no_leidas' => $usuario->unreadNotifications()->count(),
            'items' => $usuario->notifications()
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($notificacion) => [
                    'id' => $notificacion->id,
                    'titulo' => $notificacion->data['titulo'] ?? '',
                    'mensaje' => $notificacion->data['mensaje'] ?? '',
                    'icono' => $notificacion->data['icono'] ?? 'bi-bell',
                    'leida' => $notificacion->read_at !== null,
                    'fecha' => $notificacion->created_at->diffForHumans(),
                    'url' => route('notificaciones.abrir', $notificacion->id),
                ]),
        ]);
    }

    /**
     * Marca la notificación como leída y lleva al usuario a donde apunta.
     */
    public function abrir(string $id): RedirectResponse
    {
        $notificacion = auth()->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        $destino = $notificacion->data['url'] ?? null;

        return redirect($destino ?: url()->previous());
    }

    public function leerTodas(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
