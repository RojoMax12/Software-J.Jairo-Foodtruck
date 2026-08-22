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
        return $this->rolRepository->getRoleById($id);
    }

    public function createRole($data)
    {
        return $this->rolRepository->createRole($data);
    }

    public function updateRole($id, $data)
    {
        return $this->rolRepository->updateRole($id, $data);
    }

    public function deleteRole($id)
    {
        return $this->rolRepository->deleteRole($id);
    }
}