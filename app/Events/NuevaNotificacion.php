<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaNotificacion implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificacion;

    public function __construct($notificacion)
    {
        $this->notificacion = $notificacion;
    }

    // El "Canal" por donde viajará el mensaje
    public function broadcastOn()
    {
        return new Channel('condominio-canal');
    }

    // El "Nombre" del evento que React va a escuchar
    public function broadcastAs()
    {
        return 'aviso-creado';
    }
}
