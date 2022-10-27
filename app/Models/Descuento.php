<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;
    protected $table = 'descuentos';
    protected $primaryKey = 'id_descuento';
    protected $fillable = [
        'dsc_nombre',
        'dsc_tipo',
        'dsc_cantidad',
        'dsc_estado'
    ];
}
