<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Validator;
use Exception;

use App\Cart;
use App\CartItem;
use App\Product;
use App\User;
use App\Setting;
use App\ShipmentType;
use App\SelfPickedUp;
use App\Warehouse;
use App\Promosi;
use App\PromosiItem;
use App\PromosiType;
use App\PromosiInfoBundle;

class CartNewController extends Controller
{
    private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
	}

    private function __getTimeNow()
    {
        return Carbon::now('Asia/Jakarta');
    }
	
    public function get(Request $request)
    {
    	try{

    		$user = User::where('email', $request->email)
    					->first();

            $user_warpay = $user->profile->warpay;

    		$cart = Cart::where('user_id', $user->id)
    					->select('id', 'user_id')
    					->first();

    		if(!$cart){
    			return response()->json([
    				'message' => "Cart kosong.",
    				'data'    => '0'
    			], 404);
    		}

    		$item = CartItem::where('cart_id', $cart->id);

            $GRAND_TOTAL_WARPAY  = 0;

            // foreach($item->get() as $index => $a) {

			// 	$warpay_convertion = floor( (int)$a->product->prod_base_price / $this->__convertionWarpay());
            //     $total_warpay = (int)$a->total_item * $warpay_convertion;

            //     $GRAND_TOTAL_WARPAY += (int)$total_warpay;

            // }

            if(!$item->first()){
    			return response()->json([
    				'message' => "Cart kosong.",
    				'data'    => '0'
    			], 404);
    		}

    		$cart_product = CartItem::leftJoin('cart AS A', 'A.id', '=', 'cart_item.cart_id')
    		->leftJoin('warehouse  	  AS B', 'B.id', 		 '=', 'cart_item.warehouse_id')
    		->leftJoin('product    	  AS C', 'C.id', 		 '=', 'cart_item.product_id')
    		->leftJoin('principles 	  AS D', 'D.id', 		 '=', 'C.principle_id')
    		->leftJoin('brands     	  AS E', 'E.id_brand', 	 '=', 'C.brand_id')
    		->leftJoin('category      AS G', 'G.id',         '=', 'C.sub_category_id')
    		->leftJoin('category  	  AS F', 'F.id', 		 '=', 'G.parent_id')
    		->leftJoin('product_image', function ($join) {
    			$join->on('C.id', '=', 'product_image.id_product')
    				 ->on(
    					'product_image.id',
    					'=',
    					DB::raw("(select min(`id`) from product_image where C.id = product_image.id_product)")
    				 );
    		})
            ->leftJoin('master_stock', function ($join) use ($request) {
                $join->on('master_stock.warehouse_id', '=', 'cart_item.warehouse_id');
                $join->on('master_stock.product_id',   '=', 'cart_item.product_id');
            })
            ->leftJoin('settings', function ($join) {
                $join->on('settings.id', '=', DB::raw("'" . 1 . "'"));
            })
    		->select(
    			'cart_item.id', 'cart_item.total_item', 'cart_item.promosi_id',
    			'A.id AS cart_id', 'A.user_id',
    			'B.id AS wh_id', 'B.short AS wh_short', 'B.name AS wh_name', 'B.code AS wh_code',
    			'C.id AS prod_id', 'C.prod_number', 'C.prod_name', 'C.prod_base_price', 'C.prod_gram',
                DB::raw('IFNULL(product_image.path, "assets/product_image/_blank.jpg") AS prod_image'),
    			'C.principle_id', 'C.prod_type_id', 'D.name AS principle_name',
    			'C.brand_id', 'E.brand_name',
    			'F.id AS category_id', 'F.category_name',
    			'C.sub_category_id', 'G.category_name AS sub_category_name',
                DB::raw('FLOOR(C.prod_base_price / settings.convertion_warpay) as prod_warpay'),
                DB::raw('IFNULL( master_stock.stock, 0) as stock')
    		)
			->where('A.user_id', $user->id)
			->where('master_stock.deleted_at', NULL)
			->get();

    		$info = [
    			'final_price'      => 0,
    			'final_price_ori'  => 0,
    			'final_total_item' => 0,
    			'final_ongkir'	   => 0,
    			'final_ongkir_ori' => 0,
                'total_weight'     => 0,
                'total_item'       => 0
    		];

            $GROUP_WH = [];

    		foreach($cart_product as $index => $a){

				$info['total_item']++;

                if($a->promosi_id !== null){

                    /*
                        =============================================================================
                        ------------------------------- PRODUCT PROMO -------------------------------
                        =============================================================================
                    */

                    $now = strtotime($this->__getTimeNow());

                    $CHECK_PROMOSI = Promosi::leftJoin('promosi_item AS A', 'A.promosi_id', '=', 'promosi.id')
                    ->leftJoin('master_stock AS B', 'B.id', '=', 'A.stock_id')
                    ->leftJoin('product      AS C', 'C.id', '=', 'B.product_id')
                    ->leftJoin('promosi_type AS D', 'D.id', '=', 'promosi.promosi_type')
                    ->select(
                        'promosi.id', 'promosi.promosi_type', 'promosi.info_bundle_id', 'promosi.start_date', 'promosi.end_date',
                        'promosi.promosi_name', 'promosi.promosi_image',
                        'promosi.total_bundle AS stock_bundle', 'promosi.total_value',
                        'A.id AS promosi_item_id', 'A.stock_promosi AS stock', 'A.fix_value',
                        'D.promosi_type AS promosi_info'
                    )
                    ->where('promosi.id', $a->promosi_id)
                    ->where('B.product_id', $a->prod_id)
                    ->where('B.warehouse_id', $a->wh_id)
                    ->whereNull('A.deleted_at')
                    ->whereNull('B.deleted_at')
                    ->whereNull('C.deleted_at')
                    ->first();

                    if(!$CHECK_PROMOSI){

                        /* NOT FOUND */
                        CartItem::where('id', $a->id)->delete();
                        unset($cart_product[$index]);
                        continue;

                    }else{

                        switch($CHECK_PROMOSI->promosi_type){

                            case 1: // Bundle

                                $__prod_gram = 0;

                                $bundle = PromosiItem::leftJoin('master_stock AS A', 'A.id', '=', 'promosi_item.stock_id')
                                ->leftJoin('product      AS B', 'B.id', '=', 'A.product_id')
                                ->leftJoin('product_type AS C', 'C.id', '=', 'B.prod_type_id')
                                ->select(
                                    'promosi_item.*',
                                    'B.prod_gram',
                                    'C.product_type'
                                )
								->where('promosi_item.promosi_id', $CHECK_PROMOSI->id)
								->where('A.deleted_at', NULL)
                                ->get();

                                foreach($bundle as $b){
                                    $__prod_gram += (int)$b->prod_gram * (int)$b->stock_promosi;
                                }

                                $a['prod_name']     = $CHECK_PROMOSI->promosi_name;
                                $a['prod_gram']     = $__prod_gram;
                                $a['prod_image']    = $CHECK_PROMOSI->promosi_image;
                                $a['category_name'] = $bundle[0]->product_type;

                                $__arr = [
                                    'prod_id', 'prod_number', 'principle_id', 'principle_name',
                                    'brand_id', 'brand_name', 'category_id', 'sub_category_id',
                                    'sub_category_name'
                                ];

                                foreach($__arr as $b){
                                    $a[$b] = null;
                                }

                                $a['stock'] = $CHECK_PROMOSI->stock_bundle;

                                $a['promosi'] = [
                                    'promosi_info' => PromosiInfoBundle::find($CHECK_PROMOSI->info_bundle_id)->info_bundle
                                ];

                                $a['prod_base_price'] = (int)$CHECK_PROMOSI->total_value;
                                $a['prod_warpay']     = floor((int)$CHECK_PROMOSI->total_value / $this->__convertionWarpay());

                                break;

                            case 2: // Diskon

                                $a['stock'] = $CHECK_PROMOSI->stock;

                                $a['promosi'] = [
                                    'promosi_info'             => $CHECK_PROMOSI->promosi_info,
                                    'prod_base_price_before'   => _numberFormat((int)$a->prod_base_price),
                                    'prod_warpay_before'       => (int)$a->prod_warpay,
                                    'total_price_before'       => _numberFormat((int)$a->total_item * (int)$a->prod_base_price),
                                    'total_prod_warpay_before' => (int)$a->total_item * (int)$a->prod_warpay
                                ];

                                $a['prod_base_price'] = (int)$CHECK_PROMOSI->fix_value;
                                $a['prod_warpay']     = floor((int)$CHECK_PROMOSI->fix_value / $this->__convertionWarpay());

							break;

                        }

                        if((strtotime($CHECK_PROMOSI->start_date) <= $now) && (strtotime($CHECK_PROMOSI->end_date) >= $now)){}else{

                            /* TIME OUT */
                            $a['stock'] = 0;

                        }

                    }

                }

				$total_price        = (int)$a->total_item * (int)$a->prod_base_price;
				$total_prod_warpay	= (int)$a->total_item * (int)$a->prod_warpay;

				$a['total_price']       = (string)$total_price;
				// $a['total_price']       = _numberFormat($total_price);
				$a['total_price_ori']   = $total_price;
				$a['total_prod_warpay'] = $total_prod_warpay;

    			$info['final_price_ori']  += $total_price;
    			$info['final_total_item'] += (int)$a->total_item;
                $info['total_weight']     += (int)$a->total_item * (int)$a->prod_gram;

                if(!array_key_exists($a->wh_id, $GROUP_WH)){

					$param_where = [
						'cart_id'      => $cart->id,
						'warehouse_id' => $a->wh_id
					];

					$CHECK_SHIPMENT    = ShipmentType::where($param_where)->first();
					$CHECK_SELF_PICKUP = SelfPickedUp::where($param_where)->first();

                    if($CHECK_SHIPMENT) {

						if($CHECK_SELF_PICKUP) {

							if($CHECK_SELF_PICKUP->status_pick == 1) {
	
								// $info['final_price_ori']	+= 0;
								// $info['final_ongkir_ori'] = 0;
	
							} else {

								$info['final_ongkir_ori'] += (int)$CHECK_SHIPMENT->courier_ongkir;
								$info['final_price_ori']  += (int)$CHECK_SHIPMENT->courier_ongkir;
							
							}
	
						} else {
							$info['final_ongkir_ori'] += (int)$CHECK_SHIPMENT->courier_ongkir;
                        	$info['final_price_ori']  += (int)$CHECK_SHIPMENT->courier_ongkir;
						}
					}

                    $GROUP_WH[$a->wh_id] = '1';

                }

    		}


			foreach($cart_product as $index => $a) {

				$warpay_convertion = floor( (int)$a->prod_base_price / $this->__convertionWarpay());
                $total_warpay = (int)$a->total_item * $warpay_convertion;

                $GRAND_TOTAL_WARPAY += (int)$total_warpay;

            }


    		$info['final_price']         = _numberFormat($info['final_price_ori']);
    		$info['final_ongkir']        = _numberFormat($info['final_ongkir_ori']);
            $info['final_ongkir_warpay'] = _numberFormat(floor((int)$info['final_ongkir_ori'] / $this->__convertionWarpay()));

			// $info['total_weight_kg'] = floor((int)$info['total_weight'] / 1000) . ' kg';
			$info['total_weight_kg'] = ($info['total_weight'] / 1000) . ' kg';

            if(isset($request->group) && $request->group != ''){

                if($request->group == 1) {

                    $group = [];
                    $temp  = [];

                    foreach($cart_product as $index => $a){

                        if(!in_array($a->wh_name, $temp)){

                            array_push($temp, $a->wh_name);

							$param_where = [
								'cart_id'      => $a->cart_id,
								'warehouse_id' => $a->wh_id
							];
		
							$status_pick = SelfPickedUp::where($param_where)->first();

							$is_pick = 0;

							if($status_pick) {
								if($status_pick->status_pick == 1) {
									$is_pick = 1;
								}
							}

                            array_push($group, [
                                'id'          => $a->wh_id,
								'warehouse'   => $a->wh_name,
								'status_pick' => $is_pick,
                                'data'        => [$a]
                            ]);

                        }else{

                            $ix = array_search($a->wh_name, $temp);

                            array_push($group[$ix]['data'], $a);

                        }

                    }

                    $cart_product = $group;

                }

            }

            // $info['grand_total_warpay_product'] = floor((int)$info['final_price_ori'] / $this->__convertionWarpay());
           	$info['grand_total_warpay_product'] = $GRAND_TOTAL_WARPAY + (int)$info['final_ongkir_warpay'];
			$info['warpay_user'] = $user_warpay;

            $info['subtotal_ori'] = $info['final_price_ori'] - $info['final_ongkir_ori'];
            $info['subtotal']     = _numberFormat($info['subtotal_ori']);
            // $info['subtotal_wp']  = (string)floor((int)$info['subtotal_ori'] / $this->__convertionWarpay());
            $info['subtotal_wp'] = (string)$GRAND_TOTAL_WARPAY;

			return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => [
                	'product' => $cart_product,
                	'info'	  => $info
                ]
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function add(Request $request)
    {
    	$rules = [
            'warehouse_id' => 'required|regex:/^\S*$/u',
            'product_id'   => 'required|regex:/^\S*$/u',
            'total_item'   => 'required|regex:/^\S*$/u'
        ];

    	try{

    		$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $user = User::where('email', $request->email)
            			->first();

            $cart = Cart::where('user_id', $user->id)
            			->first();

            if(!$cart){

            	$cart = Cart::create([
            		'user_id' => $user->id
            	]);

            }

            $data = [
            	'cart_id' 	   => $cart->id,
            	'warehouse_id' => $request->warehouse_id,
            	'product_id'   => $request->product_id
            ];

            if(isset($request->promosi_id)){
                $data['promosi_id'] = $request->promosi_id;
            }

            $item = CartItem::where($data)->first();

    		if(!$item){

    			$data['total_item'] = $request->total_item;

    			$item = CartItem::create($data);

    		}else{

    			CartItem::where($data)->update([
    				'total_item' => $request->total_item
    			]);

    			$item = CartItem::where($data)->first();

    		}

            return response()->json([
            	'message' => 'Produk ditambahkan ke keranjang.',
            	'data'    => $item
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function update(Request $request)
    {
    	try{

    		if(!$request['id']){
    			return response()->json([
                    'message' => 'Masukkan id cart item.'
                ], 400);
    		}

    		$item = DB::table('cart_item')
    					->where('id', $request->id)
    					->whereNull('deleted_at')
    					->update([
    						'total_item' => $request->total_item
    					]);

    		if($item){

    			return response()->json([
    				'message' => 'Data item cart berhasil diperbarui.'
    			], 200);

    		}else{

    			return response()->json([
                    'message' => 'ID cart item tidak ditemukan.'
                ], 400);

    		}

		}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function deleteItem(Request $request)
    {
    	$rules = [
            'id' => 'required|regex:/^\S*$/u'
        ];

    	try{

    		$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $item = CartItem::find($request->id);

    		if(!$item){
    			return response()->json([
    				'message' => "Item cart tidak ditemukan.",
    				'data'    => ['id' => $request->id]
    			], 404);
    		}

    		$item->delete();

    		return response()->json([
    			'message' => 'Produk berhasil dihapus dari keranjang.',
    			'data'    => ['id' => $request->id]
    		], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function deleteAll(Request $request)
    {
    	try{

    		$user = User::where('email', $request->email)->first();
    		$cart = Cart::where('user_id', $user->id)->first();

    		if(!$cart){
    			return response()->json([
    				'message' => "Cart tidak ditemukan."
    			], 404);
    		}

    		$item = CartItem::where('cart_id', $cart->id)->first();

    		if(!$item){
    			return response()->json([
    				'message' => "Cart tidak ditemukan."
    			], 404);
    		}

    		CartItem::where('cart_id', $cart->id)->delete();

    		return response()->json([
    			'message' => 'Semua produk berhasil dihapus dari keranjang.'
    		], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }
}
