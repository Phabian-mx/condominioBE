<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VotoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $encuesta;

    public function __construct($encuesta)
    {
        // Enviamos la encuesta completa con los nuevos conteos
        $this->encuesta = $encuesta;
    }

    public function broadcastOn()
    {
        return new Channel('condominio-canal');
    }

    public function broadcastAs()
    {
        return 'voto-actualizado';
    }
}
