<?php
namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Repositories\ProductoRepository;

class ProductoService
{
    protected $productoRepository;

    public function __construct(ProductoRepository $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    public function createProducto($data)
    {
        if (empty($data['nombre'])) {
            throw new \InvalidArgumentException('El nombre del producto es obligatorio.');
        }

        $producto = $this->productoRepository->createProducto($data);
        if ($producto) {
            HistorialMovimiento::registrar(
                'producto',
                'crear',
                'Nuevo producto agregado',
                $producto->nombre,
                'Agregado al catálogo gastronómico con precio base $' . number_format($producto->precio_base ?? 0, 0, ',', '.'),
                $producto->precio_base ?? 0
            );
        }
        return $producto;
    }

    public function getAllProductos()
    {
        return $this->productoRepository->getAllProductos();
    }

    public function getProductoById($id)
    {
        return $this->productoRepository->getProductoById($id);
    }

    public function getProductoByNombre($nombre)
    {
        return $this->productoRepository->getProductoByNombre($nombre);
    }

    public function getProductosByTipo($tipo)
    {
        return $this->productoRepository->getProductosByTipo($tipo);
    }

    public function getPedidosByProductoId($id)
    {
        return $this->productoRepository->getPedidosByProductoId($id);
    }

    public function getIngredientesByProductoId($id)
    {
        return $this->productoRepository->getIngredientesByProductoId($id);
    }

    public function getOfertasByProductoId($id)
    {
        return $this->productoRepository->getOfertasByProductoId($id);
    }

    public function updateProducto($id, $data)
    {
        $prodAnterior = $this->productoRepository->getProductoById($id);
        $producto = $this->productoRepository->updateProducto($id, $data);
        if ($producto) {
            $nombre = $producto->nombre ?? ($prodAnterior->nombre ?? "Producto #$id");
            $detalles = [];
            if (isset($data['activo'])) {
                $detalles[] = $data['activo'] ? 'Activado en tienda' : 'Desactivado de la tienda';
            }
            if (isset($data['disponible'])) {
                $detalles[] = $data['disponible'] ? 'Marcado con stock disponible' : 'Marcado sin stock';
            }
            if (isset($data['precio_base'])) {
                $detalles[] = 'Precio base: $' . number_format($data['precio_base'], 0, ',', '.');
            }
            $detalleStr = !empty($detalles) ? implode(' · ', $detalles) : 'Modificación general de datos';

            HistorialMovimiento::registrar(
                'producto',
                'editar',
                'Producto modificado',
                $nombre,
                $detalleStr,
                $producto->precio_base ?? 0
            );
        }
        return $producto;
    }

    public function deleteProductoById($id)
    {
        $prod = $this->productoRepository->getProductoById($id);
        $nombre = $prod ? $prod->nombre : "Producto ID #$id";
        
        // Desactivar estado del producto en lugar de eliminarlo físicamente
        $updated = $this->productoRepository->updateProducto($id, [
            'activo' => false,
            'disponible' => false,
        ]);

        if ($updated) {
            HistorialMovimiento::registrar(
                'producto',
                'estado',
                'Producto desactivado del catálogo',
                $nombre,
                'El producto fue dado de baja (inactivo) para preservar la integridad de pedidos.'
            );
        }
        return $updated;
    }
}
