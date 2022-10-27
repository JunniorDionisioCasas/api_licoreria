<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;
    protected $table = 'direcciones';
    protected $primaryKey = 'id_direccion';
    protected $fillable = [
        'id_distrito',
        'drc_direccion'
    ];
}
