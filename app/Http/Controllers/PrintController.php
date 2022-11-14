<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\UserAddress;
use App\Setting;

class PrintController extends Controller
{


    private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
    }

    public function printPurchaseOrder($id)
    {

    	try {

    		$order_item         = [];
            $products           = [];
            $self_pickup_badge  = "<span class=\'badge badge-primary\'>Self Pickup</span>";
            $order =  DB::table('order')
                ->leftJoin('master_order_status     AS a', 'a.id',              '=',    'order.status')
                ->leftJoin('user_profile            AS b', 'b.user_id',         '=',    'order.user_id')
                ->leftJoin('users                   AS c', 'c.id',              '=',    'order.user_id')
                ->leftJoin('shipment_types          AS d', 'd.order_id',        '=',    'order.id')
                ->select(
                    'order.*', 
                    'b.phone AS user_profile_phone', 
                    'c.fullname AS user_fullname',
                    'a.status_name AS status_po',
                    // DB::raw("
                    //     IF(e.subdistrict_id, CONCAT(e.province,', ',e.type,'.',e.city,', ',e.subdistrict_name), ' ') AS user_profile_address"
                    // ),
                    'd.user_address_id',
                    'b.address',
                    'b.subdistrict_id'
                )
                ->where('order.deleted_at', null)
                ->where('order.id', $id)
                ->first();

                $order_item = DB::table('order_item AS a')
                ->leftJoin('product AS b', function ($join) {
                    $join->on('a.product_id', '=', 'b.id');
                })
                ->leftJoin('product_image AS c', function ($join) {
                    $join->on('b.id', '=', 'c.id_product')->on('c.id', '=', DB::raw("(SELECT min(id) FROM product_image WHERE id_product = b.id)"));
                })
                ->where('a.order_id', $id)
				->where('a.deleted_at', NULL)
				->select('a.*', 'b.prod_name', 'b.prod_number', 'b.prod_base_price', 'b.prod_gram', 'c.path')
                ->get();


                if($order->user_address_id) {
                     
                    $user_address   = UserAddress::find($order->user_address_id);
                    $sub    = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $user_address->subdistrict_id)->get()[0];
                    
                    $order->user_fullname       = $user_address->receiver_name;
                    $order->user_profile_phone  = $user_address->receiver_phone; 

                    $order->address = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$user_address->address;

                } else if($order->user_address_id !== 0 && $order->user_address_id < 1){
                    
                    $order->user_fullname       = $order->user_fullname;
                    $order->user_profile_phone  = $order->user_profile_phone; 
                    $order->address = $self_pickup_badge;
                
                } else {
                
                    $order->user_fullname       = $order->user_fullname;
                    $order->user_profile_phone  = $order->user_profile_phone; 
                    $sub                        = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $order->subdistrict_id)->get()[0];
                    $order->address             = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$order->address;
                }

            $data = [
                'order_item' => $order_item,
                'order' => $order
            ];

            // return response()->json([
            //     'message' => 'Data berhasil diambil.',
            //     'data'    => $data
            // ], 200);

            return view('print.purchase_order', $data);

    	} catch(Exception $e) {

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

        }
        
    }

    public function printInvoice($id)
    {

    	try {

    		$order_item         = [];
            $products           = [];
            $self_pickup_badge  = "<span class=\'badge badge-primary\'>Self Pickup</span>";
            $order =  DB::table('order')
                ->leftJoin('master_order_status     AS a', 'a.id',              '=',    'order.status')
                ->leftJoin('user_profile            AS b', 'b.user_id',         '=',    'order.user_id')
                ->leftJoin('users                   AS c', 'c.id',              '=',    'order.user_id')
                ->leftJoin('shipment_types          AS d', 'd.order_id',        '=',    'order.id')
                ->select(
                    'order.*', 
                    'b.phone AS user_profile_phone', 
                    'c.fullname AS user_fullname',
                    'a.status_name AS status_po',
                    // DB::raw("
                    //     IF(e.subdistrict_id, CONCAT(e.province,', ',e.type,'.',e.city,', ',e.subdistrict_name), ' ') AS user_profile_address"
                    // ),
                    'd.user_address_id',
                    'b.address',
                    'b.subdistrict_id'
                )
                ->where('order.deleted_at', null)
                ->where('order.id', $id)
                ->first();

                $order_item = DB::table('order_item AS a')
                ->leftJoin('product AS b', function ($join) {
                    $join->on('a.product_id', '=', 'b.id');
                })
                ->leftJoin('product_image AS c', function ($join) {
                    $join->on('b.id', '=', 'c.id_product')->on('c.id', '=', DB::raw("(SELECT min(id) FROM product_image WHERE id_product = b.id)"));
                })
                ->where('a.order_id', $id)
				->where('a.deleted_at', NULL)
				->select('a.*', 'b.prod_name', 'b.prod_number', 'b.prod_gram', 'c.path')
                ->get();



                foreach ($order_item as $key => &$a) {

                    if($order->payment_type == 'warpay') {

                        $warpay_convertion = floor( (int)$a->price / $this->__convertionWarpay());

                        $a->price = $warpay_convertion;
                        $a->total_price = (int)$a->total_item * $warpay_convertion;
                        $order->total_price = (int)$a->total_item * $warpay_convertion;
                        $order->total_ongkir  = (int) $order->total_ongkir /  $this->__convertionWarpay();
                        $order->final_total  = $order->total_ongkir + $a->total_price;

                    } else if($order->payment_type == 'point') {

                        $a->price = (int)$order->final_total;
                        $a->total_price = (int)$order->final_total;
                        $order->total_ongkir  = 0;

                    } else {

                        $a->price =  'Rp. ' . number_format($a->price);
                        $a->total_price =  'Rp. ' . number_format($a->total_price);
                        $order->total_price =  'Rp. ' . number_format($order->total_price);
                        $order->total_ongkir =  'Rp. ' . number_format($order->total_ongkir);
                        $order->final_total =  'Rp. ' . number_format($order->final_total);
                    }


                    $a->total_weight = (((int)$a->total_item * (int)$a->prod_gram) / 1000) . ' kg';
                }



                if($order->user_address_id) {
                     
                    $user_address   = UserAddress::find($order->user_address_id);
                    $sub    = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $user_address->subdistrict_id)->get()[0];
                    
                    $order->user_fullname       = $user_address->receiver_name;
                    $order->user_profile_phone  = $user_address->receiver_phone; 

                    $order->address = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$user_address->address;

                } else if($order->user_address_id !== 0 && $order->user_address_id < 1){
                    
                    $order->user_fullname       = $order->user_fullname;
                    $order->user_profile_phone  = $order->user_profile_phone; 
                    $order->address = $self_pickup_badge;
                
                } else {
                
                    $order->user_fullname       = $order->user_fullname;
                    $order->user_profile_phone  = $order->user_profile_phone; 
                    $sub                        = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $order->subdistrict_id)->get()[0];
                    $order->address             = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$order->address;
                }


            $order->no_invoice = str_replace('PO-SGS', 'INV', $order->no_po);

                // return $order_item;

            $data = [
                'order_item' => $order_item,
                'order' => $order
            ];

            return view('print.invoice', $data);

    	} catch(Exception $e) {

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

        }
        
    }

}
