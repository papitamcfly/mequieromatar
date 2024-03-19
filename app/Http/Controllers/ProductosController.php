<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductosController extends Controller
{

    public function index()
    {
        DB::connection()->enableQueryLog();
        $producto = Producto::all();
        return response()->json($producto, 200);
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
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'descripcion'=>'required',
            'precio'=>'required|numeric'
        ],[
            'nombre.required'=>'el campo nombre es obligatorio',
            'nombre.max'=>'solo puedes poner 255 caracteres en este campo',
            'descripcion.required'=>'el campo descripcion es obligatorio',
            'precio.required'=>'el campo precio es obligatorio',
            'precio.numeric'=>'el campo precio debe ser numerico'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        Producto::create($request->all());     
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
        return response()->json(['message'=>'producto creado correctamente'],201);
    }

    public function show($id)
    {
        DB::connection()->enableQueryLog();

        $Producto = Producto::find($id);

        if (!$Producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
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
        return response()->json($Producto, 200);
    }

    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'descripcion'=>'required',
            'precio'=>'required|numeric'
        ],[
            'nombre.required'=>'el campo nombre es obligatorio',
            'nombre.max'=>'solo puedes poner 255 caracteres en este campo',
            'descripcion.required'=>'el campo descripcion es obligatorio',
            'precio.required'=>'el campo precio es obligatorio',
            'precio.numeric'=>'el campo precio debe ser numerico'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $Producto = Producto::find($id);

        if (!$Producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $Producto->update($request->all());
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
        return response()->json(['message' => 'Producto actualizado correctamente'], 200);
    }

    
    public function destroy($id)
    {
        DB::connection()->enableQueryLog();

        $Producto = Producto::find($id);

        if (!$Producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $Producto->delete();
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
        return response()->json(['message' => 'Producto eliminado correctamente'], 200);
    }

}
