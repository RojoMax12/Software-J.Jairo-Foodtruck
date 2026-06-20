<?php
namespace App\Repositories;
use App\Models\Rol;

# Repositorio Rol
class RolRepository
{
    # Create
    public function createRol($data)
    {
        return Rol::create($data);
    }

    # Geters
    public function getAllRoles()
    {
        return Rol::all();
    }

    public function getRolById($id)
    {
        return Rol::find($id);
    }

    public function getRolByNombre($nombre)
    {
        return Rol::where('nombre_rol', $nombre)->first();
    }

    public function getUsuariosByRolId($id)
    {
        $rol = Rol::find($id);
        if ($rol) {
            return $rol->usuarios;
        }
        return null;
    }

    # Seters
    public function updateRol($id, $data)
    {
        $rol = Rol::find($id);
        if ($rol) {
            $rol->update($data);
            return $rol;
        }
        return null;
    }

    # Delete
    public function deleteRolById($id)
    {
        $rol = Rol::find($id);
        if ($rol) {
            $rol->delete();
            return true;
        }
        return false;
    }
}