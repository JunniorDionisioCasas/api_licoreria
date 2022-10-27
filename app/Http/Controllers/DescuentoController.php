<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Descuento;

class DescuentoController extends Controller
{
    public function index()
    {
        $descuentos = Descuento::where('dsc_estado', 1)
                                ->get();

        return $descuentos;
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $descuento = Descuento::find($id);

        return $descuento;
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
