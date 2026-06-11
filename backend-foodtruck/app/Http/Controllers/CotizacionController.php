<?php

namespace App\Http\Controllers;
use App\Services\CotizacionServices;
use Illuminate\Http\Request;


class CotizacionController extends Controller
{
    protected $cotizacionServices;
    protected $usuario_dicremeServices;

    public function __construct(CotizacionServices $cotizacionServices)
    {
        $this->cotizacionServices = $cotizacionServices;
    }

    public function index()
    {
        return response()->json($this->cotizacionServices->getAllCotizaciones());
    }

    public function store(Request $request)
    {
        // Validamos solo los campos que existen en la tabla y los que son necesarios
        $data = $request->validate([
            'id_distribuidor'      => 'required|integer|exists:usuarios_distribuidores,id',
            'id_usuario_dicreme'   => 'nullable|integer|exists:usuarios_dicreme,id',
            'id_estado_cotizacion' => 'required|integer|exists:estados_cotizacion,id',
            'total_cotizacion'     => 'required|numeric', // Cambiado a numeric para ser preciso
            'persona_recibe' => 'required|string',
            
            // Validamos el array de productos que viene en el JSON
            'cotizacion_productos' => 'required|array|min:1',
            'cotizacion_productos.*.id_producto'     => 'required|integer|exists:productos,id',
            'cotizacion_productos.*.cantidad'        => 'required|integer|min:1',
            'cotizacion_productos.*.precio_unitario_venta' => 'required|numeric',
        ]);

        // Agregamos las fechas que el usuario NO envía, pero que el sistema debe registrar
        $data['fecha_creacion'] = now()->toDateString();
        $data['hora_creacion']  = now()->toTimeString();

        $data['subtotal_cotizacion'] = $data['total_cotizacion'];

        return response()->json($this->cotizacionServices->createCotizacion($data), 201);
    }

    public function show($id)
    {   
        return response()->json($this->cotizacionServices->getCotizacionById($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_distribuidor' => 'required|integer|exists:usuarios_distribuidores,id',
            'id_usuario_dicreme' => 'required | integer| exists:usuarios_dicreme, id',
            'id_estado_cotizacion' => 'required|integer|exists:estados_cotizacion,id',
            'total_cotizacion'     => 'required|numeric',
            'fecha_creacion',
            'hora_creacion',
            'persona_recibe' => 'required|string'
        ]);

        return response()->json($this->cotizacionServices->updateCotizacion($id, $data));
    }

    public function updateTotal(Request $request, $id)
    {
        $data = $request->validate([
            'total_cotizacion' => 'required|numeric',
        ]);

        return response()->json($this->cotizacionServices->updateTotalCotizacion($id, $data['total_cotizacion']));
    }

    public function destroy($id)
    {
        return response()->json($this->cotizacionServices->deleteCotizacion($id));
    }

