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

class ReportesController extends Controller
{
    public function index()
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function reporte_ventas($dateFrom, $dateUntil, $idProducto, $idTipoPedido, $idCliente)
    {
        $pedidos = Pedido::join('comprobantes', 'pedidos.id_comprobante', 'comprobantes.id_comprobante')
                        ->join('tipos_pedidos', 'pedidos.id_tipo_pedido', 'tipos_pedidos.id_tipo_pedido')
                        ->join('users', 'pedidos.id_user', 'users.id')
                        /* ->join('detalles_pedidos', 'pedidos.id_pedido', 'detalles_pedidos.id_pedido')
                        ->join('productos', 'detalles_pedidos.id_producto', 'productos.id_producto')*/
                        ->select('pedidos.id_pedido', 'pedidos.pdd_total', 'pedidos.pdd_fecha_entrega', 'pedidos.pdd_estado',
                                'comprobantes.cmp_serie', 'comprobantes.cmp_tipo', 'comprobantes.cmp_numero',
                                'tipos_pedidos.id_tipo_pedido', 'tipos_pedidos.tpe_nombre', 'users.name', 'users.usr_apellidos',
                                /* 'productos.id_producto', 'productos.prd_nombre' */);
        
        $pedidos = $pedidos->whereBetween('pedidos.pdd_fecha_entrega', [$dateFrom, $dateUntil]);
        if ( $idProducto != 0 ) {
            $pedidos = $pedidos->where('productos.id_producto', $idProducto);
        }
        if ( $idTipoPedido != 0 ) {
            $pedidos = $pedidos->where('tipos_pedidos.id_tipo_pedido', $idTipoPedido);
        }
        if ( $idCliente != 0 ) {
            $pedidos = $pedidos->where('users.id', $idCliente);
        }
        $pedidos = $pedidos->get();

        return $pedidos;
    }
}
