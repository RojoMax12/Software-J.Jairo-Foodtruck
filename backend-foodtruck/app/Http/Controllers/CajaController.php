<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Services\CajaService;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

    public function index(Request $request)
    {
        if ($request->has('page') || $request->boolean('paginate')) {
            $perPage = max(1, min(200, (int) $request->query('per_page', 50)));
            return response()->json(Caja::with(['usuario'])->orderBy('id_caja', 'asc')->paginate($perPage));
        }

        if ($request->filled('limit')) {
            $limit = max(1, min(500, (int) $request->query('limit')));
            return response()->json(Caja::with(['usuario'])->orderBy('id_caja', 'desc')->limit($limit)->get()->reverse()->values());
        }

        return response()->json($this->cajaService->getAllCajas());
    }

    public function show($id)
    {
        $caja = $this->cajaService->getCajaById($id);
        if (!$caja) {
            return response()->json(['message' => 'Caja no encontrada.'], 404);
        }
        return response()->json($caja);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => 'nullable|integer|exists:usuarios,id_usuario',
            'fecha_apertura' => 'nullable|date',
            'monto_inicial' => 'nullable|numeric|min:0',
            'total_ventas' => 'nullable|numeric|min:0',
            'total_recaudado' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string|in:abierta,cerrada',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        if (Caja::where('estado', 'abierta')->exists()) {
            return response()->json([
                'message' => 'Ya existe una caja abierta activa. Ciérrala antes de iniciar un nuevo turno.'
            ], 422);
        }

        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1 || empty($data['id_usuario'])) {
            $data['id_usuario'] = $user?->id_usuario ?? 1;
        }
        if (empty($data['fecha_apertura'])) {
            $data['fecha_apertura'] = now()->format('Y-m-d H:i:s');
        } else {
            $data['fecha_apertura'] = \Carbon\Carbon::parse($data['fecha_apertura'])->timezone(config('app.timezone', 'America/Santiago'))->format('Y-m-d H:i:s');
        }
        $data['total_ventas'] = (int)($data['total_ventas'] ?? 0);
        $data['total_recaudado'] = (int)($data['total_recaudado'] ?? 0);
        $data['estado'] = $data['estado'] ?? 'abierta';

        return response()->json($this->cajaService->createCaja($data), 201);
    }

    public function update(Request $request, $id)
    {
        $cajaActual = $this->cajaService->getCajaById($id);
        if (!$cajaActual) {
            return response()->json(['message' => 'Caja no encontrada.'], 404);
        }

        $user = $request->user();
        $isAdmin = $user && (int)$user->id_rol === 1;
        if ($cajaActual->estado === 'cerrada' && !$isAdmin) {
            return response()->json(['message' => 'Solo un administrador puede modificar un arqueo de caja cerrado.'], 403);
        }

        $data = $request->validate([
            'fecha_cierre' => 'nullable|date',
            'total_ventas' => 'nullable|numeric|min:0',
            'total_recaudado' => 'nullable|numeric|min:0',
            'efectivo_esperado' => 'nullable|numeric|min:0',
            'diferencia' => 'nullable|numeric',
            'ventas_efectivo' => 'nullable|numeric|min:0',
            'ventas_debito' => 'nullable|numeric|min:0',
            'ventas_transferencia' => 'nullable|numeric|min:0',
            'gastos_efectivo' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'nullable|string|in:abierta,cerrada',
        ]);

        if ((isset($data['total_recaudado']) || ($data['estado'] ?? null) === 'cerrada') && empty($data['fecha_cierre'])) {
            $data['fecha_cierre'] = now()->format('Y-m-d H:i:s');
            $data['estado'] = 'cerrada';
        } elseif (!empty($data['fecha_cierre'])) {
            $data['fecha_cierre'] = \Carbon\Carbon::parse($data['fecha_cierre'])->timezone(config('app.timezone', 'America/Santiago'))->format('Y-m-d H:i:s');
        }

        if (isset($data['efectivo_esperado']) && isset($data['total_recaudado']) && !isset($data['diferencia'])) {
            $data['diferencia'] = (int)$data['total_recaudado'] - (int)$data['efectivo_esperado'];
        }

        return response()->json($this->cajaService->updateCaja($id, $data));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || (int)$user->id_rol !== 1) {
            return response()->json(['message' => 'Solo un administrador puede eliminar registros de caja.'], 403);
        }

        $deleted = $this->cajaService->deleteCajaById($id);
        if (!$deleted) {
            return response()->json(['message' => 'Caja no encontrada.'], 404);
        }
        return response()->json(null, 204);
    }
}
