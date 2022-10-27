<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    use HasFactory;
    protected $table = 'distritos';
    protected $primaryKey = 'id_distrito';
    protected $fillable = [
        'id_provincia',
        'dst_nombre'
    ];
}
