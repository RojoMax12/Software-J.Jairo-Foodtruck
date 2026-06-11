<?php

namespace App\Http\Controllers;
use App\Services\PedidoServices;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoServices $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function index()
    {
        return response()->json($this->pedidoService->getAllPedidos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoService->getPedidoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'id_usuario_distribuidor' =>'required|integer|exists:usuario_distribuidores,id',
            'fecha_creacion' => 'required|date',
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id',
            'id_usuario_dicreme' => 'sometimes|integer|exists:usuarios_dicreme,id',
            'monto_estimado' => 'required|integer',
            'monto_final' => 'required|integer',
        ]);

        return response()->json($this->pedidoService->createPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_cotizacion' => 'sometimes|required|integer|exists:cotizaciones,id',
            'id_usuario_distribuidor' =>'required|integer|exists:usuario_distribuidores,id',
            'fecha_creacion' => 'sometimes|required|date',
            'id_estado_pedido' => 'sometimes|required|integer|exists:estados_pedido,id',
            'id_usuario_dicreme' => 'sometimes|integer|exists:usuarios_dicreme,id',
            'monto_estimado' => 'sometimes|required|integer',
            'monto_final' => 'sometimes|required|integer',
        ]);

        return response()->json($this->pedidoService->updatePedido($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->pedidoService->deletePedido($id));
    }

    public function getallPedidosByUsuariodicreme($id_usuario_dicreme)
    {
        return response() ->json($this->pedidoService->getPedidoByUsuario($id_usuario_dicreme));
    }

    public function getallPedidosByUsuariodistribuidor($id_usuario_distribuidor)
    {
        return response() ->json($this->pedidoService->getPedidoByUsuario_distribuidores($id_usuario_distribuidor));
    }

    public function cambiarEstado($id_pedido)
    {
        try {
            // Ejecutamos la función secuencial interna
            $pedidoActualizado = $this->pedidoService->actualizarEstadoPedido($id_pedido);

            return response()->json([
                'status'  => 'success',
                'message' => "El pedido avanzó a la siguiente etapa correctamente.",
                'data'    => $pedidoActualizado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getdetailpedido($id){
        
        $resultado = $this->pedidoService->getDetailPedido($id);

        if( $resultado === false){
            return response()->json([
                'status' => 'error',
                'message' => 'No existe el pedido'
            ], 403); 
        
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detalles de la cotizacion obtenidas exitosamente',
            'data' => $resultado
        ], 200); // 200 OK
    }
}