<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Importante: interfaz para transmitir
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SesionCerrada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $usuarioId;

    /**
     * El constructor recibe el ID del usuario que cambió su clave.
     */
    public function __construct($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }

    /**
     * Transmitimos por un canal público para que todas las pestañas
     * abiertas puedan escuchar el aviso de cierre.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('condominio-canal'),
        ];
    }

  
    public function broadcastAs(): string
    {
        return 'sesion.cerrada';
    }
}
