<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cine;
use App\Models\RequestLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class cineController extends Controller
{
    public function index(){
        DB::connection()->enableQueryLog();
        $cines =  Cine::all();
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
        return response()->json($cines, 200);

    }

    public function store(Request $request){
        $validate = Validator::make($request->all(),[
            'nombre'  => 'required|min:2|max:20|string',
            'dirección' => 'required|min:10|max:40|string',
            'ciudad' => 'required|min:5|max:20|string',
            'capacidad_total' => 'required|integer|min:1'
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.min' => 'El campo nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El campo nombre no debe tener más de 20 caracteres.',
            'dirección.required' => 'El campo dirección es obligatorio.',
            'dirección.min' => 'El campo dirección debe tener al menos 10 caracteres.',
            'dirección.max' => 'El campo dirección no debe tener más de 40 caracteres.',
            'ciudad.required' => 'El campo ciudad es obligatorio.',
            'ciudad.min' => 'El campo ciudad debe tener al menos 5 caracteres.',
            'ciudad.max' => 'El campo ciudad no debe tener más de 20 caracteres.',
            'capacidad_total.required' => 'El campo capacidad total es obligatorio.',
            'capacidad_total.integer' => 'El campo capacidad total debe ser un número entero.',
            'capacidad_total.min' => 'El campo capacidad total debe ser al menos 1.',
        ]);

        if($validate->fails()){
        return response()->json(['errors' =>$validate->errors()],422);
        }

        Cine::create($request->all());
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
        return response()->json('Cine creado correctamente', 201);
    }

    public function show($id){
        DB::connection()->enableQueryLog();

        $cine = Cine::find($id);

        if(!$cine){
            return response()->json(['message'=> 'Cine no encontrado'],404);
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
        return response()->json($cine, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre'  => 'required|min:2|max:20|string',
            'dirección' => 'required|min:10|max:40|string',
            'ciudad' => 'required|min:5|max:20|string',
            'capacidad_total' => 'required|integer|min:1'
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.min' => 'El campo nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El campo nombre no debe tener más de 20 caracteres.',
            'dirección.required' => 'El campo dirección es obligatorio.',
            'dirección.min' => 'El campo dirección debe tener al menos 10 caracteres.',
            'dirección.max' => 'El campo dirección no debe tener más de 40 caracteres.',
            'ciudad.required' => 'El campo ciudad es obligatorio.',
            'ciudad.min' => 'El campo ciudad debe tener al menos 5 caracteres.',
            'ciudad.max' => 'El campo ciudad no debe tener más de 20 caracteres.',
            'capacidad_total.required' => 'El campo capacidad total es obligatorio.',
            'capacidad_total.integer' => 'El campo capacidad total debe ser un número entero.',
            'capacidad_total.min' => 'El campo capacidad total debe ser al menos 1.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cine = Cine::find($id);

        if (!$cine) {
            return response()->json(['message' => 'Cine no encontrado'], 404);
        }

        $cine->update($request->all());
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
        return response()->json($cine);
    }

    public function destroy($id)
    {
        DB::connection()->enableQueryLog();
        $cine = Cine::find($id);

        if (!$cine) {
            return response()->json(['message' => 'Cine no encontrado'], 404);
        }

        $cine->delete();
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
        return response()->json(['message' => 'Cine eliminado correctamente'], 200);
    }
}
