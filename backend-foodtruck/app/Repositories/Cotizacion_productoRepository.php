<?php

namespace App\Repositories;
use App\Models\Cotizacion_producto;

class Cotizacion_productoRepository {
    public function createCotizacionProducto($data)
    {
        return Cotizacion_producto::create($data);
    }

    public function getCotizacionProductoById($id)
    {
        return Cotizacion_producto::find($id);
    }

    public function getCotizacionProductosByCotizacionId($idCotizacion)
    {
        return Cotizacion_producto::where('id_cotizacion', $idCotizacion)->get();
    }

    public function getCotizacionProductosByProductoId($idProducto)
    {
        return Cotizacion_producto::where('id_producto', $idProducto)->get();
    }

    public function updateCotizacionProducto($id, $data)
    {
        $cotizacionProducto = Cotizacion_producto::find($id);
        if ($cotizacionProducto) {
            $cotizacionProducto->update($data);
            return $cotizacionProducto;
        }
        return null;
    }

    public function deleteCotizacionProducto($id)
    {
        $cotizacionProducto = Cotizacion_producto::find($id);
        if ($cotizacionProducto) {
            $cotizacionProducto->delete();
            return true;
        }
        return false;
    }

    public function getAllCotizacionProductos()
    {
        return Cotizacion_producto::all();
    }
}