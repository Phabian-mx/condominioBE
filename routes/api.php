<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notificacion;
use App\Models\Usuario;
use App\Models\Encuesta;
use App\Models\HistorialVoto;
use App\Http\Controllers\AuthController;

// ==========================================
// 1. RUTAS PÚBLICAS (No necesitan Token)
// ==========================================

// Login: Genera el Token de Sanctum
Route::post('/login', [AuthController::class, 'login']);

// Registro opcional (si permites que se den de alta solos)
Route::post('/registro', [AuthController::class, 'registro']);

// api.php
Route::post('/vecinos/finalizar-registro', [AuthController::class, 'finalizarRegistro']);

// ==========================================
// 2. RUTAS PROTEGIDAS (Middleware Sanctum)
// ==========================================
// Todo lo que esté aquí adentro requiere el encabezado: Authorization: Bearer {token}
Route::middleware('auth:sanctum')->group(function () {

    // --- RUTA PARA QUE EL ADMIN REGISTRE VECINOS ---
  
    Route::post('/registrar-vecino', [AuthController::class, 'registrarVecino']);

    // --- RUTAS DE NOTIFICACIONES ---
    Route::get('/notificaciones', function () {
        $notificaciones = Notificacion::orderBy('created_at', 'desc')->take(10)->get();
        return response()->json($notificaciones);
    });

    Route::post('/notificaciones', function (Request $request) {
        $notificacion = Notificacion::create(['mensaje' => $request->mensaje]);
        event(new \App\Events\NuevaNotificacion($notificacion));
        return response()->json(['exito' => true, 'notificacion' => $notificacion]);
    });

    // --- RUTAS DE ENCUESTAS ---
    Route::get('/encuestas', function () {
        $encuestas = Encuesta::orderBy('created_at', 'desc')->get();
        return response()->json($encuestas);
    });

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

    Route::post('/encuestas/{id}/votar', function (Request $request, $id) {
        // Al estar protegido, sacamos el usuario directamente del Token
        $usuarioId = $request->user()->id;

        $yaVoto = HistorialVoto::where('usuario_id', $usuarioId)
                               ->where('encuesta_id', $id)
                               ->first();

        if ($yaVoto) {
            return response()->json(['exito' => false, 'mensaje' => 'Ya votaste en esta encuesta'], 400);
        }

        HistorialVoto::create([
            'usuario_id' => $usuarioId,
            'encuesta_id' => $id
        ]);

        $encuesta = Encuesta::find($id);
        $encuesta->increment($request->columna);
        event(new \App\Events\VotoActualizado($encuesta));

        return response()->json(['exito' => true, 'encuesta' => $encuesta]);
    });

    // --- RUTAS DE ADMINISTRACIÓN ---
    Route::delete('/encuestas/{id}', function ($id) {
        Encuesta::destroy($id);
        return response()->json(['exito' => true]);
    });

    Route::post('/encuestas/{id}/limpiar', function ($id) {
        $encuesta = Encuesta::find($id);
        $encuesta->update(['votos_a' => 0, 'votos_b' => 0]);
        HistorialVoto::where('encuesta_id', $id)->delete();
        event(new \App\Events\VotoActualizado($encuesta));
        return response()->json(['exito' => true]);
    });

    // Logout: Para invalidar el token al salir
    Route::post('/logout', [AuthController::class, 'logout']);
});
