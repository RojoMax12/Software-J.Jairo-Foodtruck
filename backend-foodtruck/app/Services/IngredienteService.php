<?php
namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Models\Movimientos;
use App\Repositories\IngredienteRepository;
use Illuminate\Support\Facades\DB;

# Servicio Ingrediente
class IngredienteService
{
    protected $ingredienteRepository;

    public function __construct(IngredienteRepository $ingredienteRepository)
    {
        $this->ingredienteRepository = $ingredienteRepository;
    }

    public function createIngrediente($data)
    {
        if (empty($data['nombre'])) {
            throw new \InvalidArgumentException('El nombre del ingrediente es obligatorio.');
        }
        if (isset($data['cantidad']) && $data['cantidad'] < 0) {
            throw new \InvalidArgumentException('La cantidad no puede ser negativa.');
        }

        return DB::transaction(function () use ($data) {
            $ing = $this->ingredienteRepository->createIngrediente($data);
            if ($ing) {
                HistorialMovimiento::registrar(
                    'stock',
                    'crear',
                    'Nuevo ingrediente/stock agregado',
                    $ing->nombre,
                    'Stock inicial: ' . ($ing->cantidad_actual ?? $ing->cantidad ?? 0) . ' ' . ($ing->unidad_medida ?? 'uds')
                );

                // Registrar movimiento de stock inicial en Kardex si aplica
                $stockInicial = (float)($ing->cantidad_actual ?? $ing->cantidad ?? 0);
                if ($stockInicial > 0) {
                    Movimientos::create([
                        'id_ingrediente' => $ing->id_ingrediente,
                        'cantidad' => $stockInicial,
                        'tipo_movimiento' => 'Entrada',
                        'fecha_movimiento' => now()->toDateString(),
                    ]);
                }
            }
            return $ing;
        });
    }

    public function getAllIngredientes()
    {
        return $this->ingredienteRepository->getAllIngredientes();
    }

    public function getIngredienteById($id)
    {
        return $this->ingredienteRepository->getIngredienteById($id);
    }

    public function getIngredienteByNombre($nombre)
    {
        return $this->ingredienteRepository->getIngredienteByNombre($nombre);
    }

    public function getIngredientesDisponibles()
    {
        return $this->ingredienteRepository->getIngredientesDisponibles();
    }

    public function getProductoIngredienteByIngredienteId($id)
    {
        return $this->ingredienteRepository->getProductoIngredienteByIngredienteId($id);
    }

    public function updateIngrediente($id, $data)
    {
        if (isset($data['cantidad_actual']) && $data['cantidad_actual'] < 0) {
            throw new \InvalidArgumentException('La cantidad no puede ser negativa.');
        }

        if (isset($data['cantidad_actual'])) {
            $qty = (int)$data['cantidad_actual'];
            if ($qty <= 0) {
                $data['disponible'] = false;
            }
        }

        return DB::transaction(function () use ($id, $data) {
            $ingAnterior = $this->ingredienteRepository->getIngredienteById($id);
            $stockAnterior = $ingAnterior ? (float)($ingAnterior->cantidad_actual ?? $ingAnterior->cantidad ?? 0) : 0;

            $ing = $this->ingredienteRepository->updateIngrediente($id, $data);
            if ($ing) {
                $nombre = $ing->nombre ?? ($ingAnterior->nombre ?? "Ingrediente #$id");
                $detalles = [];
                if (isset($data['cantidad_actual'])) {
                    $detalles[] = 'Stock ajustado a: ' . $data['cantidad_actual'] . ' ' . ($ing->unidad_medida ?? 'uds');
                }
                if (isset($data['disponible'])) {
                    $detalles[] = $data['disponible'] ? 'Marcado disponible' : 'Marcado sin stock';
                }
                $detalleStr = !empty($detalles) ? implode(' · ', $detalles) : 'Modificación de ingrediente';

                HistorialMovimiento::registrar(
                    'stock',
                    'editar',
                    'Ajuste de stock / ingrediente',
                    $nombre,
                    $detalleStr
                );

                // Registrar movimiento en Kardex si hubo cambio de stock
                if (isset($data['cantidad_actual'])) {
                    $stockNuevo = (float)$data['cantidad_actual'];
                    $diff = $stockNuevo - $stockAnterior;
                    if (abs($diff) > 0.001) {
                        Movimientos::create([
                            'id_ingrediente' => $id,
                            'cantidad' => abs($diff),
                            'tipo_movimiento' => $diff > 0 ? 'Entrada' : 'Salida',
                            'fecha_movimiento' => now()->toDateString(),
                        ]);
                    }
                }
            }
            return $ing;
        });
    }

    public function deleteIngredienteById($id)
    {
        $ing = $this->ingredienteRepository->getIngredienteById($id);
        $nombre = $ing ? $ing->nombre : "Ingrediente #$id";
        $deleted = $this->ingredienteRepository->deleteIngredienteById($id);
        if ($deleted) {
            HistorialMovimiento::registrar(
                'stock',
                'eliminar',
                'Ingrediente eliminado del inventario',
                $nombre,
                'Eliminado de control de stock'
            );
        }
        return $deleted;
    }
}