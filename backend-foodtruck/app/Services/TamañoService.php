<?php

namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Repositories\TamañoRepository;

class TamañoService
{
    protected $tamañoRepository;

    public function __construct(TamañoRepository $tamañoRepository)
    {
        $this->tamañoRepository = $tamañoRepository;
    }

    public function createTamaño($data)
    {
        $tam = $this->tamañoRepository->createTamaño($data);
        if ($tam) {
            HistorialMovimiento::registrar(
                'tamaño',
                'crear',
                'Nuevo tamaño o formato agregado',
                $tam->nombre,
                'Formato creado en catálogo'
            );
        }
        return $tam;
    }

    public function getAllTamaños()
    {
        return $this->tamañoRepository->getAllTamaños();
    }

    public function getTamañoById($id)
    {
        return $this->tamañoRepository->getTamañoById($id);
    }

    public function updateTamaño($id, $data)
    {
        $tamAnterior = $this->tamañoRepository->getTamañoById($id);
        $tam = $this->tamañoRepository->updateTamaño($id, $data);
        if ($tam) {
            HistorialMovimiento::registrar(
                'tamaño',
                'editar',
                'Tamaño o formato modificado',
                $tam->nombre ?? ($tamAnterior->nombre ?? "Tamaño #$id"),
                'Modificación de formato en catálogo'
            );
        }
        return $tam;
    }

    public function deleteTamañoById($id)
    {
        $tam = $this->tamañoRepository->getTamañoById($id);
        $nombre = $tam ? $tam->nombre : "Tamaño #$id";
        $deleted = $this->tamañoRepository->deleteTamañoById($id);
        if ($deleted) {
            HistorialMovimiento::registrar(
                'tamaño',
                'eliminar',
                'Tamaño o formato eliminado',
                $nombre,
                'Eliminado de catálogo'
            );
        }
        return $deleted;
    }
}