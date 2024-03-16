<?php

namespace App\Http\Controllers;
use App\Mail\AccountActivationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tymon\JWTAuth\Providers\JWT\Provider;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected $auth;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','verifyCode']]);
    }

    public function generateTokenWithCustomGuard($user, $guardName)
    {
        $customClaims = [
            'abilities' => [$guardName],
        ];

        try {
            $token = JWTAuth::claims($customClaims)->fromUser($user);
        } catch (JWTException $e) {
            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al generar el token'], 500);
        }
        

        return $token;
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validar las credenciales
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        // Si la validación falla, devolver los errores
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        // Extraer las credenciales de la solicitud
        $credentials = $request->only('email', 'password');
    
        // Intentar autenticar al usuario con las credenciales
        if (!$tokenjwt = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales no válidas'], 401);
        }
        $user = User::where('email', $request->email)->first();
        log::info($user);
        if (!$user->is_active) {
            $this->mandarcorreo($user);
            return response()->json([
                'message' => 'Verifica tu correo para activar tu cuenta.'
            ],201);
        }
        

        $jwt = $this->generateTokenWithCustomGuard($user, 'limited-access');
        $token = $this->respondWithToken($jwt);

        $code = mt_rand(100000, 999999);
        $hashedCode = Hash::make($code);
        $expiresAt = now()->addMinutes(5); // Establece la expiración en 5 minutos
            // Almacenar el código en la base de datos
        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $hashedCode,
            'expires_at' => $expiresAt, // Agrega la fecha de expiración
    ]);

    // Enviar el código por correo electrónico
    Mail::to($user->email)->send(new VerificationCodeMail($code));
        // Si la autenticación es exitosa, responder con el token
        return response()->json(['message' => 'Verifica tu correo electrónico para obtener el código de verificación.','token'=>$token], 200);
    }

    public function verifyCode(Request $request)
    {
        
        $credentials = Auth::user();
        $user = User::where('email', $credentials->email)->first();

        log::info($user);
        $code = $request->input('code');

        log::info($code);
        //$hashedCode = Hash::make($code);
        $verificationCode = VerificationCode::where('user_id', $user->id)
                                                //->where('code', $hashedCode)
                                                ->where('is_used', false)
                                                ->first();

        log::info($verificationCode);
        
        if ($verificationCode || !Hash::check($code, $verificationCode->code)) {

            $jwt = $this->generateTokenWithCustomGuard($user, 'all-access');
            $token = $this->respondWithToken($jwt);
            // Se marca el codigo como condon usado
            $verificationCode->markAsUsed();
            
            return response()->json([
                'message' => 'Código de verificación correcto.',
                'token' => $token
            ], 200);
            
            } 
            
            
        else {
             // Código incorrecto
            return response()->json(['error' => 'El código de verificación es incorrecto o ha expirado.'], 401);
        }
    }
    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user'=>auth()->user(),
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
    if($validator->fails()){
        return response()->json($validator->errors()->toJson(),400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
        ));
        $token = JWTAuth::fromUser($user);
        log::info($token);

        $url = URL::temporarySignedRoute(
            'activate', now()->addMinutes(30), ['token' => $token]
        );

        Mail::to($user->email)->send(new AccountActivationMail($url));
        return response()->json([
            'message' => 'usuario registrado correctamente. verifica tu correo para activar tu cuenta ', 'user'=>$user
        ],201);
    }

    public function mandarcorreo($user)
    {
        $token = JWTAuth::fromUser($user);
        $url = URL::temporarySignedRoute(
            'activate', now()->addMinutes(30), ['token' => $token]
        );

        Mail::to($user->email)->send(new AccountActivationMail($url));
                return response()->json([
            'message' => 'Verifica tu correo para activar tu cuenta.'
        ],201);
    }
    public function activate($token)
    {
        $user = JWTAuth::parseToken()->authenticate();
 
        if ($user->is_active  == 0) {
            $user->is_active = 1;
            $user->save();
 
            return response()->json([
                'success' => true,
                'message' => 'Account activated successfully.',
            ]);
        }
 
        return response()->json([
            'success' => false,
            'message' => 'Account is already activated.',
        ]);
    }

    
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}