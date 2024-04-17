<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juego extends Model
{
    use HasFactory;
    protected $table = 'juegos';
    protected $fillable = ['jugador1', 'jugador2', 'puntuacion1', 'puntuacion2', 'estado'];
    public function jugador1()
    {
        return $this->belongsTo(User::class, 'jugador1');
    }
    public function jugador2()
    {
        return $this->belongsTo(User::class, 'jugador2');
    }
}