    public function transformarCotizacionEnPedido($idCotizacion)
    {   
    $pedido = $this->cotizacionServices->transformarCotizacionEnPedido($idCotizacion);

        if ($pedido === false) { 
            return response()->json([
                'status'  => 'error',
                'message' => 'La cotización no está en estado completado o no se pudo procesar.',
            ], 400); 
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'La cotización ahora es un pedido',
            'data'    => $pedido
        ], 200);
    }

    public function getallCotizacionesByUsuariodicreme($id_usuario_dicreme)
    {
        return response()->json($this->cotizacionServices->getCotizacionesByUsuario($id_usuario_dicreme));

    }

    public function tomarcotizacion($id_cotizacion, $id_usuario_dicreme){
        
    $cotizacionActualizada = $this->cotizacionServices->tomarcotizacionadmin($id_cotizacion, $id_usuario_dicreme);

        if($cotizacionActualizada === false){
            return response()->json([
            'status'  => 'error',
            'message' => 'La cotizacion no existe',
        ], 404);
        }

        if($cotizacionActualizada === null){
            return response()->json([
            'status'  => 'error',
            'message' => 'el usuario no existe',
        ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'El administrador tomó la cotización correctamente.',
            'data'    => $cotizacionActualizada
        ], 200);

    }


    public function dejarCotizacion($id, $id_usuario_dicreme) 
    {
        $resultado = $this->cotizacionServices->Dejarcotizacionadmin($id, $id_usuario_dicreme);

        if ($resultado === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para liberar esta cotización porque está asignada a otro administrador.'
            ], 403); // 403 Forbidden (Prohibido)
        }

        if ($resultado === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cotización solicitada no existe.'
            ], 404); // 404 Not Found
        }

        // Éxito total
        return response()->json([
            'status' => 'success',
            'message' => 'La cotización fue liberada correctamente y vuelve a estar disponible.',
            'data' => $resultado
        ], 200); // 200 OK
    }   


    public function cancelarCotizacion($id, $id_usuario) 
    {
        $resultado = $this->cotizacionServices->Cancelarcotizacionadmin($id, $id_usuario);

        if ($resultado === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para cancelar esta cotización porque está asignada a otro administrador.'
            ], 403); // 403 Forbidden (Prohibido)
        }

        if ($resultado === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cotización solicitada no existe.'
            ], 404); // 404 Not Found
        }

        // Éxito total
        return response()->json([
            'status' => 'success',
            'message' => 'La cotización fue cancelada exitosamente',
            'data' => $resultado
        ], 200); // 200 OK
    }

    public function validarCotizacion(Request $request, $id, $id_usuario_dicreme)
    {
        
        $request->validate([
            'discountType' => 'required|in:percentage,fixed,none',
            'discountInput' => 'required|numeric|min:0', 
            'productos' => 'nullable|array',
            'productos.*.id_cotizacion_producto' => 'required|integer',
            'productos.*.discountType' => 'required|in:percentage,fixed,none',
            'productos.*.discountValue' => 'required|numeric|min:0', 
        ]);

        try {

            // 2. Llamamos a tu función del servicio pasando los 3 datos
            $this->cotizacionServices->validarCotizacion($id, $id_usuario_dicreme, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => '¡Cotización validada, montos aplicados y pasada a estado Completado con éxito!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al validar la cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getallCotizacionesByUsuariodistribuidor($id_usuario_distribuidor){
        return response()->json($this->cotizacionServices->getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor));
    }

    public function getdetailcotizacion($id){
        
        $resultado = $this->cotizacionServices->getDetailCotizacion($id);

        if( $resultado === false){
            return response()->json([
                'status' => 'error',
                'message' => 'No existe la cotización'
            ], 403); 
        
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detalles de la cotizacion obtenidas exitosamente',
            'data' => $resultado
        ], 200); // 200 OK
    }

    public function addproductocotizacion(Request $request, $id_cotizacion)
    {
        // Validación minimalista y optimizada
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
        ]);

        try {
            // Formateamos como lista para el servicio
            $listaProductos = [
                [
                    'id_producto' => $data['id_producto'],
                    'cantidad'    => $data['cantidad']
                ]
            ];

            $cotizacionActualizada = $this->cotizacionServices
                ->add_productos_to_cotizacion($id_cotizacion, $listaProductos);

            return response()->json([
                'status'  => 'success',
                'message' => 'Producto añadido correctamente.',
                'data'    => $cotizacionActualizada
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeProductoCotizacion(Request $request, $id_cotizacion)
{
    // Solo validamos el ID del producto y la cantidad a quitar
    $data = $request->validate([
        'id_producto' => 'required|integer|exists:productos,id',
        'cantidad'    => 'required|integer|min:1', // Cantidad que se quiere restar/quitar
    ]);

    try {
        $listaProductos = [
            [
                'id_producto' => $data['id_producto'],
                'cantidad'    => $data['cantidad']
            ]
        ];

        // Llamamos al servicio para procesar la baja
        $cotizacionActualizada = $this->cotizacionServices
            ->remove_productos_to_cotizacion($id_cotizacion, $listaProductos);

        return response()->json([
            'status'  => 'success',
            'message' => 'Producto modificado/removido de la cotización con éxito.',
            'data'    => $cotizacionActualizada
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Error al remover el producto: ' . $e->getMessage()
        ], 400); // 400 Bad Request si la regla de negocio falla
    }
}

public function destroyProductoCotizacion(Request $request, $id_cotizacion)
{
    $data = $request->validate([
        'id_producto' => 'required|integer|exists:productos,id',
    ]);

    try {
        // Llamamos al nuevo método del servicio
        $cotizacionActualizada = $this->cotizacionServices
            ->force_remove_producto($id_cotizacion, $data['id_producto']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Producto eliminado por completo de la cotización.',
            'data'    => $cotizacionActualizada
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Error al eliminar el producto: ' . $e->getMessage()
        ], 400);
    }
}

}