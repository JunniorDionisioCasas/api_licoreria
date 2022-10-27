<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Tipo_pedido;
use App\Models\User;
use App\Models\Comprobante;
use App\Models\Direccion;
use App\Models\Descuento;
use App\Models\Detalle_pedido;
use App\Models\Pedido_descuento;

class PedidoController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $comp_serie = ( $request->tipo_comp === "Boleto" ) ? 'B001' : 'F001'; //cambia a B002 o F002 por sucursal

        $last_cmp = Comprobante::latest()
                                ->first();

        if ( $last_cmp ) {
            $numeracion_comp = $last_cmp->cmp_numero + 1;
        } else {
            $numeracion_comp = 1;
        }

        $comprobante = new Comprobante();
        $comprobante->cmp_serie = $comp_serie;
        $comprobante->cmp_tipo = $request->tipo_comp;
        $comprobante->cmp_numero = $numeracion_comp;
        $comprobante->cmp_descripcion = $request->cmp_descr;
        $comprobante->save();

        $pedido = new Pedido();
        $pedido->id_tipo_pedido = $request->id_tipo_pedido;
        $pedido->id_user = $request->id_user;
        $pedido->id_empleado = $request->id_empleado;
        $pedido->id_comprobante = $comprobante->id_comprobante;
        $pedido->pdd_direccion = $request->direccion;
        $pedido->pdd_total = $request->total;
        $pedido->pdd_fecha_entrega = $request->pdd_fecha_entrega;
        // $pedido->pdd_fecha_recepcion = $request->pdd_fecha_recepcion;
        $pedido->save();

        $detalles_pedido = [];
        foreach($request->productos as $p){
            $dtl_pdd = new Detalle_pedido();
            $dtl_pdd->id_pedido = $pedido->id_pedido;
            $dtl_pdd->id_producto = $p["id"];
            $dtl_pdd->dtl_precio = $p["precio"];
            $dtl_pdd->dtl_cantidad = $p["cntd"];
            $dtl_pdd->save();

            array_push($detalles_pedido, $dtl_pdd);
        }

        $pedido_descuentos = [];
        if($request->descuentos){
            foreach($request->descuentos as $d){
                $pdd_dsc = new Pedido_descuento();
                $pdd_dsc->id_pedido = $pedido->id_pedido;
                $pdd_dsc->id_descuento = $d["id"];
                $pdd_dsc->pds_cantidad_desc = $d["cantidad"];
                $pdd_dsc->save();

                array_push($pedido_descuentos, $pdd_dsc);
            }
        }

        $data = new \stdClass();
        $data->pedido = $pedido;
        $data->comprobante = $comprobante;
        $data->detalles = $detalles_pedido;
        $data->descuentos = $pedido_descuentos;

        return response()->json([
            "status" => 1,
            "msg" => "registro de pedido exitoso",
            "data" => $data,
        ], 200);
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
