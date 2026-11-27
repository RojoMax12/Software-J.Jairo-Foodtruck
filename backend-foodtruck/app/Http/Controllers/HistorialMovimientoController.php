<?php

namespace App\Http\Controllers;

use App\Services\HistorialMovimientoService;
use Illuminate\Http\Request;

class HistorialMovimientoController extends Controller
{
    protected $service;

    public function __construct(HistorialMovimientoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $limit = max(1, min(500, (int) $request->query('limit', 200)));
        $tipo = $request->query('tipo');
        $search = $request->query('search');

        $movimientos = $this->service->getAllHistorialMovimientos($limit, $tipo, $search);
        return response()->json($movimientos);
    }

    public function show($id)
    {
        $mov = $this->service->getHistorialMovimientoById($id);
        if (!$mov) {
            return response()->json(['message' => 'Movimiento no encontrado'], 404);
        }
        return response()->json($mov);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:60',
            'accion' => 'required|string|max:60',
            'descripcion' => 'required|string|max:255',
            'entidad' => 'nullable|string|max:200',
            'detalle' => 'nullable|string|max:1000',
            'monto' => 'nullable|numeric',
            'fecha' => 'nullable|date',
        ]);
        $user = $request->user();

        if (empty($data['usuario']) && $user) {
            $data['usuario'] = $user->nombre . ' (' . ($user->id_rol === 1 ? 'Admin' : 'Personal') . ')';
        }
        if (empty($data['id_usuario']) && $user) {
            $data['id_usuario'] = $user->id_usuario;
        }

        if (empty($data['fecha'])) {
            $data['fecha'] = now()->format('Y-m-d H:i:s');
        }

        $mov = $this->service->createHistorialMovimiento($data);
        return response()->json($mov, 201);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Los registros de auditoría son inmutables y no pueden modificarse.'
        ], 403);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Los registros de auditoría son inmutables y no pueden eliminarse.'
        ], 403);
    }

    public function clear()
    {
        return response()->json([
            'message' => 'El vaciado masivo de la pista de auditoría se encuentra deshabilitado por directivas de seguridad e inmutabilidad.'
        ], 403);
    }
}
