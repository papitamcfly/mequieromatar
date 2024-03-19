<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Genero;
use App\Models\Pelicula;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PeliculasController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'titulo' => 'required|string|max:255',
            'sinopsis' => 'required',
            'duracion' => 'required|numeric',
            'clasificacion' => 'required',
            'generos' => 'required|array|min:1',
            'generos.*' => 'exists:generos,id'
        ],[
            'titulo.required' => 'El título de la película es obligatorio.',
            'titulo.string' => 'El título de la película debe ser una cadena de texto.',
            'titulo.max' => 'El título de la película no debe exceder los 255 caracteres.',
            'sinopsis.required' => 'La sinopsis de la película es obligatoria.',
            'duracion.required' => 'La duración de la película es obligatoria.',
            'duracion.numeric' => 'La duración de la película debe ser un valor numérico.',
            'clasificacion.required' => 'La clasificación de la película es obligatoria.',
            'generos.required' => 'Debe seleccionar al menos un género para la película.',
            'generos.array' => 'Los géneros de la película deben ser un array.',
            'generos.min' => 'Debe seleccionar al menos un género para la película.',
            'generos.*.exists' => 'Uno o más de los géneros seleccionados no existen en la base de datos.'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Crea una nueva película
        $pelicula = new Pelicula();
        $pelicula->titulo = $request->titulo;
        $pelicula->sinopsis = $request->sinopsis;
        $pelicula->duracion = $request->duracion;
        $pelicula->clasificacion = $request->clasificacion;
        $pelicula->save();
    
        // Adjunta los géneros seleccionados a la película
        $pelicula->generos()->attach($request->generos);
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
            'message' => 'Película creada correctamente',
            'pelicula' => $pelicula
        ], 201);
    }
    public function destroy($id){
        DB::connection()->enableQueryLog();

        $peli = Pelicula::find($id);
        if (!$peli) return response()->json(['message'=>'combo no encontrado'],404);
        $peli->delete();
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
        return response()->json(['message'=>'Pelicula eliminada'],200);

    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(),[
            'titulo' => 'required|string|max:255',
            'sinopsis' => 'required',
            'duracion' => 'required|numeric',
            'clasificacion' => 'required',
            'generos' => 'required|array|min:1',
            'generos.*' => 'exists:generos,id'
        ],[
            'titulo.required' => 'El título de la película es obligatorio.',
            'titulo.string' => 'El título de la película debe ser una cadena de texto.',
            'titulo.max' => 'El título de la película no debe exceder los 255 caracteres.',
            'sinopsis.required' => 'La sinopsis de la película es obligatoria.',
            'duracion.required' => 'La duración de la película es obligatoria.',
            'duracion.numeric' => 'La duración de la película debe ser un valor numérico.',
            'clasificacion.required' => 'La clasificación de la película es obligatoria.',
            'generos.required' => 'Debe seleccionar al menos un género para la película.',
            'generos.array' => 'Los géneros de la película deben ser un array.',
            'generos.min' => 'Debe seleccionar al menos un género para la película.',
            'generos.*.exists' => 'Uno o más de los géneros seleccionados no existen en la base de datos.'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Buscar el maestro por su ID
        $maestro = Pelicula::findOrFail($id);

        // Actualizar los datos del maestro
        $maestro->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'clasificacion'=>$request->clasificacion
        ]);

        // Sincronizar las materias asociadas al maestro
        $maestro->generos()->sync($request->generos);
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
        return response()->json(['message'=>'Pelicula actualizada','Pelicula'=>$maestro]);
    }


    public function index(){
        DB::connection()->enableQueryLog();

        $maestros = Pelicula::with('generos')->get();
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
        return response()->json($maestros);
    }

    public function show($id)
    {
        DB::connection()->enableQueryLog();

        $Pelicula = Pelicula::with('generos')->get()->find($id);

        if (!$Pelicula) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
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
        return response()->json($Pelicula, 200);
    }
}
