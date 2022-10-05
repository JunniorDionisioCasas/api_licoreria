<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    protected $fillable = [
        'id_categoria',
        'id_marca',
        'prd_nombre',
        'prd_stock',
        'prd_precio',
        'prd_fecha_vencimiento',
        'prd_descripcion',
        'prd_imagen_path'
    ];
}
