<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;

class SpaController extends Controller
{
    public function index(){
        return view('app');
    }
}
