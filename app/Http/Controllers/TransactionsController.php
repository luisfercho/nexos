<?php

namespace Nexos\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Nexos\Account;
use Illuminate\Support\Facades\Hash;
use Nexos\Transaction;

class TransactionsController extends Controller
{
    public function index(Request $request,$account_id){
        $account = (object) [
            "id"=>0
        ];

        if($account_id > 0){
            $tmp_account = Account::find($account_id);
            $account = (object) [
                "id"=>$tmp_account->id,
                "number"=>$tmp_account->number
            ];
            $transactions = Transaction::where("account_id",$account_id)
                ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->select([
                    'users.name as user',
                    'accounts.number',
                    'transactions.id',
                    'transactions.type',
                    'transactions.date',
                    'transactions.amount',
                    'transactions.description',
                    'transactions.created_at',
                ])
                ->orderBy("id","DESC")
                ->paginate(10);
        }else{
            $transactions = Transaction::
            join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->select([
                    'users.name as user',
                    'accounts.number as account_number',
                    'transactions.id',
                    'transactions.type',
                    'transactions.date',
                    'transactions.amount',
                    'transactions.description',
                    'transactions.created_at',
                ])
                ->orderBy("id","DESC")
                ->paginate(10);
        }

        return response()->json([
            "account"       => $account,
            "transactions"  => $transactions
        ]);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'account_id'  => 'required',
            'amount'      => 'required|min:0',
            'type'        => 'required',
            'password'    => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()){
            $errors =  $validator->errors()->all();
            return response()->json([
                "success"=> false,
                "message"=>implode('<br>', array_values($errors))
            ],422);
        }

        $account = Account::find($request->account_id);

        if($account->balance < $request->amount and $request->type == 1){
            return response()->json([
                "success"=> false,
                "message"=>"Esta cuenta no tiene saldo suficiente para realizar esta operación."
            ],422);
        }

        if (!Hash::check($request->password, $account->password)) {
            return response()->json([
                "success"=> false,
                "message"=>"La contraseña es incorrecta."
            ],422);
        }

        $transaction = new Transaction();

        $transaction->type          = $request->type;
        $transaction->date          = Carbon::now();
        $transaction->amount        = $request->amount;
        $transaction->description   = $request->description;
        $transaction->account_id    = $request->account_id;
        $transaction->user_id       = Auth::user()->id;

        $transaction->save();

        if(isset($transaction->id)){
            $balance = $account->balance;
            if($request->type == 2){
                $account->balance = $balance + $request->amount;
            }else{
                $account->balance = $balance - $request->amount;
            }

            if($account->balance > 100000 and $account->active == 2){
                $account->active = 1;
            }
            $account->save();

            return response()->json([
                "success"=> true,
                "message"=>"Transacción exitosa."
            ],200);
        }else{
            return response()->json([
                "success"=> false,
                "message"=>"La transacción no ha sido exitosa."
            ],200);
        }

    }
}
