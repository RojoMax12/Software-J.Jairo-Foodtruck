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
        return response()->json($this->rolService->getRolById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->rolService->createRol($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->rolService->updateRol($id, $data));
    }

    public function destroy($id)
    {
        $this->rolService->deleteRolById($id);
        return response()->json(null, 204);
    }
}
