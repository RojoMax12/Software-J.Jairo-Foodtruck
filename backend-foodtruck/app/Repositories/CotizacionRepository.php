<?php

namespace App\Repositories;
use App\Models\Cotizacion;

class CotizacionRepository
{
    public function createCotizacion($data)
    {
        return Cotizacion::create($data);
    }

    public function getCotizacionById($id)
    {
        return Cotizacion::find($id);
    }

    public function updateCotizacion($id, $data)
    {
        $cotizacion = Cotizacion::find($id);
        if ($cotizacion) {
            $cotizacion->update($data);
            return $cotizacion;
        }
        return null;
    }

    public function TomarCotizacionUsuarioDicreme($id, $id_usuario_dicreme)
    {
        $cotizacion = Cotizacion::find($id);
        if ($cotizacion) {
            $cotizacion->id_usuario_dicreme = $id_usuario_dicreme;
            $cotizacion->id_estado_cotizacion = 2;
            $cotizacion->save();
            return $cotizacion;
        }
        return null;

    }

    public function DejarCotizacionUsuarioDicreme($id)
    {
        $cotizacion = Cotizacion::find($id);

        if ($cotizacion) {
            $cotizacion->id_usuario_dicreme = null;
            $cotizacion->id_estado_cotizacion = 1; 
            $cotizacion->save();
            return $cotizacion;
        }

        return null; 
    }

    public function cotizacioncompletada($id){

    $cotizacion = Cotizacion::find($id);

    if($cotizacion) {
        $cotizacion->id_estado_cotizacion = 3; 
        $cotizacion->save();
        return $cotizacion;
        }

        return null;
    }

    public function CancelarCotizacion($id)
    {
        $cotizacion = Cotizacion::find($id);

        if ($cotizacion) {
            $cotizacion->id_estado_cotizacion = 4; 
            $cotizacion->save();
            return $cotizacion;
        }

        return null; 
    }

    public function deleteCotizacion($id)
    {
        $cotizacion = Cotizacion::find($id);
        if ($cotizacion) {
            $cotizacion->delete();
            return true;
        }
        return false;
    }

    public function getAllCotizaciones()
    {
        return Cotizacion::all();
    }

    public function getCotizacionConProductos($id)
    {
        return Cotizacion::with('cotizacionProductos')->find($id);
    }

    public function getCotizacionesByUsuario($id)
    { 
        return Cotizacion::where('id_usuario_dicreme', $id)->get();
    }

    public function getCotizacionesByUsuarioDistribuidor($id) 
    {
        return Cotizacion::where('id_distribuidor', $id)->get();
    }
}