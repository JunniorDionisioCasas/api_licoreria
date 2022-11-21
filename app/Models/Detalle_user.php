<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_user extends Model
{
    use HasFactory;
    protected $table = 'detalles_users';
    protected $primaryKey = 'id_dtl_user';
    protected $fillable = [
        'id_user',
        'dtl_usr_firstBuy'
    ];
}
