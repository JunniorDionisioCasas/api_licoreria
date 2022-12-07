<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_pedido;

class TipoPedidoController extends Controller
{
    public function index()
    {
        $tipo_pedidos = Tipo_pedido::all();

        return $tipo_pedidos;
    }

    public function store(Request $request)
    {
        $tipo_pedido = new Tipo_pedido();

        $tipo_pedido->tpe_nombre = $request->tpe_nombre;
        $tipo_pedido->tpe_descripcion = $request->tpe_descripcion;

        $tipo_pedido->save();

        return $tipo_pedido;
    }

    public function show($id)
    {
        $tipo_pedido = Tipo_pedido::find($id);
        return $tipo_pedido;
    }

    public function update(Request $request, $id)
    {
        $tipo_pedido = Tipo_pedido::findOrFail($id);

        $tipo_pedido->tpe_nombre = $request->tpe_nombre;
        $tipo_pedido->tpe_descripcion = $request->tpe_descripcion;

        $tipo_pedido->save();

        return $tipo_pedido;
    }

    public function destroy($id)
    {
        $tipo_pedido = Tipo_pedido::destroy($id);
        return $tipo_pedido;
    }
}
