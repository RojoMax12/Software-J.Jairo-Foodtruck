<?php

namespace App\Http\Controllers;

use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    public function index()
    {
        return response()->json($this->ventaService->getAllVentas());
    }

    public function show($id)
    {
        return response()->json($this->ventaService->getVentaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->ventaService->createVenta($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->ventaService->updateVenta($id, $data));
    }

    public function destroy($id)
    {
        $this->ventaService->deleteVentaById($id);
        return response()->json(null, 204);
    }
}
