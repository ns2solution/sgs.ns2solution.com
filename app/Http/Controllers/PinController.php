<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Validator;
use Exception;
use DB;

use App\User;

class PinController extends Controller
{
    public function Create(Request $request)
    {
    	$rules = [
            'pin'   => 'required|min:4|max:4'
        ];

        try{

        	$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

           	$user = User::where('email', $request->email)->first();
           	$user->pin = Hash::make($request->pin);
           	$user->save();

           	return response()->json([
           		'message' => 'Pin berhasil dibuat.',
           		'data'    => $request->all()
           	], 200);

        }catch(Exception $e){

        	return response()->json([
        		'message' => 'Terdapat kesalahan pada sistem internal.',
        		'error'   => $e->getMessage()
        	], 500);

        }
    }

    public function Check(Request $request)
    {
    	$rules = [
            'pin'   => 'required|min:4|max:4',
            // 'device_token' => 'required'
        ];

        try{

        	$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

           	$user = User::where('email', $request->email)->first();

            if(Hash::check($request->pin, $user->pin)){

                $user->device_token = $request->device_token;
                $user->save();
                
                return response()->json([
                    'message' => 'Login berhasil.',
                    'data'    => $request->all()
                ], 200);

            }else{

                return response()->json([
                    'message' => 'Pin yang Anda masukkan salah.'
                ], 401);

            }

        }catch(Exception $e){

        	return response()->json([
        		'message' => 'Terdapat kesalahan pada sistem internal.',
        		'error'   => $e->getMessage()
        	], 500);

        }
    }
}