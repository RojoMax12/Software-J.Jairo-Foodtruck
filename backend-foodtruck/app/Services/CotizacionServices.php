<?php

namespace App\Services;
use App\Repositories\CotizacionRepository;
use App\Repositories\PedidoRepository;         // <--- Inyectamos el Repositorio de Pedidos
use App\Repositories\Pedido_productoRepository; // <--- Inyectamos el Repositorio del Detalle
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\DespachoRepository;
use App\Repositories\Cotizacion_productoRepository;
use App\Repositories\ProductoRepository;
use App\Models\Cotizacion;
use Illuminate\Support\Facades\DB;

class CotizacionServices
{
    protected $cotizacionRepository;
    protected $pedidoRepository;          // <--- Repositorio de Pedidos
    protected $pedidoProductoRepository;  // <--- Repositorio del Detalle
    protected $usuariodicremeRepository;
    protected $usuario_distribuidoresRepository;
    protected $despachorepository;
    protected $cotizacionproductoRepository;
    protected $productoRepository;

    public function __construct(CotizacionRepository $cotizacionRepository , PedidoRepository $pedidoRepository, 
    Pedido_productoRepository $pedidoProductoRepository, Usuario_dicremeRepository $usuariodicremeRepository,
    Usuario_distribuidoresRepository $usuario_distribuidoresRepository, DespachoRepository $despachorepository
    ,Cotizacion_productoRepository $cotizacionproductoRepository,
    ProductoRepository $productoRepository)
    {
        $this->cotizacionRepository = $cotizacionRepository;
        $this->pedidoRepository = $pedidoRepository;
        $this->pedidoProductoRepository = $pedidoProductoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->usuario_distribuidoresRepository = $usuario_distribuidoresRepository;
        $this->despachorepository = $despachorepository;
        $this->cotizacionproductoRepository = $cotizacionproductoRepository;
        $this->productoRepository = $productoRepository;
    }

    public function createCotizacion(array $data)
    {
        // 1. Creamos la cotización maestra
        $cotizacion = Cotizacion::create([
            'id_distribuidor'      => $data['id_distribuidor'],
            'id_usuario_dicreme'   => $data['id_usuario_dicreme'] ?? null,
            'id_estado_cotizacion' => $data['id_estado_cotizacion'],
            'persona_recibe' => $data['persona_recibe'],
            'fecha_creacion'       => $data['fecha_creacion'],
            'hora_creacion'        => $data['hora_creacion'],
            'total_cotizacion'     => $data['total_cotizacion'],
            'subtotal_cotizacion'  => $data['subtotal_cotizacion']
        ]);

        // 2. Creamos los detalles asociados
        foreach ($data['cotizacion_productos'] as $producto) {
            $cotizacion->cotizacionProductos()->create($producto);
        }

        return $cotizacion->load('cotizacionProductos');
    }

    public function getCotizacionById($id)
    {
        return $this->cotizacionRepository->getCotizacionById($id);
    }

    public function updateCotizacion($id, $data)
    {
        return $this->cotizacionRepository->updateCotizacion($id, $data);
    }

    public function updateTotalCotizacion($id, $total)
    {
        return $this->cotizacionRepository->updateCotizacion($id, ['total_cotizacion' => $total]);
    }

    public function deleteCotizacion($id)
    {
        return $this->cotizacionRepository->deleteCotizacion($id);
    }

    public function getAllCotizaciones()
    {
        return $this->cotizacionRepository->getAllCotizaciones();
    }

    public function transformarCotizacionEnPedido($idCotizacion)
    {
        $cotizacion = $this->cotizacionRepository->getCotizacionConProductos($idCotizacion);
        $usuario = $this->usuario_distribuidoresRepository->getUsuarioDistribuidorById($cotizacion->id_distribuidor);

        if (!$cotizacion) {
            throw new \Exception("La cotización no existe.");
        }
        
        if($cotizacion->id_estado_cotizacion !== 3){
            return false;
        }

        // Creamos el pedido
        $pedido = $this->pedidoRepository->createPedido([
            'id_cotizacion'      => $cotizacion->id,
            'id_usuario_dicreme' => null,
            'id_usuario_distribuidor' => $cotizacion->id_distribuidor,
            'id_estado_pedido'   => 1, // Estado inicial
            'fecha_creacion'       => now()->toDateString(),
            'hora_creacion'      => now()->toTimeString(),
            'monto_estimado'     => $cotizacion->subtotal_cotizacion,
            'monto_final'        => $cotizacion->total_cotizacion,
        ]);

        // Clonamos los productos
        foreach ($cotizacion->cotizacionProductos as $item) {
            $this->pedidoProductoRepository->createPedidoProducto([
                'id_pedido'    => $pedido->id,
                'id_producto'  => $item->id_producto, // CORRECCIÓN: Usar ID del producto, no el de la cotización
                'cantidad'     => $item->cantidad,
                'precio_unitario_venta' => $item->precio_unitario_venta, 
            ]);
        }

        $this->despachorepository->createDespacho([
            'id_pedido' => $pedido->id,
            'direccion_entrega' => $usuario->direccion,
            'comuna' => $usuario->comuna,
            'fecha_entrega' => null,
            'persona_recibe' => $cotizacion->persona_recibe,
            'estado_despacho' => null,
        ]);

        return $pedido;
    }

