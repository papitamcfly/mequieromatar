<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Juego;

class juegosController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'jugador1' => 'required|integer',
            'jugador2' => 'nullable|integer',
            'puntuacion1' => 'nullable|integer',
            'puntuacion2' => 'nullable|integer',
            'estado' => 'string'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $juego = Juego::create([
            'jugador1' => $request->jugador1,
            'jugador2' => $request->jugador2,
            'puntuacion1' => $request->puntuacion1,
            'puntuacion2' => $request->puntuacion2,
            'estado' => 'en espera'
        ]);
        $juego->save();
        return response()->json([
            'message' => 'Partida creada, esperando 2do jugador',
            'juego' => $juego], 201);
    }

    public function indexEnEspera(){
        $juegos = Juego::where('estado', 'en espera')->get();
        return response()->json($juegos, 200);
    }

    public function indexFinalizados(){
        $juegos = Juego::where('estado', 'finalizado')->get();
        return response()->json($juegos, 200);
    }

    public function show($id){
        $juego = Juego::find($id);
        if ($juego){
            return response()->json($juego, 200);
        }
        return response()->json(['message' => 'Partida no encontrada'], 404);
    }

    public function update(Request $request, $id){
        $juego = Juego::find($id);
        if ($juego){
            $validator = Validator::make($request->all(),[
                'jugador1' => 'integer',
                'jugador2' => 'integer',
                'puntuacion1' => 'integer',
                'puntuacion2' => 'integer',
                'estado' => 'string'
            ]);
            if ($validator->fails()){
                return response()->json($validator->errors(), 400);
            }
            $juego->jugador1 = $request->jugador1;
            $juego->jugador2 = $request->jugador2;
            $juego->puntuacion1 = $request->puntuacion1;
            $juego->puntuacion2 = $request->puntuacion2;
            $juego->estado = $request->estado;
            $juego->save();
            return response()->json([
                'message' => 'Partida actualizada',
                'juego' => $juego], 200);
        }
        return response()->json(['message' => 'Partida no encontrada'], 404);
    }
    public function joinGame(Request $request, $id){
        $juego = Juego::find($id);
        if ($juego){
            $juego->jugador2 = $request->jugador2;
            $juego->estado = 'en proceso';
            $juego->save();
            return response()->json([
                'message' => 'Jugador 2 se ha unido al juego',
                'juego' => $juego], 200);
        }
        return response()->json(['message' => 'Partida no encontrada'], 404);
    }

    public function finishGame(Request $request, $id){
        $juego = Juego::find($id);
        if ($juego){
            $juego->puntuacion1 = $request->puntuacion1;
            $juego->puntuacion2 = $request->puntuacion2;
            $juego->estado = 'finalizado';
            $juego->save();
            return response()->json([
                'message' => 'Partida finalizada',
                'juego' => $juego], 200);
        }
        return response()->json(['message' => 'Partida no encontrada'], 404);
    }

    public function updateScore(Request $request, $id){
        $juego = Juego::find($id);
        if ($juego){
            $juego->puntuacion1 = $request->puntuacion1;
            $juego->puntuacion2 = $request->puntuacion2;
            $juego->save();
            return response()->json([
                'message' => 'Puntuaciones actualizadas',
                'juego' => $juego], 200);
        }
        return response()->json(['message' => 'Partida no encontrada'], 404);
    }
}