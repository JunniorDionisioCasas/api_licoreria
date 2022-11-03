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

    public function search_by_code($searchParams)
    {
        $descuento = Descuento::where("dsc_codigo", $searchParams)
                            ->where("dsc_estado", 1)
                            ->first();

        if($descuento->dsc_estado == 1){
            return response()->json([
                "status" => 1,
                "msg" => "se encontró un descuento para este codigo",
                "data" => $descuento,
            ], 200);
        }elseif($descuento->dsc_estado == 0){
            return response()->json([
                "status" => 0,
                "msg" => "el descuento está inactivo"
            ], 400);
        }else{
            return response()->json([
                "status" => 0,
                "msg" => "no se encontró descuentos con este código"
            ], 400);
        };
    }
}
