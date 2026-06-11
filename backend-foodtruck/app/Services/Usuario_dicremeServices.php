<?php

namespace App\Services;
use App\Models\Usuario_dicreme;
use App\Repositories\Usuario_dicremeRepository;

class Usuario_dicremeServices
{
    protected $usuarioDicremeRepository;

    public function __construct(Usuario_dicremeRepository $usuarioDicremeRepository)
    {
        $this->usuarioDicremeRepository = $usuarioDicremeRepository;
    }

    public function getAllUsuariosDicreme()
    {
        return $this->usuarioDicremeRepository->getAllUsuariosDicreme();
    }

    public function getUsuarioDicremeById($id)
    {
        return $this->usuarioDicremeRepository->getUsuarioDicremeById($id);
    }

    public function createUsuarioDicreme($data)
    {
        return $this->usuarioDicremeRepository->createUsuarioDicreme($data);
    }

    public function updateUsuarioDicreme($id, $data)
    {
        return $this->usuarioDicremeRepository->updateUsuarioDicreme($id, $data);
    }

    public function deleteUsuarioDicreme($id)
    {
        return $this->usuarioDicremeRepository->deleteUsuarioDicreme($id);
    }
}

