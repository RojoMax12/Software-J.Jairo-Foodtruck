<?php

namespace App\Http\Controllers;
use App\Services\DespachoServices;
use Illuminate\Http\Request;

class Estado_pedidoController extends Controller
{
    protected $despachoServices;

    public function __construct(DespachoServices $despachoServices)
    {
        $this->despachoServices = $despachoServices;
    }

    public function index()
    {
        return response()->json($this->despachoServices->getAllDespachos());
    }

    public function show($id)
    {
        return response()->json($this->despachoServices->getDespachoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);
        return response()->json($this->despachoServices->createDespacho($data));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);
        return response()->json($this->despachoServices->updateDespacho($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->despachoServices->deleteDespacho($id));
    }
}