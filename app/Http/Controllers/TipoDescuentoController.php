<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_descuento;

class TipoDescuentoController extends Controller
{
    public function index()
    {
        $tipo_descuentos = Tipo_descuento::all();

        return $tipo_descuentos;
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