    public function tomarcotizacionadmin($id_cotizacion, $id_usuario_dicreme){

        $usuario_dicreme = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);

        if(!$cotizacion){
            return false;
        }
        if(!$usuario_dicreme){
            return null;
        }

        if($cotizacion->id_usuario_dicreme != null){
            throw new \Exception("No puedes tomar esta cotizacion, ya tomada");
        }

        return $this->cotizacionRepository->TomarCotizacionUsuarioDicreme($id_cotizacion, $id_usuario_dicreme);
    }

    public function Dejarcotizacionadmin($id_cotizacion, $id_usuario_dicreme)
    {
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);
        $usuario = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if (!$cotizacion) {
            return null;
        }

        if (!$usuario){
            throw new \Exception("El usuario no existe");
        }

        if ($cotizacion->id_usuario_dicreme !== (int)$id_usuario_dicreme) {
            return false;
        }

        return $this->cotizacionRepository->DejarCotizacionUsuarioDicreme($id_cotizacion);
    }

    public function Cancelarcotizacionadmin($id_cotizacion, $id_usuario_dicreme)
    {
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);
        $usuario = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if (!$cotizacion) {
            return null;
        }

        if (!$usuario){
            throw new \Exception("El usuario no existe");
        }

        return $this->cotizacionRepository->CancelarCotizacion($id_cotizacion);
    }



    public function getCotizacionesByUsuario($id_usuario_dicreme) {

        $usuario_dicreme = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if(!$usuario_dicreme){
            throw new \Exception("El usuario no existe.");
        }
        
        return $this->cotizacionRepository->getCotizacionesByUsuario($id_usuario_dicreme);
    }


    public function getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor) {

        $usuario_distribuidor = $this->usuario_distribuidoresRepository->getUsuarioDistribuidorById($id_usuario_distribuidor);

        if(!$usuario_distribuidor){
            throw new \Exception("El usuario no existe.");
        }
        
        return $this->cotizacionRepository->getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor);
    }

    public function getDetailCotizacion($id){
        // 1. Buscamos la cotización base
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id);

        if (!$cotizacion) {
            return false; // No existe
        }

        // 2. Traemos las relaciones del distribuidor y los registros pivote
        $usuario_distribuidor = $this->usuario_distribuidoresRepository->getUsuarioDistribuidorById($cotizacion->id_distribuidor);
        $cotizacion_products = $this->cotizacionproductoRepository->getCotizacionProductosByCotizacionId($cotizacion->id);

        // 3. RECORREMOS LA LISTA: Combinamos el pivote con la info del catálogo del producto
        $listaProductosData = [];

        foreach ($cotizacion_products as $itemPivote) {
            // Buscamos el detalle del producto usando el id_producto de la fila intermedia
            $infoProducto = $this->productoRepository->getProductoById($itemPivote->id_producto);

            if ($infoProducto) {
                $listaProductosData[] = [
                    'id_producto'           => $infoProducto->id,
                    'nombre_producto'       => $infoProducto->nombre_producto,
                    'id_categoria'          => $infoProducto->id_categoria,
                    'id_formato'            => $infoProducto->id_formato,
                    'cantidad'              => $itemPivote->cantidad,
                    'precio_unitario_venta' => (int) $itemPivote->precio_unitario_venta,
                    'subtotal'              => $itemPivote->cantidad * $itemPivote->precio_unitario_venta
                ];
            }
        }

        // 4. RETORNAMOS EL PAQUETE COMPLETO
        return [
            'id_cotizacion'        => $cotizacion->id,
            'persona_recibe'       => $cotizacion->persona_recibe,
            'total_cotizacion'     => (float) $cotizacion->total_cotizacion,
            'id_estado_cotizacion' => $cotizacion->id_estado_cotizacion,
            'fecha_creacion'       => $cotizacion->created_at,
            'tipo_descuento_general' => $cotizacion->tipo_descuento_general,
            'valor_descuento_general' => $cotizacion->valor_descuento_general,
            'descuento_general_aplicado' => $cotizacion->descuento_general_aplicado,
            'descuento_productos_total' => $cotizacion->descuento_productos_total,

            // Objeto con la información del distribuidor
            'distribuidor'         => $usuario_distribuidor, 
            
            // Array estructurado con sus productos, cantidades y nombres reales
            'productos'            => $listaProductosData
        ];
    }


    public function validarCotizacion($id_cotizacion, $id_usuario_dicreme, array $payload = [])
{
    return DB::transaction(function () use ($id_cotizacion, $id_usuario_dicreme, $payload) {
        
        // 1. Cargamos la cotización con su relación intermedia exacta
        $cotizacion = Cotizacion::with('cotizacionProductos')->findOrFail($id_cotizacion);
        
        $subtotalGeneral = 0;
        $totalDescuentosProductos = 0;

        // Indexamos los productos que vienen de Vue usando la llave enviada 'id_cotizacion_producto'
        $productosFront = collect($payload['productos'] ?? [])->keyBy('id_cotizacion_producto');

        // 2. CALCULAR DESCUENTOS AVANZADOS (POR PRODUCTO)
        foreach ($cotizacion->cotizacionProductos as $item) {
            $subtotalItem = $item->cantidad * $item->precio_unitario_venta;
            $subtotalGeneral += $subtotalItem;

            // Si en el frontend se modificó este registro específico
            if ($productosFront->has($item->id)) {
                $prodData = $productosFront->get($item->id);
                $tipoDesc = $prodData['discountType'] ?? 'none';
                $valorDesc = max(0, (int)($prodData['discountValue'] ?? 0));
                $descuentoCalculado = 0;

                if ($valorDesc > 0) {
                    if ($tipoDesc === 'percentage') {
                        $valorDesc = min($valorDesc, 100); // Tope 100%
                        $descuentoCalculado = ($subtotalItem * $valorDesc) / 100;
                    } else if ($tipoDesc === 'fixed') {
                        $descuentoCalculado = min($valorDesc, $subtotalItem); // Tope no superar costo item
                    }
                }

                // Actualizamos las columnas de la tabla intermedia
                $item->update([
                    'tipo_descuento' => $tipoDesc,
                    'valor_descuento' => $valorDesc,
                    'descuento_aplicado' => (int)$descuentoCalculado
                ]);

                $totalDescuentosProductos += $descuentoCalculado;
            }
        }

        // 3. CALCULAR EL DESCUENTO GENERAL (PORCENTAJE O MONTO FIJO)
        $tipoDescGeneral = $payload['discountType'] ?? 'none';
        $valorDescGeneral = max(0, (int)($payload['discountInput'] ?? 0));
        $descuentoGeneralAplicado = 0;

        if ($valorDescGeneral > 0) {
            if ($tipoDescGeneral === 'percentage') {
                $valorDescGeneral = min($valorDescGeneral, 100);
                $descuentoGeneralAplicado = ($subtotalGeneral * $valorDescGeneral) / 100;
            } else if ($tipoDescGeneral === 'fixed') {
                $descuentoGeneralAplicado = min($valorDescGeneral, $subtotalGeneral);
            }
        }

        // 4. MATEMÁTICA FINAL REDONDEADA
        $descuentoTotalAcumulado = $totalDescuentosProductos + $descuentoGeneralAplicado;
        $totalFinal = max(0, $subtotalGeneral - $descuentoTotalAcumulado);

        // 5. ACTUALIZAR TODO DE GOLPE (Tus lógicas de estado + las nuevas matemáticas)
        $cotizacion->update([
            // Lógica original de tu negocio para validar:
            'id_usuario_dicreme'          => $id_usuario_dicreme, // Registra qué administrador la tomó / validó
            'id_estado_cotizacion'        => 3, // O el ID real que tenga el estado "Completado" en tu sistema

            // Nuevas columnas matemáticas de descuentos:
            'subtotal_cotizacion'         => (int)$subtotalGeneral,
            'tipo_descuento_general'      => $tipoDescGeneral,
            'valor_descuento_general'     => (int)$valorDescGeneral,
            'descuento_general_aplicado'  => (int)$descuentoGeneralAplicado,
            'descuento_productos_total'   => (int)$totalDescuentosProductos,
            'total_cotizacion'            => (int)round($totalFinal) 
        ]);

        return $cotizacion;
    });
    }

    public function add_productos_to_cotizacion($id_cotizacion, array $productos_data)
    {
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);

        if (!$cotizacion) {
            throw new \Exception("La cotización no existe.");
        }

        if ($cotizacion->estado_cotizacion === 'Aceptada' || $cotizacion->estado_cotizacion === 'Transformada') {
            throw new \Exception("Esta cotización ya fue cerrada.");
        }

        DB::beginTransaction();

        try {
            $acumulador_subtotal = 0;

            foreach ($productos_data as $data) {
                // Buscamos el producto en la DB usando el ID seguro enviado
                $producto = \App\Models\Producto::find($data['id_producto']);
                
                if (!$producto) {
                    throw new \Exception("El producto con ID {$data['id_producto']} no existe.");
                }

                // Rescatamos el precio real directo de la columna de tu catálogo
                $precioVenta = $producto->precio_producto;

                // Verificamos si ya existe la relación en la cotización
                $item_existente = $cotizacion->cotizacionProductos()
                    ->where('id_producto', $data['id_producto'])
                    ->first();

                if ($item_existente) {
                    // Si ya existe, sumamos la cantidad nueva
                    $item_existente->cantidad += $data['cantidad'];
                    $item_existente->precio_unitario_venta = $precioVenta; 
                    $item_existente->save();
                } else {
                    // Si es nuevo, creamos la fila con el precio extraído de la DB
                    $cotizacion->cotizacionProductos()->create([
                        'id_producto'           => $data['id_producto'],
                        'cantidad'              => $data['cantidad'],
                        'precio_unitario_venta' => $precioVenta,
                    ]);
                }

                $acumulador_subtotal += $data['cantidad'] * $precioVenta;
            }

            // Actualizamos los totales de la cabecera
            $cotizacion->subtotal_cotizacion += $acumulador_subtotal;
            $cotizacion->total_cotizacion    += $acumulador_subtotal;
            $cotizacion->save();

            DB::commit();

            return $cotizacion->load('cotizacionProductos');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function remove_productos_to_cotizacion($id_cotizacion, array $productos_data)
{
    // 1. Obtener la cotización
    $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);

    if (!$cotizacion) {
        throw new \Exception("La cotización no existe.");
    }

    // Seguridad: Bloquear si ya pasó a pedido
    if ($cotizacion->estado_cotizacion === 'Aceptada' || $cotizacion->estado_cotizacion === 'Transformada') {
        throw new \Exception("No se pueden alterar los datos. Esta cotización ya está cerrada.");
    }

    DB::beginTransaction();

    try {
        $descuento_subtotal = 0;

        foreach ($productos_data as $data) {
            // Buscar el producto en el detalle de la cotización
            $item_existente = $cotizacion->cotizacionProductos()
                ->where('id_producto', $data['id_producto'])
                ->first();

            if (!$item_existente) {
                throw new \Exception("El producto no se encuentra en esta cotización.");
            }

            $precioVenta = $item_existente->precio_unitario_venta;

            // Determinar si se resta cantidad o se elimina la fila completa
            if ($data['cantidad'] >= $item_existente->cantidad) {
                // CASO A: Quieren quitar más o lo mismo de lo que hay -> Se elimina el registro completo
                // El dinero a descontar es el total de lo que valía esa fila entera
                $descuento_subtotal += $item_existente->cantidad * $precioVenta;
                
                $item_existente->delete();
            } else {
                // CASO B: Solo quieren reducir la cantidad (ej. de 5 tortas bajar a 3)
                $item_existente->cantidad -= $data['cantidad'];
                $item_existente->save();

                // El dinero a descontar es solo por las unidades retiradas
                $descuento_subtotal += $data['cantidad'] * $precioVenta;
            }
        }

        // 3. Restar los montos de la cabecera cuidando que no queden valores negativos
        $cotizacion->subtotal_cotizacion = max(0, $cotizacion->subtotal_cotizacion - $descuento_subtotal);
        $cotizacion->total_cotizacion    = max(0, $cotizacion->total_cotizacion - $descuento_subtotal);
        $cotizacion->save();

        DB::commit();

        return $cotizacion->load('cotizacionProductos');

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

public function force_remove_producto($id_cotizacion, $id_producto)
{
    $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);

    if (!$cotizacion) {
        throw new \Exception("La cotización no existe.");
    }

    if ($cotizacion->estado_cotizacion === 'Aceptada' || $cotizacion->estado_cotizacion === 'Transformada') {
        throw new \Exception("No se pueden alterar los datos. Esta cotización ya está cerrada.");
    }

    DB::beginTransaction();

    try {
        // Buscamos la fila específica del producto en el detalle
        $item_existente = $cotizacion->cotizacionProductos()
            ->where('id_producto', $id_producto)
            ->first();

        if (!$item_existente) {
            throw new \Exception("El producto no está en esta cotización.");
        }

        // Calculamos el valor TOTAL que costaba este producto (cantidad * precio)
        $monto_a_descontar = $item_existente->cantidad * $item_existente->precio_unitario_venta;

        // Borramos la fila directamente de PostgreSQL
        $item_existente->delete();

        // Restamos el monto total de la cabecera
        $cotizacion->subtotal_cotizacion = max(0, $cotizacion->subtotal_cotizacion - $monto_a_descontar);
        $cotizacion->total_cotizacion    = max(0, $cotizacion->total_cotizacion - $monto_a_descontar);
        $cotizacion->save();

        DB::commit();

        return $cotizacion->load('cotizacionProductos');

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
}
