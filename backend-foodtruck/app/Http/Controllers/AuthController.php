<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\JwtService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $credentials = $request->validate([
            'nombre' => 'required|string',
            'contrasena' => 'required|string',
        ]);

        $user = $this->usuarioService->getUsuarioByNombre($credentials['nombre']);

        if (!$user || !Hash::check($credentials['contrasena'], $user->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }

        $tokenPayload = $this->jwtService->issueForUser($user);
        return response()->json($tokenPayload);
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
        $request->validate(['nombre' => 'required|string']);
        $resultado = $this->authService->enviarEnlaceRecuperacion($request->nombre);

        if (!$resultado) {
            return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado.'], 404);
        }

        return response()->json(['status' => 'success', 'message' => 'Enlace de recuperación enviado.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        try {
            $this->authService->restablecerContrasena($request->token, $request->password);
            return response()->json(['status' => 'success', 'message' => 'Contraseña restablecida.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
