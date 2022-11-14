<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

use App\SelfPickedUp;
use App\User;
use App\Cart;
use App\ShipmentType;

class SelfPickedUpController extends Controller
{
    public function update(Request $request) {

        DB::beginTransaction();

        try{

            $user = User::where('email', $request->email)
            ->first();

            $cart = Cart::where('user_id', $user->id)
                    ->select('id', 'user_id')
                    ->first();

            $param = [
                'warehouse_id' => $request->warehouse_id,
                'cart_id'   => $cart->id
            ];

            $data = SelfPickedUp::updateOrCreate($param, [
                'status_pick'        => $request->status_pick,
            ]);

            ShipmentType::where($param)->forceDelete();

            DB::commit();

            return response()->json([
                'message'       => 'Status Pick berhasil diupdate.',
                'data' 	        => $data,
            ], 200);


        } catch(Exception $e){

            DB::rollback();

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
