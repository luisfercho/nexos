<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;

use Nexos\Account;
use Nexos\Customer;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

class ClientsController extends Controller
{
    public function index(Request $request){

        $clients = Customer::orderBy("id","DESC")->paginate(20);

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
                "message"=>$message,
                "client_id"=>$request->id
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

            $account = new Account([
                "product_id"=>'1',
                "number"=>self::getRandomNumber(),
                "balance"=>0,
                "status"=>1,
                'password'=>substr($request->document, -4, 4)
            ]);


            $client->accounts()->save($account);

            $respuesta = [
                "success"=> true,
                "message"=>"Cliente creado correctamente",
                "client_id"=>$client->id
            ];
        }
        return response()->json(
            $respuesta,
            $respuesta["success"]?200:422
        );
    }

    public function delete($id){
        $client = Customer::find($id);

        if(isset($client->id)){
            if(count($client->accounts)>0){
                $success = false;
                $message = "El cliente no puede ser eliminado, tiene cuentas asociadas.";
            }else{
                $client->delete();
                $success = true;
                $message = "Cliente eliminado correctamente";
            }
        }else{
            $success = false;
            $message = "El cliente no ha sido encontrado.";
        }
        $respuesta = [
            "success"=> $success,
            "message"=>$message
        ];
        return response()->json(
            $respuesta
        );
    }

    public function list(Request $request){
        if(isset($request->all()["query"])){
            $searchTerm = $request->all()["query"];
            $client = Customer::where("name",'LIKE',"%{$searchTerm}%")
                ->orWhere("last_name",'LIKE',"%{$searchTerm}%")
                ->select([
                    "customers.id",
                    DB::raw("CONCAT(customers.name,' ',customers.last_name) AS name")
                ])
                ->get();

            return response()->json(
                $client
            );
        }
    }

    protected function getRandomNumber(){
        $length = 10;
        $number = join('', array_map(function($value) { return $value == 1 ? mt_rand(1, 9) : mt_rand(0, 9); }, range(1, $length)));

        //check number
        $account_check = Account::where("number",$number)->get();
        if(isset($account_check) and count($account_check->all())){
            return self::getRandomNumber();
        }
        return $number;
    }
}
