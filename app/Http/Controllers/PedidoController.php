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
use App\Models\Producto;
use App\Models\Detalle_user;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::join('comprobantes', 'pedidos.id_comprobante', 'comprobantes.id_comprobante')
                        ->join('tipos_pedidos', 'pedidos.id_tipo_pedido', 'tipos_pedidos.id_tipo_pedido')
                        ->join('users', 'pedidos.id_user', 'users.id')
                        ->select('pedidos.id_pedido', 'pedidos.pdd_total', 'pedidos.pdd_fecha_entrega', 'pedidos.pdd_estado',
                                'comprobantes.cmp_serie', 'comprobantes.cmp_tipo', 'comprobantes.cmp_numero',
                                'tipos_pedidos.tpe_nombre', 'users.name', 'users.usr_apellidos')
                        ->get();

        return $pedidos;
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
        $pedido->pdd_estado = $request->pdd_estado;
        $pedido->save();

        $detalles_pedido = [];
        foreach($request->productos as $p){
            $dtl_pdd = new Detalle_pedido();
            $dtl_pdd->id_pedido = $pedido->id_pedido;
            $dtl_pdd->id_producto = $p["id"];
            $dtl_pdd->dtl_precio = $p["precio"];
            $dtl_pdd->dtl_cantidad = $p["cntd"];
            $dtl_pdd->save();

            $updt_stck_prd = Producto::findOrFail($p["id"]);
            $updt_stck_prd->prd_stock = ($updt_stck_prd->prd_stock)-$p["cntd"];
            $updt_stck_prd->save();

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

                if($d["id"] == 2) { //2=primera compra del usuario
                    $dtl_user = Detalle_user::where('id_user', $id)
                                ->where('dtl_usr_firstBuy', 1)
                                ->first();
        
                    if($dtl_user){
                        $dtl_user->dtl_usr_firstBuy = 0;
                    }else{
                        $dtl_user = new Detalle_user;
                        $dtl_user->id_user = $request->id_user;
                        $dtl_user->dtl_usr_firstBuy = 0;
                    }
                    $dtl_user->save();
                }
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
        $pedido = Pedido::join('comprobantes', 'pedidos.id_comprobante', 'comprobantes.id_comprobante')
                        ->join('tipos_pedidos', 'pedidos.id_tipo_pedido', 'tipos_pedidos.id_tipo_pedido')
                        ->join('detalles_pedidos', 'pedidos.id_pedido', 'detalles_pedidos.id_pedido')
                        ->select('pedidos.id_pedido', 'pedidos.pdd_total', 'pedidos.created_at', 'pedidos.pdd_estado',
                                'comprobantes.id_comprobante', 'comprobantes.cmp_serie', 'comprobantes.cmp_tipo', 'comprobantes.cmp_numero', 
                                'tipos_pedidos.id_tipo_pedido', 'tipos_pedidos.nombre',
                                'detalles_pedidos.*')
                        ->find($id);

        return $pedido;
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function pedido_pagado($id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->pdd_estado = 3; //pasa a 3(recibido) porque no se contempla el administrar proceso de delivery
        
        $pedido->save();

        return $pedido;
    }
}
