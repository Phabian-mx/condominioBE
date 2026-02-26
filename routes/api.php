<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notificacion;
use App\Models\Usuario;
use App\Models\Encuesta;
use App\Models\HistorialVoto;

// ==========================================
// 1. RUTAS DE USUARIOS Y LOGIN
// ==========================================
Route::post('/login', function (Request $request) {
    $usuario = Usuario::where('nombre', $request->nombre)
                      ->where('clave', $request->clave)
                      ->first();

    if ($usuario) {
        return response()->json(['exito' => true, 'usuario' => $usuario]);
    } else {
        return response()->json(['exito' => false, 'mensaje' => 'Usuario o contraseña incorrectos'], 401);
    }
});

// ==========================================
// 2. RUTAS DE NOTIFICACIONES (AVISOS)
// ==========================================
Route::get('/notificaciones', function () {
    $notificaciones = Notificacion::orderBy('created_at', 'desc')->take(10)->get();
    return response()->json($notificaciones);
});

Route::post('/notificaciones', function (Illuminate\Http\Request $request) {
    $notificacion = Notificacion::create([
        'mensaje' => $request->mensaje
    ]);

    //  ENVÍA EL WEBSOCKET A TODOS LOS CLIENTES CONECTADOS
    event(new \App\Events\NuevaNotificacion($notificacion));

    return response()->json(['exito' => true, 'notificacion' => $notificacion]);
});

// ==========================================
// 3. RUTAS DE ENCUESTAS Y VOTACIONES
// ==========================================

// Leer todas las encuestas
Route::get('/encuestas', function () {
    $encuestas = Encuesta::orderBy('created_at', 'desc')->get();
    return response()->json($encuestas);
});

// Crear una nueva encuesta (ESTA ERA LA QUE FALTABA)
Route::post('/encuestas', function (Request $request) {
    $encuesta = Encuesta::create([
        'titulo' => $request->titulo,
        'descripcion' => $request->descripcion,
        'opcion_a' => $request->opcion_a,
        'opcion_b' => $request->opcion_b,
        'votos_a' => 0,
        'votos_b' => 0
    ]);
    return response()->json(['exito' => true, 'encuesta' => $encuesta]);
});

// Votar en una encuesta (TAMBIÉN FALTABA)
Route::post('/encuestas/{id}/votar', function (Request $request, $id) {
    $yaVoto = HistorialVoto::where('usuario_id', $request->usuario_id)
                           ->where('encuesta_id', $id)
                           ->first();

    if ($yaVoto) {
        return response()->json(['exito' => false, 'mensaje' => 'Ya votaste en esta encuesta'], 400);
    }

    HistorialVoto::create([
        'usuario_id' => $request->usuario_id,
        'encuesta_id' => $id
    ]);

    $encuesta = Encuesta::find($id);
    $encuesta->increment($request->columna);
event(new \App\Events\VotoActualizado($encuesta));
    return response()->json(['exito' => true, 'encuesta' => $encuesta]);
});

// Eliminar una encuesta
Route::delete('/encuestas/{id}', function ($id) {
    Encuesta::destroy($id);
    return response()->json(['exito' => true]);
});

// Limpiar los votos a cero
Route::post('/encuestas/{id}/limpiar', function ($id) {
    $encuesta = Encuesta::find($id);
    $encuesta->update(['votos_a' => 0, 'votos_b' => 0]);
    HistorialVoto::where('encuesta_id', $id)->delete();
    event(new \App\Events\VotoActualizado($encuesta));
    return response()->json(['exito' => true]);
});
