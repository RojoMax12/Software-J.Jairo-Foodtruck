<?php

namespace App\Repositories;
use App\Models\Despacho;

class DespachoRepository
{
    public function getAllDespachos()
    {
        return Despacho::all();
    }

    public function getDespachoById($id)
    {
        return Despacho::find($id);
    }

    public function createDespacho($data)
    {
        return Despacho::create($data);
    }

    public function updateDespacho($id, $data)
    {
        $despacho = Despacho::find($id);
        if ($despacho) {
            $despacho->update($data);
            return $despacho;
        }
        return null;
    }

    public function deleteDespacho($id)
    {
        $despacho = Despacho::find($id);
        if ($despacho) {
            $despacho->delete();
            return true;
        }
        return false;
    }

    public function getDespachoByIdpedido($id){

        return Despacho::where("id_pedido", $id)->first();

    }
}