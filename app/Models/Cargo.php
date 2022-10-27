<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;
    protected $table = 'cargos';
    protected $primaryKey = 'id_cargo';
    protected $fillable = [
        'crg_nombre',
        'crg_acceso_admin',
        'crg_descripcion'
    ];
}
