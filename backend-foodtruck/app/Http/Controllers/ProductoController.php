<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    protected $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    public function index()
    {
        return response()->json($this->productoService->getAllProductos());
    }

    public function show($id)
    {
        return response()->json($this->productoService->getProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // Normalización de campos obligatorios para PostgreSQL
        if (!isset($data['descripcion']) || $data['descripcion'] === null || trim($data['descripcion']) === '') {
            $data['descripcion'] = $data['nombre'] ?? 'Producto de la carta gastronómica';
        }
        if (!isset($data['tipo_armado']) || $data['tipo_armado'] === null) {
            $data['tipo_armado'] = 'estandar';
        }
        if (!isset($data['cantidad_incluida']) || $data['cantidad_incluida'] === null) {
            $data['cantidad_incluida'] = 1;
        }
        if (!isset($data['precio_ingrediente_extra']) || $data['precio_ingrediente_extra'] === null) {
            $data['precio_ingrediente_extra'] = 0;
        }
        if (!isset($data['activo'])) {
            $data['activo'] = true;
        }
        if (!isset($data['disponible'])) {
            $data['disponible'] = true;
        }

        // Si la imagen viene como base64 en el cuerpo, procesarla y guardarla en storage
        if (!empty($data['imagen']) && str_starts_with($data['imagen'], 'data:image')) {
            $data['imagen'] = $this->saveBase64Image($data['imagen'], 'prod_new_' . time());
        }

        $producto = $this->productoService->createProducto($data);

        // Sincronizar precios por tamaño si fueron enviados
        if ($producto) {
            $this->syncTamañosPrecios($producto, $data);
        }

        return response()->json($this->productoService->getProductoById($producto->id_producto ?? $producto->id) ?? $producto, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        // Normalización si se envían valores vacíos
        if (array_key_exists('descripcion', $data) && ($data['descripcion'] === null || trim($data['descripcion']) === '')) {
            $data['descripcion'] = $data['nombre'] ?? 'Producto de la carta gastronómica';
        }

        // Si la imagen viene como base64 en el cuerpo, procesarla y guardarla en storage
        if (!empty($data['imagen']) && str_starts_with($data['imagen'], 'data:image')) {
            $data['imagen'] = $this->saveBase64Image($data['imagen'], 'prod_' . $id . '_' . time());
        }

        $producto = $this->productoService->updateProducto($id, $data);

        // Sincronizar precios por tamaño si fueron enviados
        if ($producto) {
            $this->syncTamañosPrecios($producto, $data);
        }

        return response()->json($this->productoService->getProductoById($id) ?? $producto);
    }

    /**
     * Sincroniza los tamaños y sus precios en la tabla pivot producto_tamaño
     */
    protected function syncTamañosPrecios($producto, array $data)
    {
        try {
            $syncData = [];

            // 1. Si viene un array de precios por tamaño: [{"nombre": "Normal", "precio": 4500}, ...] o ["Normal" => 4500]
            if (!empty($data['precios_tamanos'])) {
                $rawList = $data['precios_tamanos'];
                if (is_array($rawList)) {
                    foreach ($rawList as $key => $item) {
                        $nombre = is_array($item) ? ($item['nombre'] ?? $key) : $key;
                        $precio = is_array($item) ? ($item['precio'] ?? $item['price'] ?? 0) : $item;
                        
                        if ($precio > 0) {
                            $tamano = \App\Models\Tamaño::firstOrCreate(['nombre' => trim($nombre)]);
                            if ($tamano) {
                                $syncData[$tamano->id_tamaño] = ['precio' => (float)$precio];
                            }
                        }
                    }
                }
            }

            // 2. Si no viene array múltiple pero viene precio_base o precio
            if (empty($syncData)) {
                $precio = $data['precio_base'] ?? $data['precio'] ?? null;
                if ($precio !== null) {
                    $idTamaño = $data['id_tamaño'] ?? 1;
                    $syncData[$idTamaño] = ['precio' => (float)$precio];
                }
            }

            if (!empty($syncData)) {
                $producto->tamaños()->sync($syncData);
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo sincronizar producto_tamaño: ' . $e->getMessage());
        }
    }

    public function uploadImage(Request $request, $id)
    {
        $producto = $this->productoService->getProductoById($id);
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Asegurar que la carpeta storage/app/public/productos exista
        if (!Storage::disk('public')->exists('productos')) {
            Storage::disk('public')->makeDirectory('productos');
        }

        $path = null;

        // 1. Si viene como archivo Multipart
        $fileKey = $request->hasFile('imagen') ? 'imagen' : ($request->hasFile('image') ? 'image' : ($request->hasFile('file') ? 'file' : null));
        if ($fileKey) {
            $request->validate([
                $fileKey => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5120',
            ], [
                "{$fileKey}.image" => 'El archivo debe ser una imagen válida.',
                "{$fileKey}.mimes" => 'Solo se admiten formatos JPEG, PNG, JPG o WEBP.',
                "{$fileKey}.max" => 'El tamaño de la imagen no puede superar los 5 MB.',
            ]);

            $file = $request->file($fileKey);
            $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'webp';
            if (!in_array(strtolower($extension), ['jpeg', 'jpg', 'png', 'webp'], true)) {
                $extension = 'webp';
            }
            $filename = 'prod_' . $id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('productos', $filename, 'public');
        } 
        // 2. Si viene como Base64 (data:image/webp;base64,...)
        else if ($request->filled('imagen') || $request->filled('image')) {
            $raw = $request->input('imagen') ?? $request->input('image');
            if (str_starts_with($raw, 'data:image')) {
                if (!preg_match('/^data:image\/(jpeg|png|jpg|webp);base64,/', $raw)) {
                    return response()->json(['message' => 'El formato Base64 debe ser una imagen JPEG, PNG o WEBP válida.'], 422);
                }
                $path = $this->saveBase64Image($raw, 'prod_' . $id . '_' . time());
            } elseif (filter_var($raw, FILTER_VALIDATE_URL)) {
                $path = $raw;
            } else {
                return response()->json(['message' => 'La URL o dato de imagen proporcionado no es válido.'], 422);
            }
        }

        if (!$path) {
            return response()->json(['message' => 'No se proporcionó un archivo o imagen válida.'], 422);
        }

        // Si ya tenía una imagen local guardada en storage, eliminar la anterior
        if ($producto->imagen && !str_starts_with($producto->imagen, 'http') && $producto->imagen !== $path) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $updated = $this->productoService->updateProducto($id, ['imagen' => $path]);

        return response()->json($updated);
    }

    /**
     * Guarda una cadena base64 en storage/app/public/productos/ y retorna la ruta relativa
     */
    protected function saveBase64Image(string $base64String, string $prefix): string
    {
        if (!Storage::disk('public')->exists('productos')) {
            Storage::disk('public')->makeDirectory('productos');
        }

        @list($type, $raw) = explode(';', $base64String);
        @list(, $raw)      = explode(',', $raw);
        $imageData = base64_decode($raw);

        // Validar tamaño máximo del base64 (5MB = 5 * 1024 * 1024 bytes)
        if ($imageData === false || strlen($imageData) > 5242880) {
            throw new \InvalidArgumentException('La imagen en Base64 es inválida o supera los 5 MB.');
        }

        // Determinar extensión segura según el tipo MIME
        $extension = 'webp';
        if (str_contains($type, 'jpeg') || str_contains($type, 'jpg')) {
            $extension = 'jpg';
        } elseif (str_contains($type, 'png')) {
            $extension = 'png';
        }

        $filename = $prefix . '_' . uniqid() . '.' . $extension;
        Storage::disk('public')->put('productos/' . $filename, $imageData);

        return 'productos/' . $filename;
    }

    public function destroy($id)
    {
        $this->productoService->deleteProductoById($id);
        return response()->json(['message' => 'Producto desactivado del catálogo correctamente.'], 200);
    }
}
