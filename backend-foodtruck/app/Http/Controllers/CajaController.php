<?php

namespace App\Http\Controllers;

use App\Services\CajaService;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function index()
    {
        return response()->json($this->cajaService->getAllCajas());
    }

    public function show($id)
    {
        return response()->json($this->cajaService->getCajaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->cajaService->createCaja($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->cajaService->updateCaja($id, $data));
    }

    public function destroy($id)
    {
        $this->cajaService->deleteCajaById($id);
        return response()->json(null, 204);
    }
}
