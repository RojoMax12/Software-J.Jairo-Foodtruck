<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\JwtService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    protected $authService;
    protected $jwtService;
    protected $usuarioService;

    public function __construct(AuthService $authService, JwtService $jwtService, UsuarioService $usuarioService)
    {
        $this->authService = $authService;
        $this->jwtService = $jwtService;
        $this->usuarioService = $usuarioService;
    }

    public function login(Request $request)
    {
        $loginInput = $request->input('correo_electronico') 
            ?? $request->input('correo') 
            ?? $request->input('nombre') 
            ?? $request->input('login');

        $contrasena = $request->input('contrasena') ?? $request->input('password');

        if (!$loginInput || !$contrasena) {
            return response()->json(['error' => 'Por favor, ingresa tu correo/usuario y contraseña.'], 422);
        }

        $user = \App\Models\Usuario::where('correo', $loginInput)
            ->orWhere('nombre', $loginInput)
            ->first();

        if (!$user || !Hash::check($contrasena, $user->contrasena)) {
            return response()->json(['error' => 'Credenciales inválidas. Revisa tu usuario/correo y contraseña.'], 401);
        }

        if (isset($user->estado) && !$user->estado) {
            return response()->json(['error' => 'Tu usuario se encuentra inactivo.'], 403);
        }

        $tokenPayload = $this->jwtService->issueForUser($user);
        $token = is_array($tokenPayload) ? ($tokenPayload['access_token'] ?? $tokenPayload['token'] ?? null) : $tokenPayload;

        return response()->json([
            'access_token' => $token,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => is_array($tokenPayload) ? ($tokenPayload['expires_in'] ?? 3600) : 3600,
            'user' => [
                'id' => $user->id_usuario,
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre,
                'correo' => $user->correo,
                'id_rol' => $user->id_rol,
            ]
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'claims' => $request->attributes->get('jwt_claims'),
        ]);
    }

    public function refresh(Request $request)
    {
        $claims = $request->attributes->get('jwt_claims');

        if (!$claims) {
            return response()->json(['message' => 'Token actual inválido.'], 401);
        }

        $this->jwtService->blacklist($claims);
        return response()->json($this->jwtService->issueForUser($request->user()));
    }

    public function logout(Request $request)
    {
        $claims = $request->attributes->get('jwt_claims');
        if ($claims) {
            $this->jwtService->blacklist($claims);
        }
        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
        ]);

        $status = $this->authService->enviarEnlaceRecuperacion(
            $request->correo
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Si el correo está registrado, recibirás un enlace de recuperación.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'correo' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = $this->authService->restablecerContrasena(
            $request->token,
            $request->correo,
            $request->password
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'error',
                'message' => __($status),
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña restablecida correctamente.',
        ]);
    }
}
