<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MenuNav;

class MenuNavController extends Controller
{
    //
    public function getRequest(Request $request)
    {
        # code...
        try{

    		if($request->has('link')){
                $data = MenuNav::where('link',$request->link)->first();
            }else {
                $data = MenuNav::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $data
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }
}
