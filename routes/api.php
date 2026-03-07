<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notificacion;
use App\Models\Usuario;
use App\Models\Encuesta;
use App\Models\HistorialVoto;
use App\Http\Controllers\AuthController;


// IMPORTANTE: Asegúrate de importar los eventos para que funcionen
use App\Events\NuevaNotificacion;
use App\Events\VotoActualizado;

// ==========================================
// 1. RUTAS PÚBLICAS (No necesitan Token)
// ==========================================

// Login: Genera el Token de Sanctum
Route::post('/login', [AuthController::class, 'login']);

// Registro opcional
Route::post('/registro', [AuthController::class, 'registro']);

// Rutas de Recuperación de Contraseña
Route::post('/password/enviar-codigo', [AuthController::class, 'enviarCodigo']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Finalizar registro de vecinos invitados
Route::post('/vecinos/finalizar-registro', [AuthController::class, 'finalizarRegistro']);

// ==========================================
// 2. RUTAS PROTEGIDAS (Middleware Sanctum)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // --- SEGURIDAD Y PERFIL ---

    // Ruta para que el admin registre vecinos
    Route::post('/registrar-vecino', [AuthController::class, 'registrarVecino']);

    // Ruta para el cambio de contraseña personal
    Route::post('/actualizar-password', [AuthController::class, 'actualizarPassword']);

    // Logout: Invalida el token actual
    Route::post('/logout', [AuthController::class, 'logout']);


    // --- RUTAS DE NOTIFICACIONES (ANUNCIOS) ---

    // Obtener los últimos 10 anuncios
    Route::get('/notificaciones', function () {
        $notificaciones = Notificacion::orderBy('created_at', 'desc')->take(10)->get();
        return response()->json($notificaciones);
    });

    // Crear un nuevo anuncio (dispara evento en tiempo real)
    Route::post('/notificaciones', function (Request $request) {
        $notificacion = Notificacion::create(['mensaje' => $request->mensaje]);

        // Se dispara el evento que React escucha como '.aviso-creado'
        event(new NuevaNotificacion($notificacion));

        return response()->json(['exito' => true, 'notificacion' => $notificacion]);
    });


    // --- RUTAS DE ENCUESTAS ---

    // Listado de encuestas
    Route::get('/encuestas', function () {
        $encuestas = Encuesta::orderBy('created_at', 'desc')->get();
        return response()->json($encuestas);
    });

    // Crear nueva encuesta
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

    // Registrar un voto
    Route::post('/encuestas/{id}/votar', function (Request $request, $id) {
        $usuarioId = $request->user()->id;

        // Verificar si ya votó
        $yaVoto = HistorialVoto::where('usuario_id', $usuarioId)
                               ->where('encuesta_id', $id)
                               ->first();

        if ($yaVoto) {
            return response()->json(['exito' => false, 'mensaje' => 'Ya votaste en esta encuesta'], 400);
        }

        // Crear registro de participación
        HistorialVoto::create([
            'usuario_id' => $usuarioId,
            'encuesta_id' => $id
        ]);

        $encuesta = Encuesta::find($id);
        $encuesta->increment($request->columna); // incrementa votos_a o votos_b

        // Notificar a todos que los resultados cambiaron
        event(new VotoActualizado($encuesta));

        return response()->json(['exito' => true, 'encuesta' => $encuesta]);
    });


    // --- RUTAS DE ADMINISTRACIÓN DE ENCUESTAS ---

    // Eliminar una encuesta
    Route::delete('/encuestas/{id}', function ($id) {
        Encuesta::destroy($id);
        return response()->json(['exito' => true]);
    });

    // Limpiar votos (Reiniciar encuesta a 0)
    Route::post('/encuestas/{id}/limpiar', function ($id) {
        $encuesta = Encuesta::find($id);
        if (!$encuesta) return response()->json(['exito' => false, 'mensaje' => 'No encontrada'], 404);

        $encuesta->update(['votos_a' => 0, 'votos_b' => 0]);

        // IMPORTANTE: Borrar historial para que los usuarios puedan votar de nuevo
        HistorialVoto::where('encuesta_id', $id)->delete();

        // Notificar reinicio en tiempo real
        event(new VotoActualizado($encuesta));

        return response()->json(['exito' => true]);
    });
});
