<?php

namespace App\Services;
use App\Repositories\DespachoRepository;
use App\Models\Despacho;

class DespachoServices
{
    protected $despachoRepository;

    public function __construct(DespachoRepository $despachoRepository)
    {
        $this->despachoRepository = $despachoRepository;
    }

    public function getAllDespachos()
    {
        return $this->despachoRepository->getAllDespachos();
    }

    public function getDespachoById($id)
    {
        return $this->despachoRepository->getDespachoById($id);
    }

    public function createDespacho($data)
    {
        return $this->despachoRepository->createDespacho($data);
    }

    public function updateDespacho($id, $data)
    {
        return $this->despachoRepository->updateDespacho($id, $data);
    }

    public function deleteDespacho($id)
    {
        return $this->despachoRepository->deleteDespacho($id);
    }

    public function despachosbyidpedido($id){
        return $this->despachoRepository->getDespachoByIdpedido($id);
    }
}
