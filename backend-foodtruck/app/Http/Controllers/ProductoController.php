<?php

namespace App\Http\Controllers;

use App\Services\ProductoServices;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    protected $productoServices;

    public function __construct(ProductoServices $productoServices)
    {
        $this->productoServices = $productoServices;
    }

    public function index()
    {
        return response()->json($this->productoServices->getAllProductos());
    }

    public function show($id)
    {
        return response()->json($this->productoServices->getProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_categoria' => 'required|integer|exists:categorias,id',
            'id_formato' => 'required|integer|exists:formatos,id',
            'nombre_producto' => 'required|string|max:255',
            'precio_producto' => 'required|integer|min:0',
        ]);

        return response()->json($this->productoServices->createProducto($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_categoria' => 'sometimes|required|integer|exists:categorias,id',
            'id_formato' => 'sometimes|required|integer|exists:formatos,id',
            'nombre_producto' => 'sometimes|required|string|max:255',
            'precio_producto' => 'sometimes|required|integer|min:0',
        ]);

        return response()->json($this->productoServices->updateProducto($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->productoServices->deleteProducto($id));
    }
}