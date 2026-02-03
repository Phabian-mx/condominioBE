<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ⚠️ IMPORTANTE: 'implements ShouldBroadcast'
class NuevaAsamblea implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;
    public $fecha;

    // Recibimos los datos al crear el evento
    public function __construct($mensaje, $fecha)
    {
        $this->mensaje = $mensaje;
        $this->fecha = $fecha;
    }

    public function broadcastOn()
    {
        // Canal público 'comunidad' (todos escuchan)
        return new Channel('comunidad');
    }

    public function broadcastAs()
    {
        return 'asamblea.creada';
    }
}
