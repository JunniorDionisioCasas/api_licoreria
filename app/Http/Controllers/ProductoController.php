<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();
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
        $producto->prd_imagen_path = $request->prd_imagen_path;
        
        $producto->save();

        return response()->json([
            'status' => 1,
            'msg' => 'registro de producto exitoso',
            'data' => $producto,
        ]);
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
        $producto->prd_imagen_path = $request->prd_imagen_path;
        
        $producto->save();

        return $producto;
    }

    public function destroy($id)
    {
        $producto = Producto::destroy($id);
        return $producto;
    }
}
