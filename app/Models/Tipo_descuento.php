<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_descuento extends Model
{
    use HasFactory;
    protected $table = 'tipos_descuentos';
    protected $primaryKey = 'id_tipo_descuento';
    protected $fillable = [
        'tds_nombre',
        'tds_descripcion'
    ];
}
