<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\Http\Request;

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
        $data = $request->all();
        return response()->json($this->usuarioService->createUsuario($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->usuarioService->updateUsuario($id, $data));
    }

    public function destroy($id)
    {
        $this->usuarioService->deleteUsuarioById($id);
        return response()->json(null, 204);
    }
}
