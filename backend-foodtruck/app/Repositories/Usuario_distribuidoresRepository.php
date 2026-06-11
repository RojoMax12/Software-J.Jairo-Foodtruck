<?php

namespace App\Repositories;
use App\Models\Usuario_distribuidores;

class Usuario_distribuidoresRepository
{
    public function getAllUsuariosDistribuidores()
    {
        return Usuario_distribuidores::all();
    }

    public function getUsuarioDistribuidorById($id)
    {
        return Usuario_distribuidores::find($id);
    }

    public function createUsuarioDistribuidor($data)
    {
        return Usuario_distribuidores::create($data);
    }

    public function updateUsuarioDistribuidor($id, $data)
    {
        $usuarioDistribuidor = Usuario_distribuidores::find($id);
        if ($usuarioDistribuidor) {
            $usuarioDistribuidor->update($data);
            return $usuarioDistribuidor;
        }
        return null;
    }

    public function deleteUsuarioDistribuidor($id)
    {
        $usuarioDistribuidor = Usuario_distribuidores::find($id);
        if ($usuarioDistribuidor) {
            $usuarioDistribuidor->delete();
            return true;
        }
        return false;
    }


    public function getUsuarioDistribuidorByCorreo($correo){
        
        return Usuario_distribuidores::where("correo_electronico", $correo)->first();
    }
}
