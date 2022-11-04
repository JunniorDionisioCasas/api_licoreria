<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Detalle_pedido;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::join('categorias', 'productos.id_categoria', 'categorias.id_categoria')
                            ->join('marcas', 'productos.id_marca', 'marcas.id_marca')
                            ->select('productos.*', 'categorias.ctg_nombre', 'marcas.mrc_nombre')
                            ->get();
        return $productos;
    }
    
    public function store(Request $request)
    {
        $producto = new Producto();
        $producto->id_categoria = $request->id_categoria;
        $producto->id_marca = $request->id_marca;
        $producto->prd_nombre = $request->prd_nombre;
        $producto->prd_stock = $request->prd_stock;
        $producto->prd_precio = $request->prd_precio;
        $producto->prd_fecha_vencimiento = $request->prd_fecha_vencimiento;
        $producto->prd_descripcion = $request->prd_descripcion;
        
        // subiendo imagen
        $carpeta_foto = 'images/productos/';
        $foto = $request->prd_imagen;
        $extension_foto = $foto->getClientOriginalExtension();
        $foto->move(base_path().'/public/'.$carpeta_foto, strtolower(   str_replace( ' ', '', $request->prd_nombre . '.' . $extension_foto ) ) );
        $ruta_foto = 'http://127.0.0.1:8080/' . $carpeta_foto . strtolower( str_replace(' ', '', $request->prd_nombre) . '.' . $extension_foto ) ;
        $producto->prd_imagen_path = $ruta_foto;

        if ( $producto->save() ) {
            return response()->json([
                'status' => 1,
                'msg' => 'registro de producto exitoso',
                'data' => $producto,
            ]);
        }else{
            return response()->json(["message" => "No se pudo registrar"]);
        }
    }

    public function show($id)
    {
        $producto = Producto::find($id);
        return $producto;
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->id_categoria = $request->id_categoria;
        $producto->id_marca = $request->id_marca;
        $producto->prd_nombre = $request->prd_nombre;
        $producto->prd_stock = $request->prd_stock;
        $producto->prd_precio = $request->prd_precio;
        $producto->prd_fecha_vencimiento = $request->prd_fecha_vencimiento;
        $producto->prd_descripcion = $request->prd_descripcion;
        if(isset($request->prd_imagen))
        {
            // actualizando imagen
            $carpeta_foto = 'images/productos/';
            $foto = $request->prd_imagen;
            $extension_foto = $foto->getClientOriginalExtension();
            $foto->move(base_path().'/public/'.$carpeta_foto, str_replace( ' ', '-', $request->prd_nombre . '.' . $extension_foto ) );
            $ruta_foto = 'http://127.0.0.1:8080/' . $carpeta_foto . str_replace(' ', '-', $request->prd_nombre) . '.' . $extension_foto ;
            $producto->prd_imagen_path = $ruta_foto;
        }
        $producto->save();

        return $producto;
    }

    public function destroy($id)
    {
        $producto = Producto::destroy($id);
        return $producto;
    }

    public function info_filtro()
    {
        $categorias = Categoria::select('ctg_nombre', 'id_categoria')
                                ->orderBy('ctg_nombre')
                                ->get();
        foreach($categorias as $c){
            $c->cantidad_productos = Producto::where('id_categoria', $c->id_categoria)
                                            ->count();
        }

        $marcas = Marca::select('mrc_nombre', 'id_marca')
                    ->orderBy('mrc_nombre')
                    ->get();
        foreach($marcas as $m){
            $m->cantidad_productos = Producto::where('id_marca', $m->id_marca)
                                            ->count();
        }
        
        $info_filtro = new \stdClass;
        $info_filtro->categorias = $categorias;
        $info_filtro->marcas = $marcas;

        return $info_filtro;
    }

    public function search_by_filtro ($searchParams)
    {
        /* // convertir a objeto json
        $searchParams = '"'.$searchParams;
        $searchParams = rtrim($searchParams, '&');
        $searchParams = str_replace("=", '":', $searchParams);
        $searchParams = str_replace("&", ',"', $searchParams);
        $searchParams = '{' . $searchParams . '}'; */
        $array_c = [];
        $array_m = [];
        $min_price = $max_price = 0;
        $searchParams = explode("&", $searchParams, -1);
        foreach($searchParams as $p){
            $key = strstr($p, '=', true);
            switch ($key) {
                case 'c':
                    $value = substr(strstr($p, '='), 1);
                    array_push($array_c, $value);
                    break;
                case 'm':
                    $value = substr(strstr($p, '='), 1);
                    array_push($array_m, $value);
                    break;
                case 'min_price':
                    $min_price = substr(strstr($p, '='), 1);
                    break;
                case 'max_price':
                    $max_price = substr(strstr($p, '='), 1);
                    break;
            };
        };

        $productos = Producto::join('categorias', 'productos.id_categoria', 'categorias.id_categoria')
                            ->join('marcas', 'productos.id_marca', 'marcas.id_marca')
                            ->select('productos.*', 'categorias.ctg_nombre', 'marcas.mrc_nombre')
                            ->whereBetween('productos.prd_precio', [$min_price, $max_price]);
        
        foreach($array_c as $q){
            $productos = $productos->where('productos.id_categoria', $q);
        }

        foreach($array_m as $q){
            $productos = $productos->where('productos.id_marca', $q);
        }
        
        $productos = $productos->get();

        return $productos;

        /* return response()->json([
            "status" => 1,
            "msg" => "se ejecuto search params api",
            "data" => $productos,
        ], 200); */
    }

    public function count_vistas($id)
    {
        $producto = Producto::findOrFail($id);
        $contador = $producto->prd_contador_vistas;
        $contador++;
        $producto->prd_contador_vistas = $contador;
        $producto->save();

        return true;
    }

    public function home_data()
    {
        $marcas = Marca::whereNotNull('mrc_image_path')
                    ->get();

        $prd_mas_vistos = Producto::where('prd_stock', '>=', 1)
                                ->orderBy('prd_contador_vistas', 'desc')
                                ->take(5)
                                ->get();
        
        $id_prd_mas_vendidos = Detalle_pedido::selectRaw('id_producto, SUM(dtl_cantidad) as cant_vendida')
                                            ->groupBy('id_producto')
                                            ->orderBy('cant_vendida', 'desc')
                                            ->take(4)
                                            ->get();
        
        $data_prd_mas_vendidos = [];
        foreach($id_prd_mas_vendidos as $p){
            $data_prd = Producto::find($p->id_producto);
            array_push($data_prd_mas_vendidos, $data_prd);
        }
        
        $prd_nuevos = Producto::where('prd_stock', '>=', 1)
                            ->latest()
                            ->take(4)
                            ->get();

        $prd_en_oferta = Producto::join('descuentos', 'productos.id_descuento', '=', 'descuentos.id_descuento')
                            ->where('productos.prd_stock', '>=', 1)
                            ->whereNotNull('productos.id_descuento')
                            ->orderBy('descuentos.dsc_cantidad', 'desc')
                            ->take(4)
                            ->get();

        $home_data = new \stdClass;
        $home_data->marcas = $marcas;
        $home_data->prd_mas_vistos = $prd_mas_vistos;
        $home_data->data_prd_mas_vendidos = $data_prd_mas_vendidos;
        $home_data->prd_nuevos = $prd_nuevos;
        $home_data->prd_en_oferta = $prd_en_oferta;

        return $home_data;
    }
}
