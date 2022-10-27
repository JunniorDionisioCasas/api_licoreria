<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido_descuento extends Model
{
    use HasFactory;
    protected $table = 'pedidos_descuentos';
    protected $primaryKey = 'id_pedido_descuentos';
    protected $fillable = [
        'id_pedido',
        'id_descuento'
    ];
}
