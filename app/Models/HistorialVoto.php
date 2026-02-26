<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialVoto extends Model
{
    protected $table = 'historial_votos';
    // para guardar los votos
    protected $fillable = ['usuario_id', 'encuesta_id'];
}
