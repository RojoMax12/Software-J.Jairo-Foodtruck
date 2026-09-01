<?php

namespace App\Repositories;

use App\Models\Producto_Tamaño;

class ProductoTamañoRepository
{
    public function getAllProductoTamaños()
    {
        return Producto_Tamaño::with(['producto', 'tamaño'])->get();
    }

    public function getProductoTamañoById($id)
    {
        return Producto_Tamaño::with(['producto', 'tamaño'])->find($id);
    }

    public function createProductoTamaño($data)
    {
        return Producto_Tamaño::create($data);
    }

    public function getProductoTamañosByProductoId($id_producto)
    {
        return Producto_Tamaño::with(['tamaño'])->where('id_producto', $id_producto)->get();
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