<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Aviso genérico que se guarda en la tabla notifications y se muestra en la campana.
 * Sirve para canjes, recetas por aprobar y cualquier otro aviso futuro.
 */
class AvisoSistema extends Notification
{
    public function __construct(
        public string $titulo,
        public string $mensaje,
        public ?string $url = null,
        public string $icono = 'bi-bell',
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'url' => $this->url,
            'icono' => $this->icono,
        ];
    }

    /**
     * Avisa a todos los Químicos Farmacéuticos (opcionalmente excluyendo a quien originó la acción).
     * Nunca lanza excepción: si falla, lo registra y la operación principal sigue su curso.
     */
    public static function paraQuimicos(
        string $titulo,
        string $mensaje,
        ?string $url = null,
        string $icono = 'bi-bell',
        ?int $excluirUserId = null
    ): void {
        try {
            $destinatarios = User::role('Químico Farmacéutico')
                ->when($excluirUserId, fn($consulta) => $consulta->where('id', '!=', $excluirUserId))
                ->get();

            NotificationFacade::send($destinatarios, new self($titulo, $mensaje, $url, $icono));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Avisa a un usuario puntual. Nunca lanza excepción.
     */
    public static function para(
        ?User $usuario,
        string $titulo,
        string $mensaje,
        ?string $url = null,
        string $icono = 'bi-bell'
    ): void {
        try {
            $usuario?->notify(new self($titulo, $mensaje, $url, $icono));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
