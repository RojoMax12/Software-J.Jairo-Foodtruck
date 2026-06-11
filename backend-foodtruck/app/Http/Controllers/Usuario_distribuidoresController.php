<?php

namespace App\Http\Controllers;

use App\Services\Usuario_distribuidoresServices;
use Illuminate\Http\Request;

class Usuario_distribuidoresController extends Controller
{
    protected $usuarioDistribuidoresService;

    public function __construct(Usuario_distribuidoresServices $usuarioDistribuidoresService)
    {
        $this->usuarioDistribuidoresService = $usuarioDistribuidoresService;
    }

    public function index()
    {
        return response()->json($this->usuarioDistribuidoresService->getAllUsuariosDistribuidores());
    }

    public function show($id)
    {
        return response()->json($this->usuarioDistribuidoresService->getUsuarioDistribuidorById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'correo_electronico' => 'required|string|email|max:255|unique:usuarios_distribuidores',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'comuna' => 'required|string|max:255',
            'id_rol' => 'required|integer|exists:rol,id',
            'contrasena' => 'required|string|min:6',
            'rut_empresa' => 'required|string|max:255|unique:usuarios_distribuidores,rut_empresa',
            'nombre_empresa' => 'required|string|max:255',
        ]);

        return response()->json($this->usuarioDistribuidoresService->createUsuarioDistribuidor($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'correo_electronico' => 'required|string|email|max:255|unique:usuarios_distribuidores,correo_electronico,'.$id,
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'comuna' => 'required|string|max:255',
            'id_rol' => 'required|integer|exists:rol,id',
            'contrasena' => 'required|string|min:6',
            'rut_empresa' => 'required|string|max:255|unique:usuarios_distribuidores,rut_empresa,'.$id,
            'nombre_empresa' => 'required|string|max:255',
        ]);

        return response()->json($this->usuarioDistribuidoresService->updateUsuarioDistribuidor($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->usuarioDistribuidoresService->deleteUsuarioDistribuidor($id), 204);
    }
}