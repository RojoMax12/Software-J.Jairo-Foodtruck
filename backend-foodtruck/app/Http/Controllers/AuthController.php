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
                'telefono' => $user->telefono,
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

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'apellido' => 'sometimes|nullable|string|max:150',
            'telefono' => 'sometimes|nullable|string|max:30',
            'correo' => 'sometimes|email|unique:usuarios,correo,' . $user->id_usuario . ',id_usuario',
            'correo_electronico' => 'sometimes|nullable|email',
            'password_actual' => 'nullable|string',
            'nueva_password' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()
            ]
        ], [
            'correo.unique' => 'El correo electrónico ya se encuentra registrado por otro usuario.',
            'nueva_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        // Si desea cambiar la contraseña, validar la contraseña actual
        if (!empty($validated['nueva_password'])) {
            if (empty($validated['password_actual'])) {
                return response()->json([
                    'message' => 'Debes ingresar tu contraseña actual para poder cambiarla.'
                ], 422);
            }

            if (!Hash::check($validated['password_actual'], $user->contrasena)) {
                return response()->json([
                    'message' => 'La contraseña actual ingresada es incorrecta.'
                ], 422);
            }

            $user->contrasena = Hash::make($validated['nueva_password']);
        }

        if (isset($validated['nombre'])) {
            $user->nombre = $validated['nombre'];
        }
        if (array_key_exists('apellido', $validated)) {
            $user->apellido = $validated['apellido'];
        }
        if (array_key_exists('telefono', $validated)) {
            $user->telefono = $validated['telefono'];
        }
        if (isset($validated['correo'])) {
            $user->correo = $validated['correo'];
            $user->correo_electronico = $validated['correo'];
        } elseif (isset($validated['correo_electronico'])) {
            $user->correo = $validated['correo_electronico'];
            $user->correo_electronico = $validated['correo_electronico'];
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tus datos se han actualizado correctamente.',
            'user' => [
                'id' => $user->id_usuario,
                'id_usuario' => $user->id_usuario,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido ?? '',
                'correo' => $user->correo,
                'correo_electronico' => $user->correo,
                'telefono' => $user->telefono,
                'id_rol' => $user->id_rol,
            ]
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

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'sometimes|nullable|string|max:150',
            'nombre_empresa' => 'sometimes|nullable|string|max:150',
            'correo_electronico' => 'sometimes|nullable|email',
            'correo' => 'sometimes|nullable|email',
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_#\-+=~`^()])[A-Za-z\d@$!%*?&_#\-+=~`^()]{8,}$/'
            ],
            'id_rol' => 'sometimes|integer',
        ], [
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contrasena.regex' => 'La contraseña debe contener al menos una letra mayúscula, un número y un carácter especial (@, $, !, %, *, #, etc.).',
        ]);

        $email = $request->input('correo_electronico') ?? $request->input('correo');
        $nombre = $request->input('nombre_empresa') ?? $request->input('nombre') ?? explode('@', (string) $email)[0];
        $telefono = $request->input('telefono');
        $idRol = (int) ($request->input('id_rol') ?? 2); // 2 = Cliente

        if (empty($email)) {
            return response()->json(['message' => 'El correo electrónico es requerido.'], 422);
        }

        if (\App\Models\Usuario::where('correo', $email)->exists()) {
            return response()->json(['message' => 'El correo electrónico ya se encuentra registrado.'], 422);
        }

        $usuario = \App\Models\Usuario::create([
            'nombre' => $nombre,
            'correo' => $email,
            'telefono' => $telefono,
            'contrasena' => Hash::make($request->input('contrasena')),
            'id_rol' => $idRol,
            'estado' => true,
        ]);

        $tokenPayload = $this->jwtService->issueForUser($usuario);
        $token = is_array($tokenPayload) ? ($tokenPayload['access_token'] ?? $tokenPayload['token'] ?? null) : $tokenPayload;

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'access_token' => $token,
            'token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $usuario->id_usuario,
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'telefono' => $usuario->telefono,
                'id_rol' => $usuario->id_rol,
            ]
        ], 201);
    }
}
