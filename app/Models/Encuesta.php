<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    // para guardar estos datos
    protected $fillable = ['titulo', 'descripcion', 'opcion_a', 'opcion_b', 'votos_a', 'votos_b'];
}
