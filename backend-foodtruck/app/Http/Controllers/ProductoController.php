<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use App\Support\CatalogCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    protected $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    public function index(Request $request)
    {
        $summary = $request->boolean('summary');
        $data = $summary
            ? CatalogCache::rememberSummaryProducts(fn () => $this->productoService->getPublicProductos(true))
            : CatalogCache::rememberProducts(fn () => $this->productoService->getPublicProductos(false));
        return CatalogCache::respondWithEtag($request, $data);
    }

    public function show($id)
    {
        return response()->json($this->productoService->getProductoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'id_categoria' => 'nullable|integer|exists:categorias,id_categoria',
            'grupo' => 'nullable|string|max:120',
            'tipo_armado' => 'nullable|string|max:40',
            'cantidad_incluida' => 'nullable|integer|min:0|max:50',
            'precio_ingrediente_extra' => 'nullable|integer|min:0',
            'activo' => 'sometimes|boolean',
            'disponible' => 'sometimes|boolean',
            'imagen' => 'nullable|string',
            'imagen_posicion' => 'nullable|string|max:40',
            'imagen_zoom' => 'nullable|numeric|min:0.5|max:4',
            'imagen_ajuste' => 'nullable|string|max:20',
            'precio_base' => 'nullable|numeric|min:0',
            'precio' => 'nullable|numeric|min:0',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'precios_tamanos' => 'nullable|array',
            'ingredientes' => 'nullable|array',
        ]);

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
        if (!empty($data['imagen']) && is_string($data['imagen']) && str_starts_with($data['imagen'], 'data:image')) {
            if (!preg_match('#^data:image/(jpeg|png|jpg|webp);base64,#', $data['imagen'])) {
                return response()->json(['message' => 'El formato Base64 debe ser JPEG, PNG o WEBP.'], 422);
            }
            $data['imagen'] = $this->saveBase64Image($data['imagen'], 'prod_new_' . time());
        }

        $producto = $this->productoService->createProducto($data);

        // Sincronizar precios por tamaño e ingredientes si fueron enviados
        if ($producto) {
            $this->syncTamañosPrecios($producto, $data);
            $this->syncIngredientes($producto, $data);
        }

        CatalogCache::invalidate();

        return response()->json($this->productoService->getProductoById($producto->id_producto ?? $producto->id) ?? $producto, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'id_categoria' => 'nullable|integer|exists:categorias,id_categoria',
            'grupo' => 'nullable|string|max:120',
            'tipo_armado' => 'nullable|string|max:40',
            'cantidad_incluida' => 'nullable|integer|min:0|max:50',
            'precio_ingrediente_extra' => 'nullable|integer|min:0',
            'activo' => 'sometimes|boolean',
            'disponible' => 'sometimes|boolean',
            'imagen' => 'nullable|string',
            'imagen_posicion' => 'nullable|string|max:40',
            'imagen_zoom' => 'nullable|numeric|min:0.5|max:4',
            'imagen_ajuste' => 'nullable|string|max:20',
            'precio_base' => 'nullable|numeric|min:0',
            'precio' => 'nullable|numeric|min:0',
            'id_tamaño' => 'nullable|integer|exists:tamaños,id_tamaño',
            'precios_tamanos' => 'nullable|array',
        ]);

        // Normalización si se envían valores vacíos
        if (array_key_exists('descripcion', $data) && ($data['descripcion'] === null || trim($data['descripcion']) === '')) {
            $data['descripcion'] = $data['nombre'] ?? 'Producto de la carta gastronómica';
        }

        // Si la imagen viene como base64 en el cuerpo, procesarla y guardarla en storage
        if (!empty($data['imagen']) && is_string($data['imagen']) && str_starts_with($data['imagen'], 'data:image')) {
            if (!preg_match('#^data:image/(jpeg|png|jpg|webp);base64,#', $data['imagen'])) {
                return response()->json(['message' => 'El formato Base64 debe ser JPEG, PNG o WEBP.'], 422);
            }
            $data['imagen'] = $this->saveBase64Image($data['imagen'], 'prod_' . $id . '_' . time());
        }

        $producto = $this->productoService->updateProducto($id, $data);

        // Sincronizar precios por tamaño e ingredientes si fueron enviados
        if ($producto) {
            $this->syncTamañosPrecios($producto, $data);
            $this->syncIngredientes($producto, $data);
        }

        CatalogCache::invalidate();

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
                if ($precio !== null && (float)$precio > 0) {
                    $idTamaño = $data['id_tamaño']
                        ?? \App\Models\Tamaño::whereRaw('LOWER(nombre) = ?', ['normal'])->value('id_tamaño')
                        ?? \App\Models\Tamaño::value('id_tamaño');
                    if ($idTamaño) {
                        $syncData[(int)$idTamaño] = ['precio' => (float)$precio];
                    }
                }
            }

            if (!empty($syncData)) {
                $producto->tamaños()->sync($syncData);
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo sincronizar producto_tamaño: ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza la receta base de ingredientes manteniendo extras y configuraciones previas.
     */
    protected function syncIngredientes($producto, array $data): void
    {
        if (!array_key_exists('ingredientes', $data) || !is_array($data['ingredientes'])) {
            return;
        }

        try {
            $selectedIds = [];
            foreach ($data['ingredientes'] as $entry) {
                if (is_numeric($entry)) {
                    $ing = \App\Models\Ingrediente::find((int)$entry);
                } else {
                    $name = is_array($entry)
                        ? trim((string)($entry['nombre'] ?? $entry['name'] ?? ''))
                        : trim((string)$entry);
                    if ($name === '') {
                        continue;
                    }
                    $ing = \App\Models\Ingrediente::whereRaw('LOWER(nombre) = ?', [mb_strtolower($name, 'UTF-8')])->first();
                    if (!$ing) {
                        $ing = \App\Models\Ingrediente::create([
                            'nombre' => $name,
                            'cantidad_actual' => 0,
                            'cantidad_minima' => 0,
                            'disponible' => true,
                            'tipo_inventario' => 'unidad',
                            'unidades_por_pack' => 1,
                            'es_cuantificable' => true,
                            'fecha_de_ingreso' => now(),
                        ]);
                    }
                }
                if ($ing) {
                    $selectedIds[] = (int)$ing->id_ingrediente;
                }
            }

            $selectedIds = array_values(array_unique($selectedIds));

            \App\Models\Producto_ingrediente::where('id_producto', $producto->id_producto)
                ->where('incluido_por_defecto', true)
                ->when(!empty($selectedIds), fn ($q) => $q->whereNotIn('id_ingrediente', $selectedIds))
                ->delete();

            foreach ($selectedIds as $ingId) {
                $exists = \App\Models\Producto_ingrediente::where('id_producto', $producto->id_producto)
                    ->where('id_ingrediente', $ingId)
                    ->exists();

                if (!$exists) {
                    \App\Models\Producto_ingrediente::create([
                        'id_producto' => $producto->id_producto,
                        'id_ingrediente' => $ingId,
                        'cantidad' => 1,
                        'incluido_por_defecto' => true,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo sincronizar producto_ingrediente: ' . $e->getMessage());
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
            } elseif (filter_var($raw, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $raw)) {
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
        CatalogCache::invalidate();

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
        CatalogCache::invalidate();
        return response()->json(['message' => 'Producto desactivado del catálogo correctamente.'], 200);
    }
}
