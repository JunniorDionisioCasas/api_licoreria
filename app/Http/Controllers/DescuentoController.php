<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Descuento;
use App\Models\Detalle_user;
use App\Models\Producto;

class DescuentoController extends Controller
{
    public function index()
    {
        $descuentos = Descuento::where('dsc_estado', 1)
                                ->get();

        return $descuentos;
    }

    public function crud_index()
    {
        $descuentos = Descuento::join('tipos_descuentos', 'descuentos.id_tipo_descuento', 'tipos_descuentos.id_tipo_descuento')
                                ->leftJoin('productos', 'descuentos.id_descuento', 'productos.id_descuento')
                                ->select('descuentos.*', 'tipos_descuentos.tds_nombre', 'productos.prd_nombre')
                                ->get();

        return $descuentos;
    }

    public function store(Request $request)
    {
        $descuento = new Descuento();

        $descuento->id_tipo_descuento = $request->id_tipo_descuento;
        $descuento->dsc_nombre = $request->dsc_nombre;
        $descuento->dsc_cantidad = $request->dsc_cantidad;
        $descuento->dsc_codigo = $request->dsc_codigo;
        $descuento->dsc_estado = $request->dsc_estado;

        $descuento->save();

        if ($descuento->id_tipo_descuento == 1) {
            $replaceIfSet = Producto::where('id_descuento', $descuento->id_descuento)
                                    ->first();
            if ($replaceIfSet) {
                $replaceIfSet->id_descuento = null;
                $replaceIfSet->save();
            }
            $producto = Producto::findOrFail($request->id_producto);
            $producto->id_descuento = $descuento->id_descuento;
            $producto->save();
        }

        return $descuento;
    }

    public function show($id)
    {
        $descuento = Descuento::find($id);

        return $descuento;
    }

    public function update(Request $request, $id)
    {
        $descuento = Descuento::findOrFail($id);

        $descuento->id_tipo_descuento = $request->id_tipo_descuento;
        $descuento->dsc_nombre = $request->dsc_nombre;
        $descuento->dsc_cantidad = $request->dsc_cantidad;
        $descuento->dsc_codigo = $request->dsc_codigo;
        $descuento->dsc_estado = $request->dsc_estado;

        $descuento->save();

        if ($descuento->id_tipo_descuento == 1) {
            $replaceIfSet = Producto::where('id_descuento', $descuento->id_descuento)
                                    ->first();
            
            if ($replaceIfSet) {
                $replaceIfSet->id_descuento = null;
                $replaceIfSet->save();
            }
            $producto = Producto::findOrFail($request->id_producto);
            $producto->id_descuento = $descuento->id_descuento;
            $producto->save();
        }

        return $descuento;
    }

    public function destroy($id)
    {
        $descuento = Descuento::destroy($id);

        return $descuento;
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
