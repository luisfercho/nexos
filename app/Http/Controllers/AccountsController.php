<?php

namespace Nexos\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Nexos\Account;
use Nexos\Customer;

class AccountsController extends Controller
{
    public function index(Request $request,$client_id){
        $client = (object) [
            "id"=>0
        ];
        if($client_id > 0){
            $tmp_client = Customer::find($client_id);
            $client = (object) [
                "id"=>$tmp_client->id,
                "name"=>$tmp_client->name
            ];
            $accounts = Account::where("customer_id",$client_id)
                ->join('products', 'accounts.product_id', '=', 'products.id')
                ->select([
                    'products.name as type',
                    'accounts.id',
                    'accounts.number',
                    'accounts.balance',
                    'accounts.status',
                    'accounts.active',
                ])
                ->orderBy("id","DESC")
                ->paginate(10);
        }else{
            $accounts = Account::
                  join('products', 'accounts.product_id', '=', 'products.id')
                ->join('customers', 'accounts.customer_id', '=', 'customers.id')
                ->select([
                    'customers.name as client_name',
                    'customers.last_name as client_last_name',
                    'products.name as type',
                    'accounts.id',
                    'accounts.number',
                    'accounts.balance',
                    'accounts.status',
                    'accounts.active',
                ])
                ->orderBy("id","DESC")
                ->paginate(10);
        }

        return response()->json([
            "client"    => $client,
            "accounts"  => $accounts
        ]);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'id'      => 'required',
            'balance' => 'min:0'
        ]);

        if ($validator->fails()){
            $errors =  $validator->errors()->all();
            return response()->json([
                "success"=> false,
                "message"=>implode('<br>', array_values($errors))
            ],422);
        }

        $client = Customer::find($request->id);

        if(isset($client->id)){
            $balance = 0;
            if(isset($request->balance)){
                $balance = $request->balance;
            }
            $active = 2;
            if($balance > 100000){
                $active = 1;
            }
            $account = new Account([
                "product_id"=>'1',
                "number"=>self::getRandomNumber(),
                "active"=>$active,
                "status"=>1,
                "balance"=>$balance,
                'password'=>Hash::make(substr($client->document, -4, 4))
            ]);

            $client->accounts()->save($account);

            $success = true;
            $message="Cuenta creada correctamente";

        }else{
            $success = false;
            $message = "El cliente no ha sido encontrado.";
        }
        $respuesta = [
            "success"=> $success,
            "message"=>$message,
            "client_id"=>$request->id
        ];

        return response()->json(
            $respuesta,
            $respuesta["success"]?200:422
        );
    }

    public function delete($account_id){

        $account = Account::find($account_id);

        if(isset($account->id)){
            $account->status = 2;
            $account->inactivity_date = Carbon::now();
            $account->save();
            $success = true;
            $message = "Cuenta inhabilitada correctamente";
        }else{
            $success = false;
            $message = "La cuenta no ha sido encontrada.";
        }
        $respuesta = [
            "success"=> $success,
            "message"=>$message
        ];
        return response()->json(
            $respuesta
        );
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

    public function list(Request $request){
        if(isset($request->all()["query"])){
            $searchTerm = $request->all()["query"];
            $accounts = Account::where("number",'LIKE',"%{$searchTerm}%")
                ->select([
                    "accounts.id",
                    "accounts.number",
                ])
                ->get();

            return response()->json(
                $accounts
            );
        }
    }
}
