<?php
namespace App\Services;

use App\Repositories\UsuarioRepository;

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function createUsuario($data)
    {
        if (empty($data['nombre'])) {
            throw new \InvalidArgumentException('El nombre del usuario es obligatorio.');
        }

        return $this->usuarioRepository->createUsuario($data);
    }

    public function getAllUsuarios()
    {
        return $this->usuarioRepository->getAllUsuarios();
    }

    public function getUsuarioById($id)
    {
        return $this->usuarioRepository->getUsuarioById($id);
    }

    public function getUsuarioByNombre($nombre)
    {
        return $this->usuarioRepository->getUsuarioByNombre($nombre);
    }

    public function getUsuariosByRolId($idRol)
    {
        return $this->usuarioRepository->getUsuariosByRolId($idRol);
    }

    public function getUsuariosActivos()
    {
        return $this->usuarioRepository->getUsuariosActivos();
    }

    public function getPedidosByUsuarioId($id)
    {
        return $this->usuarioRepository->getPedidosByUsuarioId($id);
    }

    public function getCajasByUsuarioId($id)
    {
        return $this->usuarioRepository->getCajasByUsuarioId($id);
    }

    public function updateUsuario($id, $data)
    {
        return $this->usuarioRepository->updateUsuario($id, $data);
    }

    public function deleteUsuarioById($id)
    {
        return $this->usuarioRepository->deleteUsuarioById($id);
    }
}
