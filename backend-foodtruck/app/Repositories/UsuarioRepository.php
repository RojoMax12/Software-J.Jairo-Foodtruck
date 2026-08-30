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

    public function existsByEmail($correo)
    {
        return Usuario::where('correo', $correo)->exists();
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

    public function getUsuarioByEmail($correo)
    {
        return Usuario::where('correo', $correo)->first();
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

    public function getUsuariosAdministrativos()
    {
        return Usuario::whereHas('rol', function ($query) {
            $query->whereIn('nombre_rol', [
                'Admin',
                'Trabajador'
            ]);
        })
        ->orderBy('id_usuario')
        ->get();
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

    public function existsByEmailExceptUser($correo, $id)
    {
        return Usuario::where('correo', $correo)
            ->where('id_usuario', '!=', $id)
            ->exists();
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