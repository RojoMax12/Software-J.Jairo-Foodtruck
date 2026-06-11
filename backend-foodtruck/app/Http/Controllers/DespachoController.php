<?php

namespace App\Http\Controllers;
use App\Services\DespachoServices;
use Illuminate\Http\Request;

class DespachoController extends Controller
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
            'id_pedido' => 'required|integer|exists:pedidos,id',
            'direccion_entrega' => 'required|string|max:255',
            'fecha_entrega' => 'required|date',
            'persona_recibe' => 'required|string|max:255',
            'comuna' => 'required|string|max:255',
            'estado_despacho' => 'required|string|max:40',
        ]);
        return response()->json($this->despachoServices->createDespacho($data));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id',
            'direccion_entrega' => 'required|string|max:255',
            'fecha_entrega' => 'required|date',
            'persona_recibe' => 'required|string|max:255',
            'comuna' => 'required|string|max:255',
            'estado_despacho' => 'required|string|max:40',
        ]);
        return response()->json($this->despachoServices->updateDespacho($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->despachoServices->deleteDespacho($id));
    }

    public function getdespachobyidpedido($id){
        return response()->json($this->despachoServices->despachosbyidpedido($id));
    }
}