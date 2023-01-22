<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Detalle_user;
use App\Models\Direccion;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    /* apis Empleados */

    public function index_empleados()
    {
        $clientes = User::join('cargos', 'users.id_cargo', 'cargos.id_cargo')
                        ->leftJoin('direcciones', 'users.id_direccion', 'direcciones.id_direccion')
                        ->leftJoin('distritos', 'direcciones.id_distrito', 'distritos.id_distrito')
                        ->leftJoin('provincias', 'distritos.id_provincia', 'provincias.id_provincia')
                        ->where("users.id_cargo", "!=", 1) //1=cliente, 2=admin
                        ->select('users.*', 'cargos.crg_nombre', 'direcciones.drc_direccion', 'direcciones.id_distrito', 'distritos.id_provincia')
                        ->get();
        return $clientes;
    }
    public function store_empleado(Request $request)
    {
        // validation
        $rules = [
            'name' => 'required',
            'usr_apellidos' => 'required',
            'email' => 'required|unique:users',
            'usr_num_documento' => 'required|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
            ]
        ];
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'El campo :attribute ya está en uso.',
            'confirmed' => 'El campo :attribute no coincide con su campo de confirmación :other.'
        ];
        $atributtes = [
            'email' => 'correo electrónico',
            'name' => 'nombre',
            'usr_apellidos' => 'apellidos',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'usr_num_documento' => 'DNI'
        ];
        $validator = Validator::make( $request->all(), $rules, $messages, $atributtes );

        if ($validator->fails()) {
            /* return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput(); */
            return response()->json([
                                        "status" => 0,
                                        "msg" => "Los datos no son válidos.",
                                        "validator_errors" => $validator->errors(),
                                    ], 500);
        }

        // Retrieve the validated input...
        $validated = $validator->validated();
    
        $empleado = new User();
        $empleado->id_cargo = $request->id_cargo;
        $empleado->name = $request->name;
        $empleado->email = $request->email;
        $empleado->password = Hash::make($request->password);
        $empleado->usr_apellidos = $request->usr_apellidos;
        $empleado->usr_fecha_nacimiento = $request->usr_fecha_nacimiento;
        $empleado->usr_num_documento = $request->usr_num_documento;
        if ( $request->drc_direccion ) {
            $check_existing_direction = Direccion::where('drc_direccion', $request->drc_direccion)
                                                ->where('id_distrito', $request->id_distrito)
                                                ->first();
            if($check_existing_direction){
                $empleado->id_direccion = $check_existing_direction->id_direccion;
            } else {
                $direccion = new Direccion();
                $direccion->drc_direccion = $request->drc_direccion;
                $direccion->id_distrito = $request->id_distrito;
                $direccion->save();

                $empleado->id_direccion = $direccion->id_direccion;
            }
        }
        // subiendo imagen
        if ( $request->profile_photo_path ) {
            $rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
            $folder_destination = '/images/empleados/';
            $file = $request->profile_photo_path;

            // file name corrections
            $file_extension = $file->getClientOriginalExtension();
            $file_name_modified = str_replace('ñ', 'n', $request->name . '_' . $request->usr_apellidos);
            $file_name_modified = str_replace(' ', '_', $file_name_modified);
            $file_name_modified = strtolower($file_name_modified . '.' . $file_extension);

            $file->move($rootDir.$folder_destination, $file_name_modified );
            $file_path = config('app.domainUrl.urlApiPublic') . $folder_destination . $file_name_modified;
            $empleado->profile_photo_path = $file_path;
        }
        
        $empleado->save();

        return $empleado;
    }

    public function show_empleado($id)
    {
        $cliente = User::where("id_cargo", "!=", 1) //1=cliente, 2=admin
                        ->where("id", $id)
                        ->first();
        return $cliente;
    }

    public function update_empleado(Request $request, $id)
    {
        // validation
        $rules = [
            'name' => 'required',
            'usr_apellidos' => 'required',
            'usr_num_documento' => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'email' => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
            ]
        ];
        // ->uncompromised()
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'El campo :attribute ya está en uso.',
            'confirmed' => 'El campo :attribute no coincide con su campo de confirmación :other.'
        ];
        $atributtes = [
            'email' => 'correo electrónico',
            'name' => 'nombre',
            'usr_apellidos' => 'apellidos',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'usr_num_documento' => 'DNI'
        ];
        $validator = Validator::make( $request->all(), $rules, $messages, $atributtes );

        if ($validator->fails()) {
            return response()->json([
                                        "status" => 0,
                                        "msg" => "Los datos no son válidos.",
                                        "validator_errors" => $validator->errors(),
                                    ], 500); //response status = 500 so that frontend fetch get as error
        }

        // Retrieve the validated input...
        $validated = $validator->validated();
    
        $empleado = User::where("id_cargo", "!=", 1) //1=cliente
                        ->where("id", $id)
                        ->first();
        $empleado->id_cargo = $request->id_cargo;
        $empleado->name = $request->name;
        $empleado->email = $request->email;
        $empleado->usr_apellidos = $request->usr_apellidos;
        $empleado->usr_num_documento = $request->usr_num_documento;
        if ( $request->password ) {
            $empleado->password = Hash::make($request->password);
        }
        if ( $request->usr_fecha_nacimiento ) {
            $empleado->usr_fecha_nacimiento = $request->usr_fecha_nacimiento;
        }
        if ( $request->drc_direccion ) {
            $check_existing_direction = Direccion::where('drc_direccion', $request->drc_direccion)
                                                ->where('id_distrito', $request->id_distrito)
                                                ->first();
            if($check_existing_direction){
                $empleado->id_direccion = $check_existing_direction->id_direccion;
            } else {
                $direccion = new Direccion();
                $direccion->drc_direccion = $request->drc_direccion;
                $direccion->id_distrito = $request->id_distrito;
                $direccion->save();

                $empleado->id_direccion = $direccion->id_direccion;
            }
        }
        // actualizando imagen
        if ( $request->profile_photo_path ) {
            $rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
            $folder_destination = '/images/empleados/';
            $file = $request->profile_photo_path;

            // file name corrections
            $file_extension = $file->getClientOriginalExtension();
            $file_name_modified = str_replace('ñ', 'n', $request->name . '_' . $request->usr_apellidos);
            $file_name_modified = str_replace(' ', '_', $file_name_modified);
            $file_name_modified = strtolower($file_name_modified . '.' . $file_extension);

            //delete previous file
            try{
                $previousFilePath = $empleado->profile_photo_path;
                //if file is stored in api subdomain
                $previousFilePath = str_replace(env("URL_API_PUBLIC").'/', '', $previousFilePath);
                //if file is stored in main domain
                $previousFilePath = str_replace('https://licoreriasansebastian.com/storage', '/licoreriasansebastian.com/storage/app/public', $previousFilePath);
                //if file is stored in admin subdomain
                $previousFilePath = str_replace('https://admin.licoreriasansebastian.com/storage', '/admin.licoreriasansebastian.com/storage/app/public', $previousFilePath);
                File::delete($previousFilePath);
            }catch(Throwable $e){
                report($e);
            }

            $file->move($rootDir.$folder_destination, $file_name_modified );
            $file_path = config('app.domainUrl.urlApiPublic') . $folder_destination . $file_name_modified;
            $empleado->profile_photo_path = $file_path;
        }
        
        $empleado->save();

        return $empleado;
    }

    public function destroy_empleado($id)
    {
        $empleado = User::where("id_cargo", "!=", 1) //1=cliente, 2=admin
                        ->where("id", $id)
                        ->delete();
        return $empleado;
    }
    
    /* Clientes */
    public function index_clientes()
    {
        $clientes = User::leftJoin('direcciones', 'users.id_direccion', 'direcciones.id_direccion')
                        ->leftJoin('distritos', 'direcciones.id_distrito', 'distritos.id_distrito')
                        ->leftJoin('provincias', 'distritos.id_provincia', 'provincias.id_provincia')
                        ->where("id_cargo", "=", 1) //1=cliente, 2=admin
                        ->select('users.*', 'direcciones.drc_direccion', 'direcciones.id_distrito', 'distritos.id_provincia')
                        ->get();
        return $clientes;
    }

    public function update_cliente(Request $request, $id)
    {
        // validation
        $rules = [
            'name' => 'required',
            'usr_apellidos' => 'required',
            'usr_num_documento' => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'email' => [
                'required',
                Rule::unique('users')->ignore($id),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
            ]
        ];
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'El campo :attribute ya está en uso.',
            'confirmed' => 'El campo :attribute no coincide con su campo de confirmación.'
        ];
        $atributtes = [
            'email' => 'correo electrónico',
            'name' => 'nombre',
            'usr_apellidos' => 'apellidos',
            'usr_num_documento' => 'DNI',
            'password' => 'contraseña'
        ];
        $validator = Validator::make( $request->all(), $rules, $messages, $atributtes );

        if ($validator->fails()) {
            return response()->json([
                                        "status" => 0,
                                        "msg" => "Los datos no son válidos.",
                                        "validator_errors" => $validator->errors(),
                                    ], 500); //response status = 500 so that frontend fetch get as error
        }

        // Retrieve the validated input...
        $validated = $validator->validated();
    
        $cliente = User::where("id_cargo", "=", 1) //1=cliente
                        ->where("id", $id)
                        ->first();
        
        if (!$cliente) {
            return response()->json([
                                        "status" => 0,
                                        "msg" => "Usuario no encontrado."
                                    ], 404);
        }
        
        $cliente->name = $request->name;
        $cliente->email = $request->email;
        $cliente->usr_apellidos = $request->usr_apellidos;
        $cliente->usr_num_documento = $request->usr_num_documento;
        if ( $request->usr_fecha_nacimiento ) {
            $cliente->usr_fecha_nacimiento = $request->usr_fecha_nacimiento;
        }
        if ( $request->drc_direccion ) {
            $check_existing_direction = Direccion::where('drc_direccion', $request->drc_direccion)
                                                ->where('id_distrito', $request->id_distrito)
                                                ->first();
            if($check_existing_direction){
                $cliente->id_direccion = $check_existing_direction->id_direccion;
            } else {
                $direccion = new Direccion();
                $direccion->drc_direccion = $request->drc_direccion;
                $direccion->id_distrito = $request->id_distrito;
                $direccion->save();

                $cliente->id_direccion = $direccion->id_direccion;
            }
        }
        // actualizando imagen
        if ( $request->profile_photo_path ) {
            $rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
            $folder_destination = '/images/clientes/';
            $file = $request->profile_photo_path;

            // file name corrections
            $file_extension = $file->getClientOriginalExtension();
            $file_name_modified = str_replace('ñ', 'n', $request->name . '_' . $request->usr_apellidos);
            $file_name_modified = str_replace(' ', '_', $file_name_modified);
            $file_name_modified = strtolower($file_name_modified . '.' . $file_extension);

            //delete previous file
            try{
                $previousFilePath = $cliente->profile_photo_path;
                // $previousFilePath = is_dir($previousFilePath) ? rtrim($previousFilePath, '\/') . '/' : $previousFilePath;
                // $previousFilePath = str_replace('\\', '/', $previousFilePath);
                // $previousFilePath = explode('/', $previousFilePath);
                // $previousFilePath = $previousFilePath[2].'/'.$previousFilePath[3];
                // pathinfo($cliente->profile_photo_path)['dirname']
                //if file is stored in api subdomain
                $previousFilePath = str_replace(env("URL_API_PUBLIC").'/', '', $previousFilePath);
                //if file is stored in main domain
                $previousFilePath = str_replace('https://licoreriasansebastian.com/storage', '/licoreriasansebastian.com/storage/app/public', $previousFilePath);
                //if file is stored in admin subdomain
                $previousFilePath = str_replace('https://admin.licoreriasansebastian.com/storage', '/admin.licoreriasansebastian.com/storage/app/public', $previousFilePath);
                
                // $previousFilePath = app_path().'/public'.$previousFilePath;
                File::delete($previousFilePath);
            }catch(Throwable $e){
                report($e);
            }

            $file->move($rootDir.$folder_destination, $file_name_modified );
            $file_path = config('app.domainUrl.urlApiPublic') . $folder_destination . $file_name_modified;
            //assign new photo path to user
            $cliente->profile_photo_path = $file_path;
        }
        
        $cliente->save();

        return $cliente;
    }

    public function destroy_cliente($id)
    {
        $cliente = User::where("id_cargo", "=", 1) //1=cliente, 2=admin
                        ->where("id", $id)
                        ->delete();
        return $cliente;
    }

    /* register function only for clients */
    public function register(Request $request){
        // validation
        $rules = [
            'name' => 'required',
            'usr_apellidos' => 'required',
            'email' => 'required|unique:users',
            'usr_num_documento' => 'required|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
            ]
        ];
        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'unique' => 'El campo :attribute ya está en uso.',
            'confirmed' => 'El campo :attribute no coincide con su campo de confirmación.'
        ];
        $atributtes = [
            'email' => 'correo electrónico',
            'name' => 'nombre',
            'usr_apellidos' => 'apellidos',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'usr_num_documento' => 'DNI'
        ];
        $validator = Validator::make( $request->all(), $rules, $messages, $atributtes );

        if ($validator->fails()) {
            /* return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput(); */
            return response()->json([
                                        "status" => 0,
                                        "msg" => "Los datos no son válidos.",
                                        "validator_errors" => $validator->errors(),
                                    ], 500);
        }

        // Retrieve the validated input...
        $validated = $validator->validated();
    
        $user = new User();
        $user->id_cargo = 1; //id_cargo:1=client
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->profile_photo_path = $request->profile_photo_path;
        $user->usr_apellidos = $request->usr_apellidos;
        $user->usr_fecha_nacimiento = $request->usr_fecha_nacimiento;
        $user->usr_num_documento = $request->usr_num_documento;
        if ( $request->drc_direccion ) {
            $direccion = new Direccion();
            $direccion->drc_direccion = $request->drc_direccion;
            $direccion->id_distrito = $request->id_distrito;
            $direccion->save();

            $user->id_direccion = $direccion->id_direccion;
        }
        
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

        $user = User::leftJoin('direcciones', 'users.id_direccion', 'direcciones.id_direccion')
                    ->where("users.email", "=", $request->email)
                    ->first();

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
                    "user_name" => $user->name,
                    "usr_apellidos" => $user->usr_apellidos,
                    "user_mail" => $user->email,
                    "user_address" => $user->drc_direccion,
                    "usr_num_documento" => $user->usr_num_documento,
                    "usr_fecha_nacimiento" => $user->usr_fecha_nacimiento,
                    "user_profile_photo_path" => $user->profile_photo_path
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

    public function googleVerifiedLogin(Request $request){
        /* try {
            $decryptedemail = Crypt::decryptString($request->encryptedEmail);
        } catch (DecryptException $e) {
            return response()->json([
                "status" => 0,
                "msg" => "Error al verificar, intente más tarde",
            ], 500);
        }
        $decryptedemail = $request->encryptedEmail; */

        $user = User::leftJoin('direcciones', 'users.id_direccion', 'direcciones.id_direccion')
                    ->where("users.email", "=", $request->email)
                    ->first();

        if( isset($user->id) ){
            //creamos el token
            $token = $user->createToken("auth_token")->plainTextToken;
            //si está todo ok
            return response()->json([
                "status" => 1,
                "msg" => "¡Usuario logueado exitosamente!",
                "user_id" => $user->id,
                "access_token" => $token,
                "token_type" => "Bearer",
                "user_name" => $user->name,
                "usr_apellidos" => $user->usr_apellidos,
                "user_mail" => $user->email,
                "user_address" => $user->drc_direccion,
                "usr_num_documento" => $user->usr_num_documento,
                "usr_fecha_nacimiento" => $user->usr_fecha_nacimiento,
                "user_profile_photo_path" => $user->profile_photo_path
            ]);
        }else{
            return response()->json([
                "status" => 0,
                "msg" => "Usuario no registrado",
            ], 404);
        }
    }
}
