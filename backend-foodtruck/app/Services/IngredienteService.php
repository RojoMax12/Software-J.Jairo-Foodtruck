<?php
namespace App\Services;
use App\Repositories\IngredienteRepository;
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

        return $this->ingredienteRepository->createIngrediente($data);
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
        if (isset($data['cantidad']) && $data['cantidad'] < 0) {
            throw new \InvalidArgumentException('La cantidad no puede ser negativa.');
        }

        $ingrediente = $this->ingredienteRepository->updateIngrediente($id, $data);

        // Si la cantidad llega a 0, se marca automáticamente como no disponible
        if ($ingrediente && $ingrediente->cantidad <= 0 && $ingrediente->disponible) {
            $ingrediente = $this->ingredienteRepository->updateIngrediente($id, ['disponible' => false]);
        }

        return $ingrediente;
    }

    public function deleteIngredienteById($id)
    {
        return $this->ingredienteRepository->deleteIngredienteById($id);
    }
}