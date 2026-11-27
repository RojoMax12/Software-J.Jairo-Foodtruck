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
        if (in_array((int)$id, [1, 2, 3], true)) {
            return response()->json(['message' => 'Los roles base del sistema no pueden modificarse.'], 422);
        }

        $data = $request->validate([
            'nombre_rol' => 'sometimes|required|string|max:80',
        ]);
        return response()->json($this->rolService->updateRole($id, $data));
    }

    public function destroy($id)
    {
        if (in_array((int)$id, [1, 2, 3], true)) {
            return response()->json(['message' => 'Los roles base del sistema no pueden eliminarse.'], 422);
        }

        $this->rolService->deleteRole($id);
        return response()->json(null, 204);
    }
}
