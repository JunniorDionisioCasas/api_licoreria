<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::all();
        return $marcas;
    }

    public function store(Request $request)
    {
        $marca = new Marca();
        $marca->mrc_nombre = $request->mrc_nombre;
        $marca->mrc_descripcion = $request->mrc_descripcion;

        $marca->save();

        return response()->json([
            'status' => 1,
            'msg' => 'registro de marca exitosa',
            'data' => $marca
        ], 200);
    }

    public function show($id)
    {
        $marca = Marca::find($id);
        return $marca;
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);
        $marca->mrc_nombre = $request->mrc_nombre;
        $marca->mrc_descripcion = $request->mrc_descripcion;

        $marca->save();

        return $marca;
    }

    public function destroy($id)
    {
        $marca = Marca::destroy($id);
        return $marca;
    }
}
