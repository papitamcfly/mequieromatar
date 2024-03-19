<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Boleto;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;

class BoletoController extends Controller
{
  
    public function index()
    {
        DB::connection()->enableQueryLog();
        $boletos = Boleto::all();
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
        return response()->json($boletos, 200);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_funcion' => 'required|exists:funciones,id',
            'id_user' => 'required|exists:users,id',
            'fila' => 'required',
            'asiento' => 'required',
            'precio' => 'required|numeric',
        ], [
            'id_funcion.required' => 'El campo id_funcion es obligatorio.',
            'id_funcion.exists' => 'El id_funcion proporcionado no existe.',
            'id_user.required' => 'El campo id_user es obligatorio.',
            'id_user.exists' => 'El id_user proporcionado no existe.',
            'fila.required' => 'El campo fila es obligatorio.',
            'asiento.required' => 'El campo asiento es obligatorio.',
            'precio.required' => 'El campo precio es obligatorio.',
            'precio.numeric' => 'El campo precio debe ser un número.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Boleto::create($request->all());
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
        return response()->json(['message' => 'Boleto creado correctamente'], 201);
    }

    
    public function show($id)
    {
        DB::connection()->enableQueryLog();

        $boleto = Boleto::find($id);

        if (!$boleto) {
            return response()->json(['message' => 'Boleto no encontrado'], 404);
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
        return response()->json($boleto, 200);
    }

    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_funcion' => 'required|exists:funciones,id',
            'id_user' => 'required|exists:users,id',
            'fila' => 'required',
            'asiento' => 'required',
            'precio' => 'required|numeric',
        ], [
            'id_funcion.required' => 'El campo id_funcion es obligatorio.',
            'id_funcion.exists' => 'El id_funcion proporcionado no existe.',
            'id_user.required' => 'El campo id_user es obligatorio.',
            'id_user.exists' => 'El id_user proporcionado no existe.',
            'fila.required' => 'El campo fila es obligatorio.',
            'asiento.required' => 'El campo asiento es obligatorio.',
            'precio.required' => 'El campo precio es obligatorio.',
            'precio.numeric' => 'El campo precio debe ser un número.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $boleto = Boleto::find($id);

        if (!$boleto) {
            return response()->json(['message' => 'Boleto no encontrado'], 404);
        }

        $boleto->update($request->all());
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
        return response()->json(['message' => 'Boleto actualizado correctamente'], 200);
    }

    
    public function destroy($id)
    {
        DB::connection()->enableQueryLog();

        $boleto = Boleto::find($id);

        if (!$boleto) {
            return response()->json(['message' => 'Boleto no encontrado'], 404);
        }

        $boleto->delete();
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
        return response()->json(['message' => 'Boleto eliminado correctamente'], 200);
    }
}
