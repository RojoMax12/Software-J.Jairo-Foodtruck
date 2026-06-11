<?php

namespace App\Repositories;
use App\Models\Usuario_dicreme;


class Usuario_dicremeRepository
{
    protected $usuarioDicreme;

    public function __construct(Usuario_dicreme $usuarioDicreme)
    {
        $this->usuarioDicreme = $usuarioDicreme;
    }

    public function getAllUsuariosDicreme()
    {
        return $this->usuarioDicreme->all();
    }

    public function getUsuarioDicremeById($id)
    {
        return $this->usuarioDicreme->find($id);
    }

    public function createUsuarioDicreme($data)
    {
        return $this->usuarioDicreme->create($data);
    }

    public function updateUsuarioDicreme($id, $data)
    {
        $usuarioDicreme = $this->getUsuarioDicremeById($id);
        $usuarioDicreme->update($data);
        return $usuarioDicreme;
    }

    public function deleteUsuarioDicreme($id)
    {
        $usuarioDicreme = $this->getUsuarioDicremeById($id);
        $usuarioDicreme->delete();
        return $usuarioDicreme;
    }

    public function getUsuarioDicremeByCorreo($correo){

        return Usuario_dicreme::where("correo_electronico", $correo)->first();
    }
}