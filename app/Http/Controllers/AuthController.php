<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function Login()
    {
    	if(Session::get('email') && Session::get('token')){

        	return redirect()->route('shoppingreport');

    	}else{

    		return view('auth/login');

    	}
    }

    public function SaveToken($token)
    {
    	$token = explode('||', $token);
		
    	if(empty($token[1])){

            return $this->Logout()->with(['error' => 'Invalid token.']);

        }

        Session::put('email', $token[0]);
        Session::put('token', $token[1]);

    	$url = env('API_URL') . '/get-data/' . $token[0] . '/' . $token[1];
    	//$url =  'http://sgs.api.local/get-data/' . $token[0] . '/' . $token[1];
		//dd($url);
    	$option = [
    		"ssl" => [
    			"verify_peer"      => false,
    			"verify_peer_name" => false,
    		],
    	];

    	$data = @file_get_contents($url, false, stream_context_create($option));
    	$data = json_decode($data);
		//dd($data);
    	if($data == null){

    		return $this->Logout()->with(['error' => 'Invalid token.']);

    	}else{

    		Session::put('user', $data->user);
    		Session::put('profile', $data->profile);
            Session::put('role', $data->role);
            Session::put('warehouse', $data->warehouse);
            Session::put('warehouse_id', $data->user->wh_id);

    		return redirect()->route('shoppingreport');

    	}
    }

    public function Logout()
    {
    	Session::flush();

    	return redirect()->route('login');
    }
}
