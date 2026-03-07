<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;   
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use App\Events\SesionCerrada;
use App\Mail\RecuperarPasswordMail;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'clave' => 'required',
            // Recibimos el nombre del dispositivo (ej: "Chrome en Windows")
            'nombre_dispositivo' => 'nullable|string'
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->clave, $usuario->clave)) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Correo o contraseña incorrectos'
            ], 401);
        }

        $deviceLabel = $request->nombre_dispositivo ?? 'Dispositivo Desconocido';
        $token = $usuario->createToken($deviceLabel)->plainTextToken;

        return response()->json([
            'exito' => true,
            'token' => $token,
            'usuario' => $usuario
        ]);
    }

/**
     * --- 🔑 NUEVAS FUNCIONES DE RECUPERACIÓN ---
     */

    // 1. Enviar el código de 6 dígitos
    public function enviarCodigo(Request $request) {
        $request->validate(['email' => 'required|email']);

        $usuario = Usuario::where('email', $request->email)->first();
        if (!$usuario) {
            return response()->json(['exito' => false, 'mensaje' => 'El correo no está registrado'], 404);
        }

        $codigo = rand(100000, 999999);

        // Guardar o actualizar el código en la tabla auxiliar
        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $request->email],
            ['code' => $codigo, 'created_at' => now()]
        );

        // Enviar el correo usando tu configuración de Gmail
        Mail::to($request->email)->send(new RecuperarPasswordMail($codigo));

        return response()->json(['exito' => true, 'mensaje' => 'Código enviado a tu correo']);
    }

    // 2. Validar si el código es correcto antes de mostrar el formulario de nueva clave
    public function validarCodigo(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $registro = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$registro || now()->diffInMinutes($registro->created_at) > 15) {
            return response()->json(['exito' => false, 'mensaje' => 'Código inválido o expirado'], 422);
        }

        return response()->json(['exito' => true, 'mensaje' => 'Código verificado']);
    }

    // 3. Establecer la nueva contraseña
    public function resetPassword(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
            'nueva_clave' => ['required', 'confirmed', Password::min(6)],
        ]);

        $registro = DB::table('password_reset_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$registro) {
            return response()->json(['exito' => false, 'mensaje' => 'Solicitud no válida'], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        // Actualizamos usando tu columna 'clave'
        $usuario->clave = Hash::make($request->nueva_clave);
        $usuario->save();

        // Limpieza: borrar el código y cerrar sesiones previas por seguridad
        DB::table('password_reset_codes')->where('email', $request->email)->delete();
        $usuario->tokens()->delete();
        event(new SesionCerrada($usuario->id));

        return response()->json(['exito' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
    }

    /**
     * CAMBIO DE CONTRASEÑA Y CIERRE GLOBAL
     */
    public function actualizarPassword(Request $request)
    {
        $request->validate([
            'clave_actual' => 'required',
            'nueva_clave' => ['required', 'confirmed', Password::min(6)],
        ]);

        $usuario = $request->user();

        // --- NUEVO: Guardamos el ID antes de borrar nada ---
        $usuarioId = $usuario->id;

        // Verificar que la clave actual sea correcta
        if (!Hash::check($request->clave_actual, $usuario->clave)) {
            return response()->json(['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta'], 403);
        }

        // Actualizar la contraseña
        $usuario->clave = Hash::make($request->nueva_clave);
        $usuario->save();

        /**
         * REGLA: Al cambiar contraseña, cerramos sesión en TODOS los dispositivos.
       */
        $usuario->tokens()->delete();

        // --- NUEVO: Avisamos a React por WebSockets que debe cerrar las pestañas ---
        event(new SesionCerrada($usuarioId));

        return response()->json([
            'exito' => true,
            'mensaje' => 'Contraseña actualizada. Se han cerrado todas las sesiones activas.'
        ]);
    }

    public function registrarVecino(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:usuarios,email',
        ]);

        if (!$request->user()->es_admin) {
            return response()->json(['error' => 'Acceso denegado. Solo administradores.'], 403);
        }

        $vecino = Usuario::create([
            'nombre' => $request->nombre,
            'email'  => $request->email,
            'clave'  => Hash::make('condominio2026'),
            'es_admin' => false
        ]);

        event(new Registered($vecino));

        return response()->json([
            'exito' => true,
            'mensaje' => 'Vecino creado. Correo de validación enviado.'
        ]);
    }

    public function finalizarRegistro(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'password' => 'required|min:6'
        ]);

        $usuario = Usuario::find($request->id);

        if($usuario) {
            $usuario->clave = Hash::make($request->password);
            $usuario->save();

            // Opcional: Podrías revocar tokens aquí también si quieres asegurar limpieza inicial
            $usuario->tokens()->delete();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Contraseña establecida correctamente'
            ]);
        }

        return response()->json(['exito' => false, 'mensaje' => 'Usuario no encontrado'], 404);
    }

    public function logout(Request $request)
    {
        // Borra solo el token actual (cierra sesión solo en este dispositivo)
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada en este dispositivo']);
    }
}
