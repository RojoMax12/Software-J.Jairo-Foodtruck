<?php

namespace App\Http\Controllers;
use App\Services\VentaServices;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    protected $ventaServices;

    public function __construct(VentaServices $ventaServices)
    {
        $this->ventaServices = $ventaServices;
    }

    public function index()
    {
        return response()->json($this->ventaServices->getAllVentas());
    }

    public function show($id)
    {
        return response()->json($this->ventaServices->getVentaById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'numero_factura' => 'required|integer|unique:ventas,numero_factura',
            'fecha' => 'required|date',
            'glosa' => 'required|string|max:255',
            'estado_pago' => 'required|String|max:255',
            'total' => 'required|numeric|min:0',
        ]);
        return response()->json($this->ventaServices->createVenta($data));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'numero_factura' => 'required|integer|unique:ventas,numero_factura',
            'fecha' => 'required|date',
            'glosa' => 'required|string|max:255',
            'estado_pago' => 'required|String|max:255',
            'total' => 'required|numeric|min:0',
        ]);
        return response()->json($this->ventaServices->updateVenta($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->ventaServices->deleteVenta($id));
    }
}