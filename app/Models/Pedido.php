<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    protected $fillable = [
        'id_tipo_pedido',
        'id_user',
        'id_empleado',
        'id_comprobante',
        'id_direccion',
        'pdd_total',
        'pdd_fecha_entrega',
        'pdd_fecha_recepcion',
        'pdd_descripcion',
    ];
}
