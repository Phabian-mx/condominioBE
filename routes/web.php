<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- RUTA PARA DISPARAR LA NOTIFICACIÓN ---
Route::get('/crear-asamblea', function (Request $request) {

    // 1. Recibimos el texto que viene desde React (via URL)
    // Si no escribiste nada, usa el texto por defecto.
    $texto = $request->query('mensaje', '¡Aviso General del Condominio!');

    // 2. Disparamos el evento con ESE texto y la hora actual
    event(new \App\Events\NuevaAsamblea($texto, now()->toTimeString()));

    // 3. Respuesta para saber que todo salió bien
    return "✅ Evento disparado con el mensaje: " . $texto;
});
