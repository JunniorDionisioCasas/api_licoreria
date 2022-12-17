<?php

namespace App\Http\Controllers;
use App\Models\Distrito;
use App\Models\Provincia;

use Illuminate\Http\Request;

class DistritoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param  int  $idProvincia
     * @return \Illuminate\Http\Response
     */
    public function list_by_provincia($idProvincia)
    {
        $distritos_por_provincia = Distrito::join('provincias', 'distritos.id_provincia', 'provincias.id_provincia')
                                            ->where('distritos.id_provincia', $idProvincia)
                                            ->select('distritos.id_distrito', 'distritos.dst_nombre')
                                            ->get();
        return $distritos_por_provincia;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
