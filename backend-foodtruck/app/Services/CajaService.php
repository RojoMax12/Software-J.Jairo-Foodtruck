<?php
namespace App\Services;
use App\Repositories\CajaRepository;
# Servicio Caja
class CajaService
{
    protected $cajaRepository;

    public function __construct(CajaRepository $cajaRepository)
    {
        $this->cajaRepository = $cajaRepository;
    }

    public function createCaja($data)
    {
        if (isset($data['total_ventas']) && $data['total_ventas'] < 0) {
            throw new \InvalidArgumentException('El total de ventas no puede ser negativo.');
        }
        if (isset($data['total_recaudado']) && $data['total_recaudado'] < 0) {
            throw new \InvalidArgumentException('El total recaudado no puede ser negativo.');
        }

        return $this->cajaRepository->createCaja($data);
    }

    public function getAllCajas()
    {
        return $this->cajaRepository->getAllCajas();
    }

    public function getCajaById($id)
    {
        return $this->cajaRepository->getCajaById($id);
    }

    public function getCajasByUsuarioId($idUsuario)
    {
        return $this->cajaRepository->getCajasByUsuarioId($idUsuario);
    }

    public function getCajaByVentaId($idVenta)
    {
        return $this->cajaRepository->getCajaByVentaId($idVenta);
    }

    public function updateCaja($id, $data)
    {
        if (isset($data['total_ventas']) && $data['total_ventas'] < 0) {
            throw new \InvalidArgumentException('El total de ventas no puede ser negativo.');
        }
        if (isset($data['total_recaudado']) && $data['total_recaudado'] < 0) {
            throw new \InvalidArgumentException('El total recaudado no puede ser negativo.');
        }

        return $this->cajaRepository->updateCaja($id, $data);
    }

    public function deleteCajaById($id)
    {
        return $this->cajaRepository->deleteCajaById($id);
    }
}