<?php

namespace App\Repositories;
use App\Models\Producto_Tamaño;

class ProductoTamañoRepository
{
    public function createProductoTamaño($data)
    {
        return Producto_Tamaño::create($data);
    }

    public function getProductoTamañosByProductoId($id_producto)
    {
        return Producto_Tamaño::where('id_producto', $id_producto)->get();
    }

    public function updateProductoTamaño($id, $data)
    {
        $productoTamaño = Producto_Tamaño::find($id);
        if ($productoTamaño) {
            $productoTamaño->update($data);
            return $productoTamaño;
        }
        return null;
    }

    public function deleteProductoTamañoById($id)
    {
        $productoTamaño = Producto_Tamaño::find($id);
        if ($productoTamaño) {
            $productoTamaño->delete();
            return true;
        }
        return false;
    }
}