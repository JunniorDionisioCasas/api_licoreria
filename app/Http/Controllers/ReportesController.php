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
use Carbon\Carbon;

class ReportesController extends Controller
{
    public function admin_home_info()
    {
        $weekDays = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $today = Carbon::now('-05:00');
        $todayDay = $today->isoFormat('d');
        $barChartLabels = [];
        for($i=0; $i<7; $i++){
            $weekIndex = $todayDay + $i + 1;
            if($weekIndex>6) $weekIndex = $weekIndex - 7;
            $barChartLabels[$i] = $weekDays[$weekIndex];
        }

        $barChartData = Pedido::selectRaw('SUBSTRING(pdd_fecha_entrega, 1, 10) AS pdd_fecha,
                                            COALESCE(SUM(pdd_total), 0) AS total_dia,
                                            COUNT(id_pedido) as num_ventas_dia')
                            ->whereBetween('pdd_fecha_entrega', [Carbon::now('-05:00')->subDays(6)->format('Y-m-d 00:00:00'), Carbon::now('-05:00')->format('Y-m-d 23:59:59')])
                            ->groupByRaw('SUBSTRING(pdd_fecha_entrega, 0, 10), pdd_fecha')
                            ->take(7)
                            ->get();
        
        $pieData = Detalle_pedido::join('productos', 'detalles_pedidos.id_producto', 'productos.id_producto')
                                ->selectRaw('productos.prd_nombre, SUM(detalles_pedidos.dtl_cantidad) AS cant_vendidos')
                                ->groupByRaw('productos.prd_nombre')
                                ->get();

        $home_data = new \stdClass;
        $home_data->barChartLabels = $barChartLabels;
        $home_data->barChartData = $barChartData;
        $home_data->pieData = $pieData;
        
        return $home_data;
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
