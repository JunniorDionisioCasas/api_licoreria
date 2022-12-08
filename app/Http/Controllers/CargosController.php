<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cargo;

class CargosController extends Controller
{
    public function index()
    {
        $cargos = Cargo::all();
        return $cargos;
    }

    public function store(Request $request)
    {
        $cargo = new Cargo();

        $cargo->crg_nombre = $request->crg_nombre;
        $cargo->crg_acceso_admin = $request->crg_acceso_admin;
        $cargo->crg_descripcion = $request->crg_descripcion;

        $cargo->save();

        return $cargo;
    }

    public function show($id)
    {
        $cargo = Cargo::find($id);
        return $cargo;
    }

    public function update(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);

        $cargo->crg_nombre = $request->crg_nombre;
        $cargo->crg_acceso_admin = $request->crg_acceso_admin;
        $cargo->crg_descripcion = $request->crg_descripcion;

        $cargo->save();

        return $cargo;
    }

    public function destroy($id)
    {
        $cargo = Cargo::destroy($id);
        return $cargo;
    }
}
