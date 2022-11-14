<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\UserRole;

class UserRoleController extends Controller
{
    public function get($id = null)
    {
    	try{

    		if($id){
                $user = UserRole::findOrFail($id);
            }else {
                $user = UserRole::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $user
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }
}
