<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\Estado_cotizacionServices;


class Estado_cotizacionController extends Controller{

    protected $EstadoCotizacionServices;

    public function __construct(Estado_cotizacionServices $EstadoCotizacionServices)
    {
        $this->EstadoCotizacionServices = $EstadoCotizacionServices;
    }

    public function index()
    {
 
    return response() ->json($this->EstadoCotizacionServices->getAllEstadosCotizacion());

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_estado' => 'required|string|max:255'
        ]);

        return response()->json($this->EstadoCotizacionServices->createEstadoCotizacion($data), 201);
    
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);
        return response()->json($this->EstadoCotizacionServices->updateEstadoCotizacion($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->EstadoCotizacionServices->deleteEstadoCotizacion($id));
    }


    public function show($id)
    {
        return response()->json($this->EstadoCotizacionServices->getEstadoCotizacionById($id));
    }


}