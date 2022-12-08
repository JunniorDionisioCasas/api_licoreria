<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Detalle_user;
use App\Models\Direccion;

class UserController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function index_empleados()
    {
        $clientes = User::join('cargos', 'users.id_cargo', 'cargos.id_cargo')
                        ->leftJoin('direcciones', 'users.id_direccion', 'direcciones.id_direccion')
                        ->where("users.id_cargo", "!=", 1) //1=cliente, 2=admin
                        ->select('users.*', 'cargos.crg_nombre', 'direcciones.drc_direccion')
                        ->get();
        return $clientes;
    }
    public function store_empleado(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed'
        ]);
    
        $empleado = new User();
        $empleado->id_cargo = 2;
        $empleado->name = $request->name;
        $empleado->email = $request->email;
        $empleado->password = Hash::make($request->password);
        $empleado->profile_photo_path = $request->profile_photo_path;
        $empleado->usr_apellidos = $request->usr_apellidos;
        $empleado->usr_fecha_nacimiento = $request->usr_fecha_nacimiento;
        if ( $request->drc_direccion ) {
            $direccion = new Direccion();
            $direccion->id_direccion = $request->drc_direccion;
            $empleado->id_direccion = $request->id_direccion;
        }
        
        $empleado->save();

        return $empleado;
    }

    public function show_empleado($id)
    {
        $cliente = User::where("id_cargo", "=", 1) //1=cliente, 2=admin
                        ->where("id", $id)
                        ->first();
        return $cliente;
    }

    public function update_empleado(Request $request, $id)
    {
        //
    }

    public function destroy_empleado($id)
    {
        $cliente = User::where("id_cargo", "=", 1) //1=cliente, 2=admin
                        ->where("id", $id)
                        ->destroy();
        return $cliente;
    }
    
    public function index_clientes()
    {
        $clientes = User::where("id_cargo", "=", 1) //1=cliente, 2=admin
                        ->get();
        return $clientes;
    }

    public function register(Request $request){
        $request->validate([
          'name' => 'required',
          'email' => 'required|unique:users',
          'password' => 'required|confirmed'
        ]);
    
        $user = new User();
        $user->id_cargo = $request->id_cargo;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->profile_photo_path = $request->profile_photo_path;
        $user->usr_apellidos = $request->usr_apellidos;
        $user->usr_fecha_nacimiento = $request->usr_fecha_nacimiento;
        $user->id_direccion = $request->id_direccion;
        $user->save();
    
        return response()->json([
          'status' => 1,
          'msg' => '¡registro de usuario exitoso!',
          'id' => $user->id,
          'email' => $user->email,
          'usuario' => $user->name .' '. $user->usr_apellidos
        ]);
    }

    public function login(Request $request){
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        $user = User::where("email", "=", $request->email)->first();

        if( isset($user->id) ){
            if(Hash::check($request->password, $user->password)){
                //creamos el token
                $token = $user->createToken("auth_token")->plainTextToken;
                //si está todo ok
                return response()->json([
                    "status" => 1,
                    "msg" => "¡Usuario logueado exitosamente!",
                    "user_id" => $user->id,
                    "access_token" => $token,
                    "token_type" => "Bearer",

                ]);
            }else{
                return response()->json([
                    "status" => 0,
                    "msg" => "La password es incorrecta",
                ], 404);
            }

        }else{
            return response()->json([
                "status" => 0,
                "msg" => "Usuario no registrado",
            ], 404);
        }
    }

    public function userProfile(){
        return response()->json([
            'status' => 0,
            'msg' => 'Acerca del perfil de usuario',
            'data' => auth()->user()
        ]);
    }

    public function logout(){
        auth()->user()->tokens()->delete();

        return response()->json([
            "status" => 1,
            "msg" => "Cierre de Sesión",
        ]);
    }
}
