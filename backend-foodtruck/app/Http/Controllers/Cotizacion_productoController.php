<?php

namespace App\Http\Controllers;
use App\Services\Cotizacion_productoServices;
use Illuminate\Http\Request;

class Cotizacion_productoController extends Controller
{
    protected $cotizacionProductoServices;

    public function __construct(Cotizacion_productoServices $cotizacionProductoServices)
    {
        $this->cotizacionProductoServices = $cotizacionProductoServices;
    }

    public function index()
    {
        return response()->json($this->cotizacionProductoServices->getAllCotizacionProductos());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer',
            'precio_unitario_venta' => 'required|numeric',
        ]);

        return response()->json($this->cotizacionProductoServices->createCotizacionProducto($data), 201);
    }

    public function show($id)
    {
        return response()->json($this->cotizacionProductoServices->getCotizacionProductoById($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_cotizacion' => 'sometimes|required|exists:cotizaciones,id',
            'id_producto' => 'sometimes|required|exists:productos,id',
            'cantidad' => 'sometimes|required|integer',
            'precio_unitario_venta' => 'sometimes|required|numeric',
        ]);

        return response()->json($this->cotizacionProductoServices->updateCotizacionProducto($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->cotizacionProductoServices->deleteCotizacionProducto($id));
    }

    public function getByCotizacionId($idCotizacion)
    {
        return response()->json($this->cotizacionProductoServices->getCotizacionProductosByCotizacionId($idCotizacion));
    }

    public function getByProductoId($idProducto)
    {
        return response()->json($this->cotizacionProductoServices->getCotizacionProductosByProductoId($idProducto));
    }

}