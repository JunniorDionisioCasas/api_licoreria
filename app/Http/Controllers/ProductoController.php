<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;

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
}
