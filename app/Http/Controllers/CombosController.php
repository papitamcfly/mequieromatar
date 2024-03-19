<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CombosController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'productos' => 'required|array|min:1',
            'productos.*' => 'exists:productos,id'
        ],[
            'nombre.required' => 'El título de la combo es obligatorio.',
            'nombre.string' => 'El título de la combo debe ser una cadena de texto.',
            'nombre.max' => 'El título de la combo no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La sinopsis de la combo es obligatoria.',
            'precio.required' => 'La duración de la combo es obligatoria.',
            'precio.numeric' => 'La duración de la combo debe ser un valor numérico.',
            'productos.required' => 'Debe seleccionar al menos un género para la combo.',
            'productos.array' => 'Los géneros de la combo deben ser un array.',
            'productos.min' => 'Debe seleccionar al menos un producto para el combo.',
            'productos.*.exists' => 'Uno o más de los productos seleccionados no existen.'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Crea una nueva película
        $combo = new Combo();
        $combo->nombre = $request->nombre;
        $combo->descripcion = $request->descripcion;
        $combo->precio = $request->precio;
        $combo->save();
    
        // Adjunta los géneros seleccionados a la película
        $combo->productos()->attach($request->productos);
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
        return response()->json([
            'message' => 'Combo creado correctamente',
            'combo' => $combo
        ], 201);
    }
    public function destroy($id){
        DB::connection()->enableQueryLog();

        $combo = Combo::find($id);
        if (!$combo) return response()->json(['message'=>'combo no encontrado'],404);
        $combo->delete();
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
        return response()->json(['message'=>'combo eliminado'],200);

    }
    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'productos' => 'required|array|min:1',
            'productos.*' => 'exists:productos,id'
        ],[
            'nombre.required' => 'El título de la combo es obligatorio.',
            'nombre.string' => 'El título de la combo debe ser una cadena de texto.',
            'nombre.max' => 'El título de la combo no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La sinopsis de la combo es obligatoria.',
            'precio.required' => 'La duración de la combo es obligatoria.',
            'precio.numeric' => 'La duración de la combo debe ser un valor numérico.',
            'productos.required' => 'Debe seleccionar al menos un género para la combo.',
            'productos.array' => 'Los géneros de la combo deben ser un array.',
            'productos.min' => 'Debe seleccionar al menos un producto para la combo.',
            'productos.*.exists' => 'Uno o más de los géneros seleccionados no existen en la base de datos.'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Buscar el maestro por su ID
        $maestro = Combo::findOrFail($id);

        // Actualizar los datos del maestro
        $maestro->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
        ]);

        // Sincronizar las materias asociadas al maestro
        $maestro->productos()->sync($request->productos);
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
        // Redireccionar con un mensaje de éxito
        return response()->json(['message'=>'combo actualizado','combo'=>$maestro]);
    }
    public function index(){
        DB::connection()->enableQueryLog();
        $maestros = Combo::with('productos')->get();
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
        return response()->json($maestros);
    }

    public function show($id)
    {
        DB::connection()->enableQueryLog();

        $Combo = Combo::with('productos')->get()->find($id);

        if (!$Combo) {
            return response()->json(['message' => 'Combo no encontrado'], 404);
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
        return response()->json($Combo, 200);
    }

}
