<?php
namespace App\Services;

use App\Models\HistorialMovimiento;
use App\Repositories\OfertaRepository;

# Servicio Oferta
class OfertaService
{
    protected $ofertaRepository;

    public function __construct(OfertaRepository $ofertaRepository)
    {
        $this->ofertaRepository = $ofertaRepository;
    }

    public function createOferta($data)
    {
        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            throw new \InvalidArgumentException('El precio de oferta no puede ser negativo.');
        }

        $oferta = $this->ofertaRepository->createOferta($data);
        if ($oferta) {
            HistorialMovimiento::registrar(
                'oferta',
                'crear',
                'Nueva oferta promocional creada',
                $oferta->nombre ?? ('Oferta #' . $oferta->id_ofertas),
                'Precio de oferta: $' . number_format($oferta->precio_oferta ?? 0, 0, ',', '.'),
                $oferta->precio_oferta ?? 0
            );
        }
        return $oferta;
    }

    public function getAllOfertas()
    {
        return $this->ofertaRepository->getAllOfertas();
    }

    public function getOfertaById($id)
    {
        return $this->ofertaRepository->getOfertaById($id);
    }

    public function getOfertasByTipo($tipo)
    {
        return $this->ofertaRepository->getOfertasByTipo($tipo);
    }

    public function getOfertaProductosByOfertaId($id)
    {
        return $this->ofertaRepository->getOfertaProductosByOfertaId($id);
    }

    public function updateOferta($id, $data)
    {
        if (isset($data['precio_oferta']) && $data['precio_oferta'] < 0) {
            throw new \InvalidArgumentException('El precio de oferta no puede ser negativo.');
        }

        $ofAnterior = $this->ofertaRepository->getOfertaById($id);
        $oferta = $this->ofertaRepository->updateOferta($id, $data);
        if ($oferta) {
            HistorialMovimiento::registrar(
                'oferta',
                'editar',
                'Oferta promocional modificada',
                $oferta->nombre ?? ($ofAnterior->nombre ?? "Oferta #$id"),
                'Precio actualizado a $' . number_format($oferta->precio_oferta ?? 0, 0, ',', '.'),
                $oferta->precio_oferta ?? 0
            );
        }
        return $oferta;
    }

    public function deleteOfertaById($id)
    {
        $of = $this->ofertaRepository->getOfertaById($id);
        $nombre = $of ? ($of->nombre ?? "Oferta #$id") : "Oferta #$id";
        $deleted = $this->ofertaRepository->deleteOfertaById($id);
        if ($deleted) {
            HistorialMovimiento::registrar(
                'oferta',
                'eliminar',
                'Oferta promocional eliminada',
                $nombre,
                'Eliminada de promociones activas'
            );
        }
        return $deleted;
    }
}