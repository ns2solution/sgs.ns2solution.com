<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class SubdistrictController extends Controller
{
    public function get($city_id = null)
    {
    	try{
			if($city_id){
				$subdistrict = DB::table('rajaongkir_subdistrict')
				->where('city_id', $city_id)
				->get();
			} else {
				$subdistrict = DB::table('rajaongkir_subdistrict')
				->get();
			}

    		if(count($subdistrict) == 0){
    			return response()->json([
                    'message' => "Subdistrict tidak ditemukan."
                ], 404);
			}

    		return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $subdistrict
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }
}
