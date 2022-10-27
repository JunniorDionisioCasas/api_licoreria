<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;
    protected $table = 'comprobantes';
    protected $primaryKey = 'id_comprobante';
    protected $fillable = [
        'cmp_serie',
        'cmp_tipo',
        'cmp_numero',
        'cmp_descripcion'
    ];
}
