<?php

namespace App\Http\Controllers;

use App\Services\RolServices;
use Illuminate\Http\Request;

/*Las direcciones de las peticiones HTTP van en route/api */
class RolController extends Controller
{
    protected $rolServices;

    public function __construct(RolServices $rolServices)
    {
        $this->rolServices = $rolServices;
    }

    public function index()
    {
        return response()->json($this->rolServices->getAllRoles());
    }

    public function show($id)
    {
        return response()->json($this->rolServices->getRoleById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_rol' => 'required|string|max:255',
        ]);

        return response()->json($this->rolServices->createRole($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_rol' => 'required|string|max:255',
        ]);

        return response()->json($this->rolServices->updateRole($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->rolServices->deleteRole($id), 204);
    }
}