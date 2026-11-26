<?php

namespace App\Http\Controllers;

use App\Services\RolService;
use Illuminate\Http\Request;

class RolController extends Controller
{
    protected $rolService;

    public function __construct(RolService $rolService)
    {
        $this->rolService = $rolService;
    }

    public function index()
    {
        return response()->json($this->rolService->getAllRoles());
    }

    public function show($id)
    {
        return response()->json($this->rolService->getRoleById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_rol' => 'required|string|max:80',
        ]);
        return response()->json($this->rolService->createRole($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_rol' => 'sometimes|required|string|max:80',
        ]);
        return response()->json($this->rolService->updateRole($id, $data));
    }

    public function destroy($id)
    {
        $this->rolService->deleteRole($id);
        return response()->json(null, 204);
    }
}
