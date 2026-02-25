<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notificacion;
use App\Models\Usuario;
use App\Models\Encuesta;

// Ruta para el Login
Route::post('/login', function (Request $request) {
    // Buscamos al usuario que coincida con el nombre y la clave
    $usuario = Usuario::where('nombre', $request->nombre)
                      ->where('clave', $request->clave)
                      ->first();

    if ($usuario) {
        // Si existe, lo devolvemos
        return response()->json(['exito' => true, 'usuario' => $usuario]);
    } else {
        // Si no existe o la clave está mal, mandamos error
        return response()->json(['exito' => false, 'mensaje' => 'Usuario o contraseña incorrectos'], 401);
    }
});


Route::get('/notificaciones', function () {
    $notificaciones = Notificacion::orderBy('created_at', 'desc')
                                  ->take(10)
                                  ->get();

    return response()->json($notificaciones);
});


// Ruta para obtener las encuestas
Route::get('/encuestas', function () {
    $encuestas = Encuesta::orderBy('created_at', 'desc')->get();
    return response()->json($encuestas);
});
