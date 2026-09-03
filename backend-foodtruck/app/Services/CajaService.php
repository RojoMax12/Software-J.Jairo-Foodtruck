<?php
namespace App\Services;

use App\Models\HistorialMovimiento;
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

        $caja = $this->cajaRepository->createCaja($data);
        if ($caja) {
            HistorialMovimiento::registrar(
                'caja',
                'apertura',
                'Apertura de turno de caja',
                'Sesión de Caja #' . $caja->id_caja,
                'Turno abierto con fondo de caja: $' . number_format($caja->fondo_inicial ?? 0, 0, ',', '.'),
                $caja->fondo_inicial ?? 0
            );
        }
        return $caja;
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

        $caja = $this->cajaRepository->updateCaja($id, $data);
        if ($caja) {
            if (isset($data['estado']) && $data['estado'] === 'cerrada') {
                HistorialMovimiento::registrar(
                    'caja',
                    'cierre',
                    'Cierre de turno y arqueo de caja',
                    'Sesión de Caja #' . $caja->id_caja,
                    'Ventas totales: $' . number_format($caja->total_ventas ?? 0, 0, ',', '.') . ' · Recaudado: $' . number_format($caja->total_recaudado ?? 0, 0, ',', '.'),
                    $caja->total_recaudado ?? 0
                );
            }
        }
        return $caja;
    }

    public function deleteCajaById($id)
    {
        return $this->cajaRepository->deleteCajaById($id);
    }
}