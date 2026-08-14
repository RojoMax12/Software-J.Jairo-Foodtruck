<?php
namespace App\Repositories;
use App\Models\Usuario;

# Repositorio Usuario
class UsuarioRepository
{
    # Create
    public function createUsuario($data)
    {
        return Usuario::create($data);
    }

    # Geters
    public function getAllUsuarios()
    {
        return Usuario::all();
    }

    public function getUsuarioById($id)
    {
        return Usuario::find($id);
    }

    public function getUsuarioByNombre($nombre)
    {
        return Usuario::where('nombre', $nombre)->first();
    }

    public function getUsuariosByRolId($idRol)
    {
        return Usuario::where('id_rol', $idRol)->get();
    }

    public function getUsuariosActivos()
    {
        return Usuario::where('estado', true)->get();
    }

    public function getPedidosByUsuarioId($id)
    {
        $usuario = Usuario::find($id);
        if ($usuario) {
            return $usuario->pedido;
        }
        return null;
    }

    public function getCajasByUsuarioId($id)
    {
        $usuario = Usuario::find($id);
        if ($usuario) {
            return $usuario->caja;
        }
        return null;
    }

    # Seters
    public function updateUsuario($id, $data)
    {
        $usuario = Usuario::find($id);
        if ($usuario) {
            $usuario->update($data);
            return $usuario;
        }
        return null;
    }

    # Delete
    public function deleteUsuarioById($id)
    {
        $usuario = Usuario::find($id);
        if ($usuario) {
            $usuario->delete();
            return true;
        }
        return false;
    }
}