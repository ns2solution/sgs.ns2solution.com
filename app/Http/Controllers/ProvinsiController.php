<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MasterProvinsi;

class ProvinsiController extends Controller
{
    public function get($id = null)
    {
    	try{

    		if($id){
                $provinsi = MasterProvinsi::findOrFail($id);
            }else {
                $provinsi = MasterProvinsi::select('province_id', 'province')->get();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $provinsi
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }
}
