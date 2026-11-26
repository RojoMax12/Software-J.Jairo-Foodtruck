<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function index(Request $request)
    {
        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = max(1, min(200, (int) $request->query('per_page', 50)));
            return response()->json(Usuario::orderBy('id_usuario', 'desc')->paginate($perPage));
        }
        return response()->json($this->usuarioService->getAllUsuarios());
    }

    public function show($id)
    {
        $usuario = $this->usuarioService->getUsuarioById($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
        return response()->json($usuario);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => [
                'required',
                'string',
                'max:30',
                'regex:/^(?:\+?56\s?9\s?\d{4}\s?\d{4}|9\s?\d{4}\s?\d{4})$/'
            ],
            'correo' => 'bail|required|email|max:150|unique:usuarios,correo',
            'id_rol' => 'bail|required|integer|in:1,2,3|exists:roles,id_rol',
            'contrasena' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'correo.unique' => 'El correo electrónico ya está registrado.',
        ]);

        return response()->json(
            $this->usuarioService->createUsuario($data),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'telefono' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                'regex:/^(?:\+?56\s?9\s?\d{4}\s?\d{4}|9\s?\d{4}\s?\d{4})$/'
            ],
            'correo' => [
                'sometimes',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo')->ignore($id, 'id_usuario'),
            ],
            'id_rol' => 'sometimes|integer|in:1,2,3|exists:roles,id_rol',
            'contrasena' => [
                'sometimes',
                'nullable',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'estado' => 'sometimes|boolean',
        ]);

        return response()->json(
            $this->usuarioService->updateUsuario($id, $data)
        );
    }

    public function destroy($id)
    {
        $this->usuarioService->deleteUsuarioById($id);
        return response()->json(null, 204);
    }

    public function administrativos()
    {
        return response()->json(
            $this->usuarioService->getUsuariosAdministrativos()
        );
    }
}
