<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;
use Nexos\Account;
use Nexos\Mail\AccountCreatedEmail;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function index(){
        $account = Account::find(31);

        Mail::to("lfcifuentes7@misena.edu.co")->send(new AccountCreatedEmail($account));
    }
}
