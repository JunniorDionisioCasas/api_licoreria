<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_pedido extends Model
{
    use HasFactory;
    protected $table = 'detalles_pedidos';
    protected $primaryKey = 'id_detalle_pedido';
    protected $fillable = [
        'id_pedido',
        'id_producto',
        'dtl_precio',
        'dtl_cantidad'
    ];
}
