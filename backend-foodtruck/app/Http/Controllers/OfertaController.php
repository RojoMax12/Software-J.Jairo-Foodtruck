<?php

namespace App\Http\Controllers;

use App\Services\OfertaService;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    protected $ofertaService;

    public function __construct(OfertaService $ofertaService)
    {
        $this->ofertaService = $ofertaService;
    }

    public function index()
    {
        return response()->json($this->ofertaService->getAllOfertas());
    }

    public function show($id)
    {
        return response()->json($this->ofertaService->getOfertaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        return response()->json($this->ofertaService->createOferta($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        return response()->json($this->ofertaService->updateOferta($id, $data));
    }

    public function destroy($id)
    {
        $this->ofertaService->deleteOfertaById($id);
        return response()->json(null, 204);
    }
}
