<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Importante para envío inmediato
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaNotificacion implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificacion;

    /**
     * Se recibe la notificación recién creada en la base de datos
     */
    public function __construct($notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * El "Canal" por donde viajará el mensaje (Público en este caso)
     */
    public function broadcastOn()
    {
        return new Channel('condominio-canal');
    }

    /**
     * El "Nombre" exacto que React está escuchando con .listen('.aviso-creado', ...)
     */
    public function broadcastAs()
    {
        return 'aviso-creado';
    }

    /**
     * Datos adicionales que enviamos al Frontend
     */
    public function broadcastWith()
    {
        return [
            'notificacion' => [
                'id' => $this->notificacion->id,
                'mensaje' => $this->notificacion->mensaje,
                'created_at' => $this->notificacion->created_at,
            ],
        ];
    }
}
