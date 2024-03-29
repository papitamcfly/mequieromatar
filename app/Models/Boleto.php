<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Boleto extends Model
{
    use HasFactory;
    public $timestamps= false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_funcion',
        'id_user',
        'fila',
        'asiento',
        'precio',
    ];

    /**
     * Get the funcion that owns the boleto.
     */
    public function funcion()
    {
        return $this->belongsTo(Funcion::class, 'id_funcion');
    }

    /**
     * Get the user that owns the boleto.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
