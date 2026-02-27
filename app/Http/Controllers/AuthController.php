<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'clave' => 'required'
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->clave, $usuario->clave)) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Correo o contraseña incorrectos'
            ], 401);
        }

        $token = $usuario->createToken('token-condominio')->plainTextToken;

        return response()->json([
            'exito' => true,
            'token' => $token,
            'usuario' => $usuario
        ]);
    }

    /**
     * REGISTRO POR ADMIN: Envía correo de validación (SMTP)
     */
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
            'mensaje' => 'Vecino creado. Se ha enviado un correo de validación a: ' . $request->email
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

            return response()->json([
                'exito' => true,
                'mensaje' => 'Contraseña establecida correctamente'
            ]);
        }

        return response()->json(['exito' => false, 'mensaje' => 'Usuario no encontrado'], 404);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada y token eliminado']);
    }
}
