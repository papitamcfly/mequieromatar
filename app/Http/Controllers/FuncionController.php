<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Funcion;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;

class FuncionController extends Controller
{
    public function index()
    {
        DB::connection()->enableQueryLog();

        $funciones = Funcion::all();
        return response()->json($funciones, 200);
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
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sala_id' => 'required|exists:salas,id',
            'pelicula_id' => 'required|exists:peliculas,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
        ], [
            'sala_id.required' => 'El campo Sala es obligatorio.',
            'sala_id.exists' => 'La Sala seleccionada no existe.',
            'pelicula_id.required' => 'El campo Película es obligatorio.',
            'pelicula_id.exists' => 'La Película seleccionada no existe.',
            'fecha.required' => 'El campo Fecha es obligatorio.',
            'fecha.date' => 'El valor ingresado para la Fecha no es válido.',
            'hora_inicio.required' => 'El campo Hora de inicio es obligatorio.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Funcion::create($request->all());
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        // Obtener información de la petición
        $log = new RequestLog();
        $log->user = $userId;
        $log->metodo =$request->method();
        $log->url =$request->fullUrl();
        $log->ip = $request->ip();
        $log->agent = $request->userAgent();
        $log->timestamps = now();
        $log->datos = $request->all();
        $log->save();
        return response()->json('Funcion creada correctamente', 201);
    }

    public function show($id)
    {
        DB::connection()->enableQueryLog();

        $funcion = Funcion::find($id);

        if (!$funcion) {
            return response()->json(['message'=>__('Funcion no encontrada')],404);
        }
        $user = auth()->user();
        $userId = $user ? $user->id : null;
    
        // Obtener el query ejecutado
        $query = DB::getQueryLog();
        $query = end($query)['query'];
    
        // Crear un registro en el registro de solicitudes
        $log = new RequestLog;
        $log->user = $userId;
        $log->metodo = 'GET'; // Método GET para la operación de visualización
        $log->url = request()->fullUrl(); // URL actual
        $log->ip = request()->ip(); // IP del cliente
        $log->agent = request()->userAgent(); // Agente del usuario
        $log->timestamps = now(); // Marca de tiempo actual
        $log->query = $query; // Query ejecutado
        $log->save();
        return response()->json($funcion, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sala_id' => 'required|exists:salas,id',
            'pelicula_id' => 'required|exists:peliculas,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
        ], [
            'sala_id.required' => 'El campo sala_id es obligatorio.',
            'sala_id.exists' => 'El sala_id proporcionado no existe.',
            'pelicula_id.required' => 'El campo pelicula_id es obligatorio.',
            'pelicula_id.exists' => 'El pelicula_id proporcionado no existe.',
            'fecha.required' => 'El campo fecha es obligatorio.',
            'fecha.date' => 'El campo fecha debe ser una fecha válida.',
            'hora_inicio.required' => 'El campo hora_inicio es obligatorio.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $funcion = Funcion::find($id);

        if (!$funcion) {
            return response()->json(['message'=>'Funcion no encontrada'],404);
        }

        $funcion->update($request->all());
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        // Obtener información de la petición
        $log = new RequestLog();
        $log->user = $userId;
        $log->metodo =$request->method();
        $log->url =$request->fullUrl();
        $log->ip = $request->ip();
        $log->agent = $request->userAgent();
        $log->timestamps = now();
        $log->datos = $request->all();
        $log->save();
        return response()->json('Funcion actualizada correctamente', 201);
    }

    public function destroy($id)
    {
        DB::connection()->enableQueryLog();

        $funcion = Funcion::find($id);

        if (!$funcion) {
            return response()->json(['message'=>'Funcion no encontrada'],404);
        }

        $funcion->delete();
        $user = auth()->user();
        $userId = $user ? $user->id : null;
    
        // Obtener el query ejecutado
        $query = DB::getQueryLog();
        $query = end($query)['query'];
    
        // Crear un registro en el registro de solicitudes
        $log = new RequestLog;
        $log->user = $userId;
        $log->metodo = 'DELETE'; // Método GET para la operación de visualización
        $log->url = request()->fullUrl(); // URL actual
        $log->ip = request()->ip(); // IP del cliente
        $log->agent = request()->userAgent(); // Agente del usuario
        $log->timestamps = now(); // Marca de tiempo actual
        $log->query = $query; // Query ejecutado
        $log->save();
        return response()->json(['message' => 'Función eliminada correctamente'], 200);
    }
}
