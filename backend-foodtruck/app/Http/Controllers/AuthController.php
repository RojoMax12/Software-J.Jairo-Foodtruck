<?php

namespace App\Http\Controllers;

use App\Models\Usuario_dicreme;
use App\Models\Usuario_distribuidores;
use App\Services\JwtService;
use App\Services\AuthService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{   
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request, JwtService $jwtService): JsonResponse
    {
        // 1. Ya no exigimos 'tipo_usuario' desde el frontend
        $credentials = $request->validate([
            'correo_electronico' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        // 2. Intentamos resolver el usuario buscando primero en una tabla y luego en otra
        $user = null;
        $tipoUsuarioCalculado = 'distribuidor';

        // Primero buscamos en el personal interno de DiCreme
        $user = Usuario_dicreme::where('correo_electronico', $credentials['correo_electronico'])->first();
        
        if ($user) {
            $tipoUsuarioCalculado = 'dicreme';
        } else {
            // Si no se encuentra, buscamos en los distribuidores
            $user = Usuario_distribuidores::where('correo_electronico', $credentials['correo_electronico'])->first();
        }

        // 3. Validamos la existencia y la contraseña de PostgreSQL
        if (! $user || ! Hash::check($credentials['contrasena'], $user->contrasena)) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        // 4. Emitimos el JWT pasando el tipo de usuario detectado automáticamente
        return response()->json($jwtService->issueForUser(
            $user, 
            $this->tokenClaimsForUser($user, $tipoUsuarioCalculado)
        ));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'claims' => $request->attributes->get('jwt_claims'),
        ]);
    }

    public function refresh(Request $request, JwtService $jwtService): JsonResponse
    {
        $claims = $request->attributes->get('jwt_claims');

        if (! $claims) {
            return response()->json([
                'message' => 'No se pudieron leer los datos del token actual.',
            ], 401);
        }

        $jwtService->blacklist($claims);

        return response()->json($jwtService->issueForUser($request->user()));
    }

    public function logout(Request $request, JwtService $jwtService): JsonResponse
    {
        $claims = $request->attributes->get('jwt_claims');

        if ($claims) {
            $jwtService->blacklist($claims);
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    private function resolveUser(string $tipoUsuario, string $correoElectronico): Authenticatable|null
    {
        $tipo = Str::lower(trim($tipoUsuario));

        return match (true) {
            Str::contains($tipo, 'dicreme') => Usuario_dicreme::where('correo_electronico', $correoElectronico)->first(),
            Str::contains($tipo, 'distribuidor') => Usuario_distribuidores::where('correo_electronico', $correoElectronico)->first(),
            default => null,
        };
    }

    private function tokenClaimsForUser(Authenticatable $user, string $tipoUsuario): array
    {
        return [
            'user_type' => Str::contains(Str::lower($tipoUsuario), 'dicreme') ? 'dicreme' : 'distribuidor',
            'name' => $user instanceof Usuario_dicreme ? $user->nombre_usuario : ($user instanceof Usuario_distribuidores ? $user->nombre_empresa : null),
            'email' => $user->correo_electronico ?? null,
        ];
    }

    public function forgotPassword(Request $request)
    {
        $correo = $request->validate(['email' => 'required|email']);
        
        $resultado = $this->authService->enviarEnlaceRecuperacion($correo);

        if ($resultado == false) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No encontramos ningún usuario con ese correo.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Enlace de recuperación enviado con éxito.'
        ], 200);
    }

    // Endpoint 2: El que procesa el cambio final con la nueva clave
    public function resetPassword(Request $request)
    {
        // Validamos la entrada desde Vue
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed' // Requiere password_confirmation en el JSON
        ]);

        try {
            $this->authService->restablecerContrasena(
                $request->input('token'),
                $request->input('password')
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Tu contraseña ha sido restablecida correctamente. Ya puedes iniciar sesión.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400); // 400 Bad Request por Token inválido/expirado
        }
    }
}