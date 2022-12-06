<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::all();

        return $proveedores;
    }

    public function store(Request $request)
    {
        $proveedor = new Proveedor();
        $proveedor->prv_nombre = $request->prv_nombre;
        $proveedor->prv_anotaciones = $request->prv_anotaciones;
        $proveedor->prv_estado = $request->prv_estado;

        $proveedor->save();

        return $proveedor;
    }

    public function show($id)
    {
        $proveedor = Proveedor::find($id);
        return $proveedor;
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->prv_nombre = $request->prv_nombre;
        $proveedor->prv_anotaciones = $request->prv_anotaciones;
        $proveedor->prv_estado = $request->prv_estado;

        $proveedor->save();

        return $proveedor;
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::destroy($id);
        return $proveedor;
    }
}
