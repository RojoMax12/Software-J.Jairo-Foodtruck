<?php

namespace App\Services;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Models\Usuario_distribuidores;

class Usuario_distribuidoresServices
{
    protected $usuarioDistribuidoresRepository;

    public function __construct(Usuario_distribuidoresRepository $usuarioDistribuidoresRepository)
    {
        $this->usuarioDistribuidoresRepository = $usuarioDistribuidoresRepository;
    }

    public function getAllUsuariosDistribuidores()
    {
        return $this->usuarioDistribuidoresRepository->getAllUsuariosDistribuidores();
    }

    public function getUsuarioDistribuidorById($id)
    {
        return $this->usuarioDistribuidoresRepository->getUsuarioDistribuidorById($id);
    }

    public function createUsuarioDistribuidor($data)
    {
        return $this->usuarioDistribuidoresRepository->createUsuarioDistribuidor($data);
    }

    public function updateUsuarioDistribuidor($id, $data)
    {
        return $this->usuarioDistribuidoresRepository->updateUsuarioDistribuidor($id, $data);
    }

    public function deleteUsuarioDistribuidor($id)
    {
        return $this->usuarioDistribuidoresRepository->deleteUsuarioDistribuidor($id);
    }
}