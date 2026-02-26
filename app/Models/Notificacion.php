<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    // para guardar el mensaje
    protected $fillable = ['mensaje'];
}
