<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function index()
    {
        return response()->json($this->usuarioService->getAllUsuarios());
    }

    public function show($id)
    {
        return response()->json($this->usuarioService->getUsuarioById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required',
            'correo' => 'required|email',
            'id_rol' => 'required',
            'contrasena' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        return response()->json(
            $this->usuarioService->createUsuario($data),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'correo' => 'sometimes|email',
            'id_rol' => 'sometimes|integer',
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
