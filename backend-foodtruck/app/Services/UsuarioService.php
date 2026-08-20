<?php
namespace App\Services;

use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\DuplicateEmailException;

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function createUsuario($data)
    {

        if ($this->usuarioRepository->existsByEmail($data['correo'])) {
            throw new DuplicateEmailException('El correo electrónico ya está registrado.');
        }

        $data['contrasena'] = Hash::make($data['contrasena']);
        
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

    public function getUsuariosAdministrativos()
    {
        return $this->usuarioRepository->getUsuariosAdministrativos();
    }

    public function updateUsuario($id, $data)
    {
        if (
            isset($data['correo']) &&
            $this->usuarioRepository->existsByEmailExceptUser(
                $data['correo'],
                $id
            )
        ) {
            throw new DuplicateEmailException(
                'El correo electrónico ya está registrado.'
            );
        }

        if (!empty($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        } else {
            unset($data['contrasena']);
        }

        return $this->usuarioRepository->updateUsuario($id, $data);
    }

    public function deleteUsuarioById($id)
    {
        return $this->usuarioRepository->deleteUsuarioById($id);
    }
}
