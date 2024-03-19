<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;

class SalaController extends Controller
{
    public function index()
    {
        DB::connection()->enableQueryLog();

        $salas = Sala::all();
        $user = auth()->user();
        $userId = $user ? $user->id : null;
    
        $query = DB::getQueryLog();
        $query = end($query)['query'];
    

        $log = new RequestLog;
        $log->user = $userId;
        $log->metodo = 'GET';
        $log->url = request()->fullUrl(); 
        $log->ip = request()->ip();
        $log->agent = request()->userAgent();
        $log->timestamps = now();
        $log->query = $query; 
        $log->save();
        return response()->json($salas, 200);

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cine_id' => 'required|exists:cines,id',
            'numero_sala' => 'required|int',
            'capacidad' => 'required|int|min:1',
        ], [
            'cine_id.required' => 'El campo cine_id es obligatorio.',
            'cine_id.exists' => 'El cine_id proporcionado no existe.',
            'numero_sala.required' => 'El campo número de sala es obligatorio.',
            'numero_sala.int' => 'El campo número de sala debe ser un número entero.',
            'capacidad.required' => 'El campo capacidad es obligatorio.',
            'capacidad.int' => 'El campo capacidad debe ser un número entero.',
            'capacidad.min' => 'El campo capacidad debe ser al menos 1.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Sala::create($request->all());
                // Obtener información del usuario autenticado
                $user = auth()->user();
                $userId = $user ? $user->id : null;
        
                // Obtener información de la petición
                $log = new RequestLog;
                $log->user = $userId;
                $log->metodo =$request->method();
                $log->url =$request->fullUrl();
                $log->ip = $request->ip();
                $log->agent = $request->userAgent();
                $log->timestamps = now();
                $log->datos = $request->all();
                $log->save();

        return response()->json('Sala creada correctamente', 201);
    }

    public function show($id)
    {
        DB::connection()->enableQueryLog();

        $sala = Sala::find($id);

        if (!$sala) {
            return response()->json(['message' => 'Sala no encontrada'], 404);
        }
        $user = auth()->user();
        $userId = $user ? $user->id : null;
    
        $query = DB::getQueryLog();
        $query = end($query)['query'];
    

        $log = new RequestLog;
        $log->user = $userId;
        $log->metodo = 'GET';
        $log->url = request()->fullUrl(); 
        $log->ip = request()->ip();
        $log->agent = request()->userAgent();
        $log->timestamps = now();
        $log->query = $query; 
        $log->save();
        return response()->json($sala, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cine_id' => 'required|exists:cines,id',
            'numero_sala' => 'required|int',
            'capacidad' => 'required|int|min:1',
        ], [
            'cine_id.required' => 'El campo cine_id es obligatorio.',
            'cine_id.exists' => 'El cine_id proporcionado no existe.',
            'numero_sala.required' => 'El campo número de sala es obligatorio.',
            'numero_sala.int' => 'El campo número de sala debe ser un número entero.',
            'capacidad.required' => 'El campo capacidad es obligatorio.',
            'capacidad.int' => 'El campo capacidad debe ser un número entero.',
            'capacidad.min' => 'El campo capacidad debe ser al menos 1.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sala = Sala::find($id);

        if (!$sala) {
            return response()->json(['message' => 'Sala no encontrada'], 404);
        }

        $sala->update($request->all());
        $user = auth()->user();
        $userId = $user ? $user->id : null;
    
        $query = DB::getQueryLog();
        $query = end($query)['query'];
    

        $log = new RequestLog;
        $log->user = $userId;
        $log->metodo = 'GET';
        $log->url = request()->fullUrl(); 
        $log->ip = request()->ip();
        $log->agent = request()->userAgent();
        $log->timestamps = now();
        $log->query = $query; 
        $log->save();
        return response()->json($sala, 200);
    }

    public function destroy($id)
    {
        DB::connection()->enableQueryLog();

        $sala = Sala::find($id);

        if (!$sala) {
            return response()->json(['message' => 'Sala no encontrada'], 404);
        }

        $sala->delete();
        $user = auth()->user();
        $userId = $user ? $user->id : null;
    
        $query = DB::getQueryLog();
        $query = end($query)['query'];
    

        $log = new RequestLog;
        $log->user = $userId;
        $log->metodo = 'DELETE';
        $log->url = request()->fullUrl(); 
        $log->ip = request()->ip();
        $log->agent = request()->userAgent();
        $log->timestamps = now();
        $log->query = $query; 
        $log->save();
        return response()->json(['message' => 'Sala eliminada correctamente'], 200);
    }
}
