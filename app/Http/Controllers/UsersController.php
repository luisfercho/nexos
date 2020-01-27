<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;
use Nexos\User;
use Hash;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function index(Request $request){

        $users = User::orderBy("name","ASC")->paginate(20);

        return response()->json(
            $users
        );
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|unique:users,email,'.$request->id,
        ]);

        if ($validator->fails()){
            $errors =  $validator->errors()->all();
            return response()->json([
                "success"=> false,
                "message"=>implode('<br>', array_values($errors))
            ],422);
        }

        if($request->id > 0){
            $user = User::find($request->id);

            if(isset($user->id)){

                if($request->modal_type == 2){
                    $user->name = $request->name;
                    $user->email = $request->email;
                }else{
                    $user->password  = Hash::make($request->password);
                }

                $user->save();

                $success = true;
                $message = "Usuario actualizado correctamente";
            }else{
                $success = false;
                $message = "El usuario no ha sido encontrado.";
            }
            $respuesta = [
                "success"=> $success,
                "message"=>$message,
                "client_id"=>$request->id
            ];
        }else{
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            $respuesta = [
                "success"=> true,
                "message"=>"Usuario creado correctamente",
                "client_id"=>$user->id
            ];
        }
        return response()->json(
            $respuesta,
            $respuesta["success"]?200:422
        );
    }

    public function delete($id){
        $user = User::find($id);

        if(isset($user->id)){
            if(count($user->transactions)>0){
                $success = false;
                $message = "El usuario no puede ser eliminado, tiene transacciones asociadas.";
            }else{
                $user->delete();
                $success = true;
                $message = "Usuario eliminado correctamente";
            }
        }else{
            $success = false;
            $message = "El usuario no ha sido encontrado.";
        }
        $respuesta = [
            "success"=> $success,
            "message"=>$message
        ];
        return response()->json(
            $respuesta
        );
    }

    public function togleStatus($id){
        $user = User::find($id);

        if(isset($user->id)){
            if($user->status == 1){
                $user->status = 2;
                $message = "Usuario inactivado correctamente.";
            }else{
                $user->status = 1;
                $message = "Usuario activado correctamente";
            }

            $user->save();

            $success = true;
        }else{
            $success = false;
            $message = "El usuario no ha sido encontrado.";
        }
        $respuesta = [
            "success"=> $success,
            "message"=>$message
        ];
        return response()->json(
            $respuesta
        );
    }
}
