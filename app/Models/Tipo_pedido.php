<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_pedido extends Model
{
    use HasFactory;
    protected $table = 'tipos_pedidos';
    protected $primaryKey = 'id_tipo_pedido';
    protected $fillable = [
        'tpe_nombre',
        'tpe_descripcion'
    ];
}
