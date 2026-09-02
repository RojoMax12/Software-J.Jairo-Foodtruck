<?php

namespace App\Services;

use App\Repositories\RolRepository;


class RolService
{
    protected $rolRepository;

    public function __construct(RolRepository $rolRepository)
    {
        $this->rolRepository = $rolRepository;
    }

    public function getAllRoles()
    {
        return $this->rolRepository->getAllRoles();
    }

    public function getRoleById($id)
    {
        return $this->rolRepository->getRolById($id);
    }

    public function createRole($data)
    {
        return $this->rolRepository->createRol($data);
    }

    public function updateRole($id, $data)
    {
        return $this->rolRepository->updateRol($id, $data);
    }

    public function deleteRole($id)
    {
        return $this->rolRepository->deleteRolById($id);
    }
}