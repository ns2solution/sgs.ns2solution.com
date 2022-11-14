<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Exception;
use Validator;
use Illuminate\Support\Facades\DB;

use App\Courier;
use App\CourierSetting;
use App\Warehouse;
use App\Cart;
use App\CartItem;
use App\Order;
use App\OrderItem;
use App\User;
use App\UserAddress;
use App\ShipmentType;
use App\PromosiItem;
use App\CourierService;
use App\CourierServiceSetting;
use App\Setting;

class RajaOngkirController extends Controller
{
	public function cost(Request $request, $returnJSON = true)
	{
		$rules = [
            'warehouse_id' => 'required|regex:/^\S*$/u',
            'address_id'   => 'required|regex:/^\S*$/u'
		];
		
		DB::beginTransaction();

		try{

			$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
            	if($returnJSON === true){
            		return response()->json([
            			'message' => ucfirst($validator->errors()->first())
            		], 400);
            	}else{
            		return [
            			'message' => ucfirst($validator->errors()->first()),
            			'data'    => '0'
            		];
            	}
            }

            $wh = Warehouse::find($request->warehouse_id);

            if(!$wh){
            	if($returnJSON === true){
            		return response()->json([
            			'message' => "Warehouse tidak ditemukan."
            		], 404);
            	}else{
            		return [
            			'message' => "Warehouse tidak ditemukan.",
            			'data'    => '0'
            		];
            	}
            }

            $user = User::where('email', $request->email)
    					->first();

    		$cart = Cart::where('user_id', $user->id)
    					->select('id', 'user_id')
    					->first();

    		if(!$cart){
    			if($returnJSON === true){
    				return response()->json([
    					'message' => "Cart kosong.",
    					'data'    => '0'
    				], 404);
    			}else{
    				return [
    					'message' => "Cart kosong.",
    					'data'    => '0'
    				];
    			}
    		}

    		$item = CartItem::where([
    			'cart_id'      => $cart->id,
    			'warehouse_id' => $request->warehouse_id
    		])->get();

    		if(count($item) == 0){
    			if($returnJSON === true){
    				return response()->json([
    					'message' => "Cart kosong.",
    					'data'    => '0'
    				], 404);
    			}else{
    				return [
    					'message' => "Cart kosong.",
    					'data'    => '0'
    				];
    			}
    		}

            $CHECK_ADDRESS = UserAddress::where([
                'id'      => $request->address_id,
                'user_id' => $user->id
            ])->first();

            if(!$CHECK_ADDRESS && $request->address_id != '0'){
                if($returnJSON === true){
                    return response()->json([
                        'message' => "Alamat tidak ditemukan."
                    ], 404);
                }else{
                    return [
                        'message' => "Alamat tidak ditemukan.",
                        'data'    => '0'
                    ];
                }
            }

            $ADDRESS = '';

            if($request->address_id == '0'){
                $ADDRESS = $user->profile->subdistrict_id;
            }else{
                $ADDRESS = $CHECK_ADDRESS->subdistrict_id;
            }

    		$DATA_COURIER  = [];
    		$PARAM_COURIER = '';
			$GET_COURIER   = Courier::all();

			foreach($GET_COURIER as $index => $a){

    			$GET_SETTING = CourierSetting::where([
    				'warehouse_id' => $request->warehouse_id,
    				'courier_id'   => $a->id
				])->first();
				

    			if($GET_SETTING){


    				if($GET_SETTING->value == 'on'){

						$GET_COURIER_SERVICE = CourierService::where('courier_id', $a->id)->get();

						$tmp2 = [];

						foreach($GET_COURIER_SERVICE as $index => $b){

							$GET_SETTING_COURIER_SERVICE = CourierServiceSetting::where([
								'warehouse_id' => $request->warehouse_id,
								'courier_service_id'   => $b->id
							])->first();
							
							$_ = [
								'value'        => 'off',
								'courier_service_code' => $b->service_code,
								'courier_service_name' => $b->service_name
							];


							if($GET_SETTING_COURIER_SERVICE){
								$_['value'] = $GET_SETTING_COURIER_SERVICE->value;
								$_['courier_service_code'] = $b->service_code;
								$_['courier_service_name'] = $b->service_name;
							}

							$tmp2[] = $_;


						}

											
						if($PARAM_COURIER != ''){
							$PARAM_COURIER .= ':' . $a->code;
						}else{
							$PARAM_COURIER = $a->code;
						}

						$temp = [
							'id'   => $a->id,
							'code' => $a->code,
							'name' => $a->name,
							'logo' => url() . '/' . $a->logo,
							'service' => $tmp2
						];

						array_push($DATA_COURIER, $temp);

					}

				}
			}
			


    		$TOTAL_WEIGHT = 0;

    		foreach($item as $a){

                $__prod_gram = 0;
                $PROD_GRAM   = (int)$a->product->prod_gram;

                if($a->promosi_id !== null){

                    $bundle = PromosiItem::leftJoin('master_stock AS A', 'A.id', '=', 'promosi_item.stock_id')
                    ->leftJoin('product      AS B', 'B.id', '=', 'A.product_id')
                    ->leftJoin('product_type AS C', 'C.id', '=', 'B.prod_type_id')
                    ->leftJoin('promosi      AS D', 'D.id', '=', 'promosi_item.promosi_id')
                    ->select(
                        'D.promosi_type',
                        'promosi_item.*',
                        'B.prod_gram',
                        'C.product_type'
                    )
                    ->where('promosi_item.promosi_id', $a->promosi_id)
                    ->get();

                    if($bundle->first()->promosi_type === 1){

                        foreach($bundle as $b){
                            $__prod_gram += (int)$b->prod_gram * (int)$b->stock_promosi;
                        }

                        $PROD_GRAM = $__prod_gram;

                    }

                }

    			$WEIGHT        = $PROD_GRAM * (int)$a->total_item;
    			$TOTAL_WEIGHT += (int)$WEIGHT;

    		}

    		$param = [
    			'origin'	 	  => $wh->subdistrict_id,
    			'originType'  	  => 'subdistrict',
    			'destination'     => $ADDRESS,
    			'destinationType' => 'subdistrict',
    			'weight'		  => $TOTAL_WEIGHT,
    			'courier'    	  => $PARAM_COURIER
    		];

    		$api = Http::withHeaders([

    			'key' => _con('RAJAONGKIR-KEY')

    		])->post(_con('RAJAONGKIR-URL') . 'api/cost', $param);

    		$data = [];

			foreach($api['rajaongkir']['results'] as $index => $a){

					if($DATA_COURIER[$index]['code'] === 'jnt') {
						$DATA_COURIER[$index]['code'] = 'J&T';
					}

					if($DATA_COURIER[$index]['code'] === $a['code']) {


						foreach($a['costs'] as $b){

							CourierService::firstOrCreate([
								'courier_id'   => $DATA_COURIER[$index]['id'],
								'service_code' => $b['service'],
								'service_name' => $b['description']
							]);

							foreach ($DATA_COURIER[$index]['service'] as $key => $c) {
						

								if(($b['service'] == $c['courier_service_code']) && $c['value'] == 'on') {

									$temp = [
										'id'        => $DATA_COURIER[$index]['id'],
										'code'      => $DATA_COURIER[$index]['code'],
										'logo'      => $DATA_COURIER[$index]['logo'],
										'name' 	    => $DATA_COURIER[$index]['name'],
										'service'   => $b['service'],
										'desc'      => $b['description'],
										'price'     => 'Rp ' . _numberFormat($b['cost'][0]['value']),
										'price_ori' => $b['cost'][0]['value']
									];
		
									if($b['cost'][0]['etd'] != ''){
										$temp['desc'] .= ' (' . $b['cost'][0]['etd'] . ' hari)';
									}
		
									array_push($data, $temp);

								}

							}
						

						}

					}

			}

    		$location = [
    			'origin'      => $api['rajaongkir']['origin_details'],
    			'destination' => $api['rajaongkir']['destination_details']
    		];

    		$resp = [
    			'message'  => 'Data berhasil diambil.',
    			'param'    => $param,
    			'info'     => [
    				'cart_id' => $cart->id
    			],
    			'location' => $location,
    			'data'	   => $data
			];
			
			DB::commit();

    		if($returnJSON === true){

    			return response()->json($resp, 200);

    		}else{

    			return $resp;

			}
			

		}catch(Exception $e){

			DB::rollback();

			if($returnJSON === true){
				return response()->json([
					'message' => 'Terdapat kesalahan pada sistem internal.',
					'error'   => $e->getMessage()
				], 500);
			}else{
				return [
					'message' => 'Terdapat kesalahan pada sistem internal.',
					'error'   => $e->getMessage(),
					'data'    => '0'
				];
			}

		}
	}

	public function update(Request $request)
	{
		DB::beginTransaction();

		$rules = [
            'warehouse_id'    => 'required|regex:/^\S*$/u',
            'courier_id'      => 'required|regex:/^\S*$/u',
            'courier_service' => 'required|max:100',
            'courier_desc'    => 'required|max:100',
            'courier_ongkir'  => 'required|regex:/^\S*$/u',
            'address_id'      => 'required|regex:/^\S*$/u'
        ];

		try{

			$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $wh = Warehouse::find($request->warehouse_id);

            if(!$wh){
                return response()->json([
                    'message' => "Warehouse tidak ditemukan."
                ], 404);
            }

            $user = User::where('email', $request->email)
    					->first();

    		$cart = Cart::where('user_id', $user->id)
    					->select('id', 'user_id')
    					->first();

    		if(!$cart){
    			return response()->json([
    				'message' => "Cart kosong.",
    				'data'    => '0'
    			], 404);
    		}

    		$item = CartItem::where([
    			'cart_id'      => $cart->id,
    			'warehouse_id' => $request->warehouse_id
    		])->get();

    		if(count($item) == 0){
    			return response()->json([
    				'message' => "Cart kosong.",
    				'data'    => '0'
    			], 404);
    		}

            $CHECK_ADDRESS = UserAddress::where([
                'id'      => $request->address_id,
                'user_id' => $user->id
            ])->first();

            if(!$CHECK_ADDRESS && $request->address_id != '0'){
                return response()->json([
                    'message' => "Alamat tidak ditemukan."
                ], 404);
            }

            $ADDRESS         = '';
            $USER_ADDRESS_ID = '';

            if($request->address_id == '0'){

                $ADDRESS         = $user->profile->subdistrict_id;
                $USER_ADDRESS_ID = '0';

            }else{

                $ADDRESS         = $CHECK_ADDRESS->subdistrict_id;
                $USER_ADDRESS_ID = $CHECK_ADDRESS->id;

            }

    		$TOTAL_WEIGHT = 0;

            foreach($item as $a){

                $__prod_gram = 0;
                $PROD_GRAM   = (int)$a->product->prod_gram;

                if($a->promosi_id !== null){

                    $bundle = PromosiItem::leftJoin('master_stock AS A', 'A.id', '=', 'promosi_item.stock_id')
                    ->leftJoin('product      AS B', 'B.id', '=', 'A.product_id')
                    ->leftJoin('product_type AS C', 'C.id', '=', 'B.prod_type_id')
                    ->leftJoin('promosi      AS D', 'D.id', '=', 'promosi_item.promosi_id')
                    ->select(
                        'D.promosi_type',
                        'promosi_item.*',
                        'B.prod_gram',
                        'C.product_type'
                    )
                    ->where('promosi_item.promosi_id', $a->promosi_id)
                    ->get();

                    if($bundle->first()->promosi_type === 1){

                        foreach($bundle as $b){
                            $__prod_gram += (int)$b->prod_gram * (int)$b->stock_promosi;
                        }

                        $PROD_GRAM = $__prod_gram;

                    }

                }

                $WEIGHT        = $PROD_GRAM * (int)$a->total_item;
                $TOTAL_WEIGHT += (int)$WEIGHT;

            }

    		$PAR_1 = [
    			'cart_id'         => $cart->id,
    			'warehouse_id'    => $request->warehouse_id
    		];

    		$PAR_2 = [
    			'courier_id'      => $request->courier_id,
    			'courier_service' => $request->courier_service,
    			'courier_desc'    => $request->courier_desc,
    			'courier_ongkir'  => $request->courier_ongkir,
    			'weight'          => $TOTAL_WEIGHT,
    			'origin_id'		  => $wh->subdistrict_id,
    			'destination_id'  => $ADDRESS,
                'user_address_id' => $USER_ADDRESS_ID
    		];

    		ShipmentType::updateOrCreate($PAR_1, $PAR_2);

    		$data = array_merge($PAR_1, $PAR_2);

    		DB::commit();

    		return response()->json([
                'message' => 'Pengiriman berhasil disimpan.',
                'data'	  => $data
            ], 200);

		}catch(Exception $e){

			DB::rollback();

			return response()->json([
				'message' => 'Terdapat kesalahan pada sistem internal.',
				'error'   => $e->getMessage()
			], 500);

		}
	}

	public function get(Request $request)
	{
		try{

			$GET_SERVICE = $this->cost($request, false);

			if($GET_SERVICE['data'] == '0'){
				return response()->json($GET_SERVICE, 500);
			}

            $CART_ID = $GET_SERVICE['info']['cart_id'];

			$CHECK_SHIPMENT = ShipmentType::where([
				'cart_id'	   => $CART_ID,
				'warehouse_id' => $request->warehouse_id
			])->first();

            $data_2 = [
                'warehouse_id' => $request->warehouse_id,
                'status_pick'  => 0
            ];

            $status_self_picked_up = DB::table('self_picked_up_order')->where([
                'cart_id'	   => $CART_ID,
                'warehouse_id' => $request->warehouse_id
            ])->get();

            if(count($status_self_picked_up) > 0){
                if($status_self_picked_up[0]->status_pick == 1){
                    $data_2['status_pick'] = 1;
                }
            }

			if(!$CHECK_SHIPMENT){

				return response()->json([
					'line' => 'L:01',
					'data' => '0'
				], 404);

			}

			$data = null;

			foreach($GET_SERVICE['data'] as $a){

				$search = ShipmentType::where([
					'cart_id'	      => $CART_ID,
					'warehouse_id'    => $request->warehouse_id,
					'courier_id'   	  => $a['id'],
					'courier_service' => $a['service']
                ])->first();

				if($search){

					$data = $a;

					$search->courier_desc   = $a['desc'];
					$search->courier_ongkir = $a['price_ori'];
					$search->weight 		= $GET_SERVICE['param']['weight'];
					$search->origin_id      = $GET_SERVICE['param']['origin'];
					$search->destination_id = $GET_SERVICE['param']['destination'];
					$search->save();

				}

			}

			if($data === null){

				$CHECK_SHIPMENT->delete();

				return response()->json([
					'line' => 'L:02',
					'data' => '0'
				], 404);

			}

			return response()->json([
				'message' => 'Data berhasil diambil.',
                'data'    => $data,
                'data_2'  => $data_2
			], 200);

		}catch(Exception $e){

			return response()->json([
				'message' => 'Terdapat kesalahan pada sistem internal.',
				'error'   => $e->getMessage(),
				'data'    => '0'
			], 500);

		}
	}


	public function getCalcOrder(Request $request, $type)
	{

		$total_weight = $request->total_weight;

		try{

			switch ($type) {

				case 'FROM-DATABASE':
				
					$GET_SERVICE = $this->costCalcOrder($request, false);

					if($GET_SERVICE['data'] == '0'){
						return response()->json($GET_SERVICE, 500);
					}
		
					$ORDER_ID = $GET_SERVICE['info']['order_id'];
		
					$CHECK_SHIPMENT = ShipmentType::where([
						'order_id'	   => $ORDER_ID,
						'warehouse_id' => $request->warehouse_id
					])->first();
		
					$data_2 = [
						'warehouse_id' => $request->warehouse_id,
						'is_pick'  => 0
					];
		
					$status_self_picked_up = DB::table('order')->where([
						'id'	   => $ORDER_ID,
						'warehouse_id' => $request->warehouse_id
					])->get();
		
					if(count($status_self_picked_up) > 0){
						if($status_self_picked_up[0]->is_pick == 1){
							$data_2['is_pick'] = 1;
						}
					}
		
					if(!$CHECK_SHIPMENT){
		
						return response()->json([
							'line' => 'L:01',
							'data' => '0'
						], 404);
		
					}
		
					$data = null;
		
					foreach($GET_SERVICE['data'] as $a) {
		
						$search = ShipmentType::where([
							'order_id'	      => $ORDER_ID,
							'warehouse_id'    => $request->warehouse_id,
							'courier_id'   	  => $a['id'],
							'courier_service' => $a['service']
						])->first();
		
						if($search){
		
							$data = $a;
		
							$search->courier_desc   = $a['desc'];
							$search->courier_ongkir = $a['price_ori'];
							$search->weight 		= $GET_SERVICE['param']['weight'];
							$search->origin_id      = $GET_SERVICE['param']['origin'];
							$search->destination_id = $GET_SERVICE['param']['destination'];
							$search->save();
		
						}
		
					}
		
					if($data === null){
		
						$CHECK_SHIPMENT->delete();
		
						return response()->json([
							'line' => 'L:02',
							'data' => '0'
						], 404);
		
					}
		
					// Update harga ongkir di order
		
					$GRAND_TOTAL             = 0;
		
					$FINAL_TOTAL			 = 0;
		
					$ONGKIR					 = $data['price_ori'];
		
					$order_item              = OrderItem::leftJoin('promosi AS d', 'd.id', '=', 'order_item.promosi_id')
					->leftJoin('product AS g', 'g.id', '=', 'order_item.product_id')
					->leftJoin('promosi_item AS e', 'e.promosi_id', '=', 'order_item.promosi_id')
					->leftJoin('master_stock AS f', function ($join) {
							$join->on('f.id', '=', 'e.stock_id')
							->on('f.product_id', '=', 'order_item.product_id');
					})
					->where('e.deleted_at', NULL)
					->where('order_id', $ORDER_ID)
					->get();
					
					foreach($order_item as $index => $a) {
		
						$price 		            = (int)$a->prod_base_price;
						
						$total_price            = (int)$a->total_item * $price;
					
						$GRAND_TOTAL            += (int)$total_price;
					}
		
					$order					= Order::find($ORDER_ID);
					
					$order->total_price		= $GRAND_TOTAL;
					
					$order->total_ongkir 	= $ONGKIR;
					
					$order->final_total		= ( $GRAND_TOTAL + $ONGKIR );

					$order->save();
		
					$order['order_item']	= $order_item;



					$arr = ['PRODUCT_WARPAY' => 0];
					if($order->payment_type === 'warpay') {
	
						$arr['ONGKIR_WARPAY'] = (floor((int)$order->total_ongkir / $this->__convertionWarpay()));
	
						foreach($order_item as $a) {
	
							$warpay_convertion = floor( (int)$a->price / $this->__convertionWarpay());
							$total_warpay = (int)$a->total_item * $warpay_convertion;
			
							$arr['PRODUCT_WARPAY'] += (int)$total_warpay;
			
						}
	
						$arr['FINAL_TOTAL_WARPAY'] = $arr['PRODUCT_WARPAY'] + $arr['ONGKIR_WARPAY'];
	
					}
	
					foreach ($arr as $key => $value) {
						$order->$key = $value;
					}                
		

		
					$resp = [
						'shipment' 	=> $data,
						'order'		=> $order,
					];
		
					return response()->json([
						'status'  => true,
						'message' => 'Data berhasil diambil.',
						'data'    => $resp,
						'data_2'  => $data_2
					], 200);

					
				break;
				
				case 'FROM-RAJAONGKIR':

					$GET_SERVICE = $this->costCalcOrder($request, false, $total_weight);

					return response()->json([
						'data'    => $GET_SERVICE['data'][0]
					], 200);

				break;
			}


		}catch(Exception $e){

			return response()->json([
				'status'  =>false,
				'message' => 'Terdapat kesalahan pada sistem internal.',
				'error'   => $e->getMessage(),
				'data'    => '0'
			], 500);

		}
	}


	private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
    }


	// manage by sobari
	public function costCalcOrder(Request $request, $returnJSON = true, $CALC_WEIGHT_MANUAL = 0)
	{
		$rules = [
			'warehouse_id' => 'required|regex:/^\S*$/u',
			'order_id'	   => 'required|regex:/^\S*$/u',
        ];

		try{

			$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
            	if($returnJSON === true){
            		return response()->json([
            			'message' => ucfirst($validator->errors()->first())
            		], 400);
            	}else{
            		return [
            			'message' => ucfirst($validator->errors()->first()),
            			'data'    => '0'
            		];
            	}
            }

            $wh = Warehouse::find($request->warehouse_id);

            if(!$wh){
            	if($returnJSON === true){
            		return response()->json([
						'status'  => false,
            			'message' => "Warehouse tidak ditemukan."
            		], 404);
            	}else{
            		return [
						'status'  => false,
            			'message' => "Warehouse tidak ditemukan.",
            			'data'    => '0'
            		];
            	}
            }

            // $user = User::where('email', $request->email)
    		// 			->first();

    		// $order = Order::where(['user_id' => $user->id, 'warehouse_id' => $request->warehouse_id])
    		// 			->select('id', 'user_id')
			// 			->first();
			
			$order = Order::where('id',$request->order_id)
						->select('id', 'user_id')
						->first();


    		if(!$order){
    			if($returnJSON === true){
    				return response()->json([
						'status'  => false,
    					'message' => "Order kosong.",
    					'data'    => '0'
    				], 404);
    			}else{
    				return [
						'status'  => false,
    					'message' => "Order kosong.",
    					'data'    => '0'
    				];
    			}
    		}

    		$order_item = OrderItem::where([
    			'order_id'      => $order->id,
    		])->get();


    		if(count($order_item) == 0){
    			if($returnJSON === true){
    				return response()->json([
						'status'  => false,
    					'message' => "Order kosong.",
    					'data'    => '0'
    				], 404);
    			}else{
    				return [
						'status'  => false,
    					'message' => "Order kosong.",
    					'data'    => '0'
    				];
    			}
    		}


			// ga usah cari user address, carinya lewaat shipment type byy order id -> terus diget subdistrict idnya

			/*
			$CHECK_ADDRESS = UserAddress::where([
                'id'      => $request->address_id,
                'user_id' => $user->id
            ])->first();

            if(!$CHECK_ADDRESS && $request->address_id != '0'){
                if($returnJSON === true){
                    return response()->json([
                        'message' => "Alamat tidak ditemukan."
                    ], 404);
                }else{
                    return [
                        'message' => "Alamat tidak ditemukan.",
                        'data'    => '0'
                    ];
                }
            }

            $ADDRESS = '';

            if($request->address_id == '0'){
                $ADDRESS = $user->profile->subdistrict_id;
            }else{
                $ADDRESS = $CHECK_ADDRESS->subdistrict_id;
			}
			*/

			$CHECK_SHIPMENT = ShipmentType::where('order_id', $order->id)->select('courier_id', 'destination_id', 'courier_service')->first();

            if(!$CHECK_SHIPMENT){
            	if($returnJSON === true){
            		return response()->json([
						'status'  => false,
            			'message' => "Warehouse tidak ditemukan."
            		], 404);
            	}else{
            		return [
						'status'  => false,
            			'message' => "Warehouse tidak ditemukan.",
            			'data'    => '0'
            		];
            	}
            }

			$ADDRESS 		= $CHECK_SHIPMENT->destination_id;
			$COURIER_ID		= $CHECK_SHIPMENT->courier_id;
			$COURIER_SERVICE= $CHECK_SHIPMENT->courier_service;

			$DATA_COURIER	= Courier::find($COURIER_ID);
			$PARAM_COURIER	= $DATA_COURIER->code;

			// kurir id nyaridari shipment type
			// logic nya adalah cari kurir id dari Shipment Type where order id -> dapetin kurir id -> terus difind model courir dapetin code


			/*
    		$DATA_COURIER  = [];
    		$PARAM_COURIER = '';
    		$GET_COURIER   = Courier::all();

    		foreach($GET_COURIER as $index => $a) {

    			$GET_SETTING = CourierSetting::where([
    				'warehouse_id' => $request->warehouse_id,
    				'courier_id'   => $a->id
    			])->first();

    			if($GET_SETTING){
    				if($GET_SETTING->value == 'on'){

    					if($PARAM_COURIER != ''){
    						$PARAM_COURIER .= ':' . $a->code;
    					}else{
    						$PARAM_COURIER = $a->code;
    					}

    					$temp = [
    						'id'   => $a->id,
		    				'code' => $a->code,
		    				'name' => $a->name,
		    				'logo' => url() . '/' . $a->logo
		    			];

		    			array_push($DATA_COURIER, $temp);

    				}
    			}

			}
			*/

			
    		$TOTAL_WEIGHT = 0;

			if(!$CALC_WEIGHT_MANUAL) {


				foreach($order_item as $a){

					$WEIGHT = (int)$a->product->prod_gram * (int)$a->total_item;
					$TOTAL_WEIGHT += (int)$WEIGHT;

				}
			
			} else {

				$TOTAL_WEIGHT = $CALC_WEIGHT_MANUAL;

			}

    		$param = [
    			'origin'	 	  => $wh->subdistrict_id,
    			'originType'  	  => 'subdistrict',
    			'destination'     => $ADDRESS,
    			'destinationType' => 'subdistrict',
    			'weight'		  => $TOTAL_WEIGHT,
    			'courier'    	  => $PARAM_COURIER
			];

    		$api = Http::withHeaders([

    			'key' => _con('RAJAONGKIR-KEY')

    		])->post(_con('RAJAONGKIR-URL') . 'api/cost', $param);

			$data = [];

    		foreach($api['rajaongkir']['results'] as $index => $a) {

    			foreach($a['costs'] as $b){

					// terus di filter by shipment type where order id where courier service tersebut 
					if($b['service'] == $COURIER_SERVICE) {

						$temp = [
							'id'        => $DATA_COURIER['id'],
							'code'      => $DATA_COURIER['code'],
							'logo'      => $DATA_COURIER['logo'],
							'name' 	    => $DATA_COURIER['name'],
							'service'   => $b['service'],
							'desc'      => $b['description'],
							'price'     => 'Rp ' . _numberFormat($b['cost'][0]['value']),
							'price_ori' => $b['cost'][0]['value']
						];

						if($b['cost'][0]['etd'] != ''){
							$temp['desc'] .= ' (' . $b['cost'][0]['etd'] . ' hari)';
						}

						array_push($data, $temp);

					}

    				

    			}

			}
			
    		$location = [
    			'origin'      => $api['rajaongkir']['origin_details'],
    			'destination' => $api['rajaongkir']['destination_details']
    		];

    		$resp = [
    			'message'  => 'Data berhasil diambil.',
    			'param'    => $param,
    			'info'     => [
    				'order_id' => $order->id
    			],
    			'location' => $location,
    			'data'	   => $data
			];
			
    		if($returnJSON === true){

    			return response()->json($resp, 200);

    		}else{

    			return $resp;

    		}

		}catch(Exception $e){

			if($returnJSON === true){
				return response()->json([
					'status'  => false,
					'message' => 'Terdapat kesalahan pada sistem internal.',
					'error'   => $e->getMessage()
				], 500);
			}else{
				return [
					'status'  => false,
					'message' => 'Terdapat kesalahan pada sistem internal.',
					'error'   => $e->getMessage(),
					'data'    => '0'
				];
			}

		}
	}
}
