<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\PromosiItem;
use Exception;
use Validator;
use Carbon\Carbon;


class PromosiItemController extends Controller
{
    //
    public function getRequest(Request $request)
    {
    	try{

			if($request->has('id')){
				$data = PromosiItem::where([['warehouse_id','=',$request->warehouse_id],['deleted_at','=',NULL]])->with('promosi','stock','stock.product')->first();
			}elseif($request->has('promosi_id')){
				$data = PromosiItem::where([['promosi_id','=',$request->promosi_id],['deleted_at','=',NULL]])->with('promosi','stock','stock.product')->get();
			}elseif($request->has('stock_id')){
				$data = PromosiItem::where([['stock_id','=',$request->stock_id],['deleted_at','=',NULL]])->with('promosi','stock','stock.product')->get();
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
