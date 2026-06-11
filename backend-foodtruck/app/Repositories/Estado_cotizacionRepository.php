<?php

namespace App\Repositories;
use App\Models\Estado_cotizacion;

class Estado_cotizacionRepository {

    public function getAllEstado_cotizacion()
    {
        return Estado_cotizacion::all();
    }

    public function getEstado_cotizacionById($id){

        return Estado_cotizacion::find($id);

    }

    public function createEstado_cotizacion($data){

        return Estado_cotizacion::create($data);
    
    }

    public function updateEstado_cotizacion($id, $data) {

        $Estado_cotizacion = Estado_cotizacion::find($id);
        if($Estado_cotizacion) {
            $Estado_cotizacion ->update($data);
            return $Estado_cotizacion;
        }
        return null;
    }

    public function deleteEstado_cotizacion($id)
    {

        $Estado_cotizacion = Estado_cotizacion::find($id);
        if ($Estado_cotizacion) {
            $Estado_cotizacion->delete();
            return true;
        }
        return false;
    }






























}

