<?php
namespace App\Repositories;
use App\Models\Producto;

# Repositorio Producto
class ProductoRepository
{
    # Create
    public function createProducto($data)
    {
        return Producto::create($data);
    }

    # Geters
    public function getAllProductos()
    {
        return Producto::with([
            'categoria',
            'tamaños',
            'ingredientes.ingrediente',
            'ingredientes.tamaño',
            'promocionActiva.tamano',
            'promocionesActivas.tamano',
        ])->get();
    }

    public function getPublicProductos(bool $summary = false): array
    {
        $relations = [
            'categoria:id_categoria,nombre_categoria',
            'tamaños:id_tamaño,nombre',
            'promocionActiva.tamano:id_tamaño,nombre',
            'promocionesActivas.tamano:id_tamaño,nombre',
        ];

        if (!$summary) {
            $relations[] = 'ingredientes.ingrediente:id_ingrediente,nombre,disponible';
            $relations[] = 'ingredientes.tamaño:id_tamaño,nombre';
        }

        return Producto::with($relations)
            ->get()
            ->map(function ($prod) use ($summary) {
                $mapPromo = static function ($promo) {
                    if (!$promo) return null;
                    return [
                        'id_promocion' => $promo->id_promocion,
                        'id_producto' => $promo->id_producto,
                        'id_tamaño' => $promo->id_tamaño,
                        'titulo' => $promo->titulo,
                        'descripcion' => $promo->descripcion,
                        'precio_promocional' => $promo->precio_promocional,
                        'fecha_inicio' => $promo->fecha_inicio ? $promo->fecha_inicio->format('Y-m-d') : null,
                        'fecha_fin' => $promo->fecha_fin ? $promo->fecha_fin->format('Y-m-d') : null,
                        'activo' => (bool) $promo->activo,
                        'tamano' => $promo->tamano ? [
                            'id_tamaño' => $promo->tamano->id_tamaño,
                            'nombre' => $promo->tamano->nombre,
                        ] : null,
                    ];
                };

                $item = [
                    'id_producto' => $prod->id_producto,
                    'nombre' => $prod->nombre,
                    'descripcion' => $prod->descripcion,
                    'id_categoria' => $prod->id_categoria,
                    'grupo' => $prod->grupo,
                    'tipo_armado' => $prod->tipo_armado,
                    'cantidad_incluida' => $prod->cantidad_incluida,
                    'precio_ingrediente_extra' => $prod->precio_ingrediente_extra,
                    'precio_base' => $prod->precio_base,
                    'precio' => $prod->precio_base,
                    'activo' => $prod->activo,
                    'disponible' => $prod->disponible,
                    'imagen' => $prod->imagen,
                    'imagen_url' => $prod->imagen_url,
                    'imagen_posicion' => $prod->imagen_posicion,
                    'imagen_zoom' => $prod->imagen_zoom,
                    'imagen_ajuste' => $prod->imagen_ajuste,
                    'categoria' => $prod->categoria ? [
                        'id_categoria' => $prod->categoria->id_categoria,
                        'nombre_categoria' => $prod->categoria->nombre_categoria,
                    ] : null,
                    'tamaños' => ($prod->tamaños ?? collect())->map(static fn ($t) => [
                        'id_tamaño' => $t->id_tamaño,
                        'nombre' => $t->nombre,
                        'pivot' => [
                            'id_producto' => $t->pivot->id_producto ?? $prod->id_producto,
                            'id_tamaño' => $t->pivot->id_tamaño ?? $t->id_tamaño,
                            'precio' => $t->pivot->precio ?? 0,
                        ],
                    ])->values()->all(),
                    'promocion_activa' => $mapPromo($prod->promocionActiva),
                    'promociones_activas' => ($prod->promocionesActivas ?? collect())->map($mapPromo)->filter()->values()->all(),
                ];

                if (!$summary) {
                    $item['ingredientes'] = ($prod->ingredientes ?? collect())->map(static fn ($pi) => [
                        'id' => $pi->id ?? $pi->id_producto_ingrediente ?? null,
                        'id_producto' => $pi->id_producto,
                        'id_ingrediente' => $pi->id_ingrediente,
                        'id_tamaño' => $pi->id_tamaño,
                        'es_extra' => (bool) ($pi->es_extra ?? false),
                        'obligatorio' => (bool) ($pi->obligatorio ?? false),
                        'precio_extra' => $pi->precio_extra,
                        'ingrediente' => $pi->ingrediente ? [
                            'id_ingrediente' => $pi->ingrediente->id_ingrediente,
                            'nombre' => $pi->ingrediente->nombre,
                            'disponible' => (bool) $pi->ingrediente->disponible,
                        ] : null,
                        'tamaño' => $pi->tamaño ? [
                            'id_tamaño' => $pi->tamaño->id_tamaño,
                            'nombre' => $pi->tamaño->nombre,
                        ] : null,
                    ])->values()->all();
                }

                return $item;
            })->values()->all();
    }

    public function getProductoById($id)
    {
        return Producto::with([
            'categoria',
            'tamaños',
            'ingredientes.ingrediente',
            'ingredientes.tamaño',
            'promocionActiva.tamano',
            'promocionesActivas.tamano',
        ])->find($id);
    }

    public function getProductoByNombre($nombre)
    {
        return Producto::where('nombre', $nombre)->first();
    }

    public function getProductosByTipo($tipo)
    {
        return Producto::where('tipo', $tipo)->get();
    }

    public function getPedidosByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->detalles()->with('pedido')->get()->pluck('pedido')->filter()->unique('id_pedido')->values();
        }
        return null;
    }

    public function getIngredientesByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->ingredientes;
        }
        return null;
    }

    public function getOfertasByProductoId($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            return $producto->ofertas;
        }
        return null;
    }

    # Seters
    public function updateProducto($id, $data)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->update($data);
            return $producto;
        }
        return null;
    }

    # Delete
    public function deleteProductoById($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->delete();
            return true;
        }
        return false;
    }
}
