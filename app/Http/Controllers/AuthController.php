<?php

namespace Nexos\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){
        $http = new \GuzzleHttp\Client;
        try {

            $response = $http->post(config('services.passport.login_endpoint'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('services.passport.client_id'),
                    'client_secret' => config('services.passport.client_secret'),
                    'username' => $request->username,
                    'password' => $request->password,
                ]
            ]);

            return $response->getBody();

        }catch (\GuzzleHttp\Exception\BadResponseException $e) {

            if ($e->getCode() === 400) {
                return response()->json(
                    'Solicitud invalida. Porfavor ingrese  usuario y contraseña.',
                    $e->getCode()
                );
            } else if ($e->getCode() === 401) {
                return response()->json(
                    'Usuario o contraseña incorrectos. porfavor intente nuevamente',
                    $e->getCode()
                );
            }

            return response()->json(
                'Ups.. Ha ocurrido un error en el servido. Porfavor intente mas tarde.',
                $e->getCode()
            );
        }
    }

    public function logout(){
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return response()->json(
            'Sessión cerrada correctamente.',
            200
        );
    }
}
