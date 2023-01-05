<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMailable;
use App\Models\Pedido;
use App\Models\Tipo_pedido;
use App\Models\User;
use App\Models\Comprobante;
use App\Models\Direccion;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\Descuento;
use App\Models\Detalle_pedido;
use App\Models\Pedido_descuento;
use App\Models\Producto;
use App\Models\Detalle_user;
use Carbon\Carbon;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::join('comprobantes', 'pedidos.id_comprobante', 'comprobantes.id_comprobante')
                        ->join('tipos_pedidos', 'pedidos.id_tipo_pedido', 'tipos_pedidos.id_tipo_pedido')
                        ->join('users', 'pedidos.id_user', 'users.id')
                        ->select('pedidos.id_pedido', 'pedidos.pdd_total', 'pedidos.pdd_fecha_entrega', 'pedidos.pdd_estado',
                                'comprobantes.cmp_serie', 'comprobantes.cmp_tipo', 'comprobantes.cmp_numero', 'comprobantes.cmp_pdf_path',
                                'tipos_pedidos.tpe_nombre', 'users.name', 'users.usr_apellidos')
                        ->get();

        return $pedidos;
    }

    public function store(Request $request)
    {
        $comp_correlativo = 1;
        $comp_serie = ( $request->tipo_comp === "Boleto" ) ? 'B001' : 'F001'; //cambia a B002 o F002 por sucursal

        $last_cmp = Comprobante::latest()
                                ->first();

        if ( $last_cmp ) {
            $numeracion_comp = $last_cmp->cmp_numero + 1;
            $comp_correlativo = $numeracion_comp;
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
        $prdsArrayForInvoicing = [];
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

            $prd = new \stdClass();
            $prd->codigo = $updt_stck_prd->id_producto;
            $prd->nombre = $updt_stck_prd->prd_nombre;
            $prd->cantidad = $p["cntd"];
            $prd->precio = $p["precio"];

            array_push($prdsArrayForInvoicing, $prd);
            array_push($detalles_pedido, $dtl_pdd);
        }

        $pedido_descuentos = [];
        $totalDescuento = 0;
        if($request->descuentos){
            foreach($request->descuentos as $d){
                $pdd_dsc = new Pedido_descuento();
                $pdd_dsc->id_pedido = $pedido->id_pedido;
                $pdd_dsc->id_descuento = $d["id"];
                $pdd_dsc->pds_cantidad_desc = $d["cantidad"];
                $pdd_dsc->save();

                $totalDescuento += $d["cantidad"];
                array_push($pedido_descuentos, $pdd_dsc);

                if($d["id"] == 2) { //2=primera compra del usuario
                    $dtl_user = Detalle_user::where('id_user', $request->id_user)
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

        $fechaFormatoBoleto = Carbon::now('America/Lima')->toAtomString();
        $user = User::findOrFail($request->id_user);
        $udDireccion = $request->direccion;
        $udDepartamento = "JUNIN"; //default
        $udProvincia = "HUANCAYO"; //default
        $udDistrito = "SAN JERÓNIMO DE TUNÁN"; //default
        $udUbigueo = "120130"; //default
        if($user->id_direccion) {
            $drc = Direccion::find($user->id_direccion);
            if($drc){
                $udDireccion = $drc->drc_direccion;
                $drcDst = Distrito::find($drc->id_distrito);
                $udDistrito = $drcDst->dst_nombre;
                $udUbigueo = $drcDst->dst_ubigueo;
                $drcPrv = Provincia::find($drcDst->id_provincia);
                $udProvincia = $drcPrv->prv_nombre;
            }
        }
        $dataCompra = (object) [
            "total" => $request->total,
            "cSerie" => $comp_serie,
            "cCorrelativo" => $comp_correlativo,
            "userMail" => $request->userMail,
            "uNumDoc" => $user->usr_num_documento,
            "uNombApll" => $user->name . ' ' . $user->usr_apellidos,
            "uDireccion" => $udDireccion,
            "uDepartamento" => $udDepartamento,
            "uProvincia" => $udProvincia,
            "uDistrito" => $udDistrito,
            "uUbigueo" => $udUbigueo,
            "totalDescuento" => $totalDescuento,
            "productos" => $prdsArrayForInvoicing,
            "fechaFormatoBoleto" => $fechaFormatoBoleto,
        ];

        $envioComprobante = $this->emisionComprobante($dataCompra);

        $comprobante->cmp_pdf_path = $envioComprobante;
        $comprobante->save();
        // $comprobante->forceFill(['cmp_pdf_path' => $envioComprobante])->save();

        $data = new \stdClass();
        $data->pedido = $pedido;
        $data->comprobante = $comprobante;
        $data->detalles = $detalles_pedido;
        $data->descuentos = $pedido_descuentos;
        $data->envioComprobante = $envioComprobante;

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

    public function emisionComprobante($dataCompra){
        //documentación apisPeru: https://facturacion.apisperu.com/doc#operation/invoice_pdf
        $tokenEmpresa = config('services.api_keys.apf_enterprise_key');
        $totalDescuento = $dataCompra->totalDescuento;
        $mtoTotalAPagar = $dataCompra->total;
        $mtoTotalDsct = $mtoTotalAPagar*$totalDescuento/100;
        $mtoTotalDsct = round($mtoTotalDsct, 1);
        $mtoTotalDsct = number_format((float)$mtoTotalDsct, 2, '.', '');
        $mtoTotal = $mtoTotalAPagar + $mtoTotalDsct;
        $mtoTotalSinIGV = 0.82*$mtoTotal;
        $mtoIgvTotal = 0.18*$mtoTotal;

        $mtoTotalADeletrear = intval($mtoTotalAPagar);
        $formatterES = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        $montoTotalDeletreado = strtoupper($formatterES->format($mtoTotalADeletrear));
        $montoCentimosString = number_format($mtoTotalAPagar, 2);
        $montoCentimos = substr($montoCentimosString, -2);

        $url = 'https://facturacion.apisperu.com/api/v1/invoice/pdf';
        $details = [];
        foreach($dataCompra->productos as $prd) {
            $monto = $prd->cantidad * $prd->precio;
            $montoSinIGV = 0.82*$monto;
            $montoIGV = 0.18*$monto;
            $montoUnitSinIGV = 0.82*$prd->precio;

            $dtl = new \stdClass();
            $dtl->codProducto = $prd->codigo;
            $dtl->unidad = "NIU";
            $dtl->descripcion = $prd->nombre;
            $dtl->cantidad = $prd->cantidad;
            $dtl->mtoValorUnitario = $montoUnitSinIGV;
            $dtl->mtoValorVenta = $montoSinIGV;
            $dtl->mtoBaseIgv = $montoSinIGV;
            $dtl->porcentajeIgv = 18;
            $dtl->igv = $montoIGV;
            $dtl->tipAfeIgv = 10;
            $dtl->totalImpuestos = $montoIGV;
            $dtl->mtoPrecioUnitario = $prd->precio;
            array_push($details, $dtl);
        }
        $response = Http::withToken($tokenEmpresa)->post($url, [
            "ublVersion" => "2.1",
            "tipoOperacion" => "0101",
            "tipoDoc" => "03",
            "serie" => $dataCompra->cSerie,
            "correlativo" => $dataCompra->cCorrelativo,
            "fechaEmision" => $dataCompra->fechaFormatoBoleto,
            "formaPago" => [
                "moneda" => "PEN",
                "tipo" => "Contado"
            ],
            "tipoMoneda" => "PEN",
            "client" => [
                "tipoDoc" => "1",
                "numDoc" => $dataCompra->uNumDoc,
                "rznSocial" => $dataCompra->uNombApll,
                "address" => [
                "direccion" => $dataCompra->uDireccion,
                "provincia" => $dataCompra->uProvincia,
                "departamento" => $dataCompra->uDepartamento,
                "distrito" => $dataCompra->uDistrito,
                "ubigueo" => $dataCompra->uUbigueo
                ]
            ],
            "company" => [ //default
                "ruc" => 10409698871,
                "razonSocial" => "Bastidas Flores Ruth Marisol",
                "nombreComercial" => "Bazar Licorería San Sebastián",
                "address" => [
                "direccion" => "Jr. lima N 136 San Jerónimo de Tunan – Huancayo",
                "provincia" => "HUANCAYO",
                "departamento" => "JUNIN",
                "distrito" => "SAN JERÓNIMO DE TUNÁN",
                "ubigueo" => "120130"
                ]
            ],
            "mtoOperGravadas" => $mtoTotalSinIGV,
            "mtoIGV" => $mtoIgvTotal,
            "valorVenta" => $mtoTotalSinIGV,
            "totalImpuestos" => $mtoIgvTotal,
            "subTotal" => $dataCompra->total,
            "mtoImpVenta" => $dataCompra->total,
            "details" => $details,
            "legends" => [
                [
                "code" => "1000",
                "value" => "SON ".$montoTotalDeletreado." CON ".$montoCentimos."/100 SOLES"
                ]
            ],
            "descuentos" => [
                [
                    "codTipo" => "00",
                    "factor" => "Monto total descuento",
                    "monto" => $mtoTotalDsct,
                    "montoBase" => $mtoTotalDsct
                ]
            ]
        ]);

        $pdf = $response->body();

        $fecha_emision = Carbon::now('-05:00');
        $cmp_nombre_archivo = $dataCompra->cSerie.'_'.$dataCompra->cCorrelativo.'_'.$fecha_emision.'.pdf';
        $cmp_nombre_archivo = str_replace( ':', '-', $cmp_nombre_archivo);
        $cmp_nombre_archivo = str_replace( ' ', '-', $cmp_nombre_archivo);
        
        Storage::disk('invoices')->put($cmp_nombre_archivo, $pdf);
        $pathNewFile = public_path().'/invoices/'.$cmp_nombre_archivo;

        // $rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
        // $folder_destination = '/invoices/';
        // $pdf->move($rootDir.$folder_destination, $cmp_nombre_archivo);
        // $file_path = env("APP_URL") . $folder_destination . $cmp_nombre_archivo;

        Mail::to($dataCompra->userMail)->send(new InvoiceMailable($pathNewFile));

        return env("APP_URL").'invoices/'.$cmp_nombre_archivo;
    }
}
