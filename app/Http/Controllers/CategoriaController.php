<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::all();
        return $categorias;
    }

    public function store(Request $request)
    {
        $categoria = new Categoria();

        $categoria->ctg_nombre = $request->ctg_nombre;
        $categoria->ctg_descripcion = $request->ctg_descripcion;

        $categoria->save();

        // $response = new \stdClass();
        // $response->success = true;
        // $response->data= $categoria;

        // return response()->json($categoria, 200);

        return response()->json([
            'status' => 1,
            'msg' => 'registro de categoria exitoso',
            'data' => $categoria
        ], 200);
    }

    public function show($id)
    {
        $categoria = Categoria::find($id);
        return $categoria;
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->ctg_nombre = $request->ctg_nombre;
        $categoria->ctg_descripcion = $request->ctg_descripcion;

        $categoria->save();

        return $categoria;
    }

    public function destroy($id)
    {
        $categoria = Categoria::destroy($id);
        return $categoria;
    }
}
