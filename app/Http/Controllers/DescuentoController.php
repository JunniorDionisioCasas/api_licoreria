<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Descuento;
use App\Models\Detalle_user;

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
                            ->where("id_tipo_descuento", 3)
                            ->first();

        if($descuento){
            if($descuento->dsc_estado == 1){
                return response()->json([
                    "status" => 1,
                    "msg" => "se encontró un descuento para este codigo",
                    "data" => $descuento,
                ], 200);
            }else{
                return response()->json([
                    "status" => 0,
                    "msg" => "el descuento está inactivo"
                ], 400);
            }
        }else{
            return response()->json([
                "status" => 0,
                "msg" => "no se encontró descuentos con este código"
            ], 404);
        };
    }

    public function check_1st_buy($id)
    {
        $dtl_user = Detalle_user::where('id_user', $id)
                                ->where('dtl_usr_firstBuy', 1)
                                ->first();
        
        if($dtl_user){
            return false;
        }else{
            return true;
        }
    }
}
