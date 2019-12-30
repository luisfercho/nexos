<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;
use Nexos\Account;
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
                ->select([
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
                ->select([
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
}
