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
        $data = $request->validate([
            'id_usuario' => 'nullable|integer',
            'fecha_apertura' => 'nullable|date',
            'monto_inicial' => 'nullable|numeric|min:0',
            'total_ventas' => 'nullable|numeric|min:0',
            'total_recaudado' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string|in:abierta,cerrada',
        ]);

        $user = $request->user();
        if (empty($data['id_usuario']) && $user) {
            $data['id_usuario'] = $user->id_usuario;
        } elseif (empty($data['id_usuario'])) {
            $data['id_usuario'] = 1;
        }
        if (empty($data['fecha_apertura'])) {
            $data['fecha_apertura'] = now()->format('Y-m-d H:i:s');
        } else {
            $data['fecha_apertura'] = \Carbon\Carbon::parse($data['fecha_apertura'])->timezone(config('app.timezone', 'America/Santiago'))->format('Y-m-d H:i:s');
        }
        if (!isset($data['total_ventas'])) {
            $data['total_ventas'] = 0;
        }
        if (!isset($data['total_recaudado'])) {
            $data['total_recaudado'] = 0;
        }
        if (!isset($data['estado'])) {
            $data['estado'] = 'abierta';
        }
        return response()->json($this->cajaService->createCaja($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'fecha_cierre' => 'nullable|date',
            'total_ventas' => 'nullable|numeric|min:0',
            'total_recaudado' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string|in:abierta,cerrada',
            'diferencia' => 'nullable|numeric',
        ]);

        if (isset($data['total_recaudado']) && empty($data['fecha_cierre'])) {
            $data['fecha_cierre'] = now()->format('Y-m-d H:i:s');
            $data['estado'] = 'cerrada';
        } elseif (!empty($data['fecha_cierre'])) {
            $data['fecha_cierre'] = \Carbon\Carbon::parse($data['fecha_cierre'])->timezone(config('app.timezone', 'America/Santiago'))->format('Y-m-d H:i:s');
        }
        return response()->json($this->cajaService->updateCaja($id, $data));
    }

    public function destroy($id)
    {
        $this->cajaService->deleteCajaById($id);
        return response()->json(null, 204);
    }
}
