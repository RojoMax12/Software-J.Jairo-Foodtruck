<?php
namespace App\Repositories;
use App\Models\Ingrediente;

# Repositorio Ingrediente
class IngredienteRepository
{
    # Create
    public function createIngrediente($data)
    {
        return Ingrediente::create($data);
    }

    # Geters
    public function getAllIngredientes()
    {
        return Ingrediente::all();
    }

    public function getIngredienteById($id)
    {
        return Ingrediente::find($id);
    }

    public function getIngredienteByNombre($nombre)
    {
        return Ingrediente::where('nombre', $nombre)->first();
    }

    public function getIngredientesDisponibles()
    {
        return Ingrediente::where('disponible', true)->get();
    }

    public function getProductoIngredienteByIngredienteId($id)
    {
        $ingrediente = Ingrediente::find($id);
        if ($ingrediente) {
            return $ingrediente->producto_ingrediente;
        }
        return null;
    }

    # Seters
    public function updateIngrediente($id, $data)
    {
        $ingrediente = Ingrediente::find($id);
        if ($ingrediente) {
            $ingrediente->update($data);
            return $ingrediente;
        }
        return null;
    }

    # Delete
    public function deleteIngredienteById($id)
    {
        $ingrediente = Ingrediente::find($id);
        if ($ingrediente) {
            $ingrediente->delete();
            return true;
        }
        return false;
    }
}