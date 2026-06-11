<?php

namespace App\Http\Controllers;

use App\Services\Usuario_dicremeServices;
use Illuminate\Http\Request;

class Usuario_dicremeController extends Controller
{
    protected $usuarioDicremeServices;

    //Definición del constructor para inyectar la dependencia del servicio Usuario_dicremeServices
    public function __construct(Usuario_dicremeServices $usuarioDicremeServices)
    {
        $this->usuarioDicremeServices = $usuarioDicremeServices;
    }

    public function index()
    {
        return response()->json($this->usuarioDicremeServices->getAllUsuariosDicreme());
    }

    public function show($id)
    {
        return response()->json($this->usuarioDicremeServices->getUsuarioDicremeById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_usuario' => 'required|string|max:255',
            'correo_electronico' => 'required|string|email|max:255|unique:usuarios_dicreme',
            'contrasena' => 'required|string|min:8',
            'id_rol' => 'required|integer|exists:rol,id',
        ]);

        return response()->json($this->usuarioDicremeServices->createUsuarioDicreme($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_usuario' => 'sometimes|required|string|max:255',
            'correo_electronico' => 'sometimes|required|string|email|max:255|unique:usuarios_dicreme,correo_electronico,' . $id,
            'contrasena' => 'sometimes|required|string|min:8',
            'id_rol' => 'sometimes|required|integer|exists:rol,id',
        ]);

        return response()->json($this->usuarioDicremeServices->updateUsuarioDicreme($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->usuarioDicremeServices->deleteUsuarioDicreme($id), 204);
    }
}