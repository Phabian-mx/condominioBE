<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', function () {
    return view('welcome');
});

// ==========================================================
//  RUTAS DE VALIDACIÓN (Para cuando el vecino haga clic)
// ==========================================================

// Esta ruta se activa cuando el vecino pulsa el botón del mail
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Marca al usuario como "Verificado" en la DB

    // Redirigimos a React con un mensaje de éxito
    return redirect('http://localhost:5173/login?verificado=1');
})->middleware(['signed'])->name('verification.verify');


// ==========================================================
//  EVENTOS DE NOTIFICACIÓN
// ==========================================================
Route::get('/crear-asamblea', function (Request $request) {
    $texto = $request->query('mensaje', '¡Aviso General del Condominio!');
    event(new \App\Events\NuevaAsamblea($texto, now()->toTimeString()));
    return "✅ Evento disparado con el mensaje: " . $texto;
});
