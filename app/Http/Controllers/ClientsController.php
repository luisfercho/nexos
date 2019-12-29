<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;

use Nexos\Customer;

use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{
    public function index(Request $request){

        $clients = Customer::paginate(20);

        return response()->json(
            $clients
        );
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'last_name' => 'required',
            'email'     => 'required|unique:customers,email,'.$request->id,
            'document'  => 'required|unique:customers,document,'.$request->id,
        ]);

        if ($validator->fails()){
            $errors =  $validator->errors()->all();
            return response()->json([
                "success"=> false,
                "message"=>implode('<br>', array_values($errors))
            ],422);
        }

        if($request->id > 0){
            $client = Customer::find($request->id);

            if(isset($client->id)){

                $client->name = $request->name;
                $client->last_name = $request->last_name;
                $client->document_type = $request->document_type;
                $client->document = $request->document;
                $client->email = $request->email;
                $client->cellphone = $request->cellphone;
                $client->address = $request->address;
                $client->save();

                $success = true;
                $message = "Cliente actualizado correctamente";
            }else{
                $success = false;
                $message = "El cliente no ha sido encontrado.";
            }
            $respuesta = [
                "success"=> $success,
                "message"=>$message
            ];
        }else{
            $client = new Customer();
            $client->name = $request->name;
            $client->last_name = $request->last_name;
            $client->document_type = $request->document_type;
            $client->document = $request->document;
            $client->email = $request->email;
            $client->cellphone = $request->cellphone;
            $client->address = $request->address;
            $client->save();

            $respuesta = [
                "success"=> true,
                "message"=>"Cliente creado correctamente"
            ];
        }
        return response()->json(
            $respuesta
        );
    }
}
