<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\StockProduct;
use App\Warehouse;
use App\Product;
use App\User;
use App\CartItem;

class StockProductController extends Controller
{
	public function get($wh_id = null, $prod_id = null)
    {
    	try{

			$data = [];

			if($wh_id != null){

				if($prod_id){

					foreach(Warehouse::where('id', $wh_id)->get() as $a){
						$data[$a->id][$prod_id] = [
							'warehouse_id'	=> $a->id,
							'product_id'	=> $prod_id,
							'stock'			=> 0
						];
					}

					$stock = StockProduct::where(['warehouse_id' => $wh_id, 'product_id' => $prod_id])->first();

					if($stock){
						$data[$wh_id][$prod_id]['stock'] = $stock->stock;
					}

				}else{

					foreach(Warehouse::where('id', $wh_id)->get() as $a){
						foreach(Product::all() as $b){

							$data[$a->id][$b->id] = [
								'warehouse_id'	=> $a->id,
								'product_id'	=> $b->id,
								'prod_name'	=> $b->prod_name,
								'stock'			=> 0
							];

						}
					}

					foreach(StockProduct::where('warehouse_id', $wh_id)->get() as $a){
						if(array_key_exists($a->product_id, $data[$a->warehouse_id])){
							$data[$a->warehouse_id][$a->product_id]['stock'] = $a->stock;
						}
					}

				}

			}else{

				foreach(Warehouse::all() as $a){
					foreach(Product::all() as $b){

						$data[$a->id][$b->id] = [
							'warehouse_id'	=> $a->id,
							'product_id'	=> $b->id,
							'prod_name'		=> $b->prod_name,
							'stock'			=> 0
						];

					}
				}

				foreach(StockProduct::all() as $a){
					if(array_key_exists($a->product_id, $data[$a->warehouse_id])){
						$data[$a->warehouse_id][$a->product_id]['stock'] = $a->stock;
					}
				}

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

    public function getStockWarehouse(Request $request)
    {
        try{

            $wh   = Warehouse::all();
            $data = [];

            foreach($wh as $index => $a){

                $search = StockProduct::where([
                    'warehouse_id' => $a->id,
                    'product_id'   => $request->product_id
                ])->first();

                if($search){

                    if($search->stock != 0){

                        array_push($data, [
                            'warehouse_id' => $a->id,
                            'warehouse'    => $a->name,
                            'product_id'   => (int)$request->product_id,
                            'stock'        => $search->stock
                        ]);

                    }

                }

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

    public function updateWarehouse(Request $request)
    {
        try{

            $data_request = [
                'cart_item_id' => $request->cart_item_id,
                'warehouse_id' => $request->warehouse_id
            ];

            $cart_item = CartItem::find($request->cart_item_id);

            if(!$cart_item){
                return response()->json([
                    'message' => 'Item cart tidak ditemukan.',
                    'data'    => $data_request
                ], 404);
            }

            $check_stock = StockProduct::where([
                'product_id'   => $cart_item->product_id,
                'warehouse_id' => $request->warehouse_id
            ])->first();

            if(!$check_stock){
                return response()->json([
                    'message' => 'Terdapat kesalahan tidak terduga.',
                    'data'    => $data_request
                ], 400);
            }else{
                if($cart_item->total_item > $check_stock->stock){
                    return response()->json([
                        'message' => 'Jumlah stok tidak mencukupi.',
                        'data'    => $data_request
                    ], 400);
                }
            }

            $cart_item->warehouse_id = $request->warehouse_id;
            $cart_item->save();

            return response()->json([
                'message' => 'Item cart berhasil diperbarui.',
                'data'    => $data_request
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

	public function getRequest(Request $request)
    {
    	try{

			if($request->has('id')){
				$data = StockProduct::where([['id','=',$request->id],['deleted_at','=',NULL]])->with('warehouse','product')->first();
			}elseif($request->has('warehouse_id')){
				$data = StockProduct::where([['warehouse_id','=',$request->warehouse_id],['deleted_at','=',NULL]])->with('warehouse','product')->get();
			}elseif($request->has('product_id')){
				$data = StockProduct::where([['product_id','=',$request->product_id],['deleted_at','=',NULL]])->with('warehouse','product')->get();
			}elseif($request->has('bundle_id')){
				$data = StockProduct::where([['deleted_at','=',NULL]])->whereIn('id',$request->bundle_id)->with('warehouse','product')->get();
			}elseif($request->has('product_id') && $request->has('warehouse_id')){
				$data = StockProduct::where([['warehouse_id','=',$request->warehouse_id],['product_id','=',$request->product_id],['deleted_at','=',NULL]])->with('warehouse','product')->get();
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

    public function update(Request $request)
    {
        try{

            $request['stock'] = (int)str_replace('.', '', $request->stock);

            StockProduct::updateOrCreate([
                'warehouse_id' => $request->warehouse_id,
                'product_id'   => $request->id
            ],[
                'stock'        => $request->stock,
                'created_by'   => $request->by,
                'updated_by'   => $request->by
            ]);

            return response()->json([
                'message' => 'Stok produk berhasil diperbarui.',
                'data'    => $request->all()
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function dataTable(Request $request)
    {
        try{

            $columns = [null, null, 'product.id', null, 'prod_number', 'prod_name','A.name', null, null, null, null, null];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Product::where('deleted_at', NULL)->count();
            $totalFiltered = $totalData;
            $posts         = '';

            if(empty($request->input('search.value'))){

                $posts = Product::leftJoin('principles AS A', 'A.id', '=', 'product.principle_id')
                                ->select('product.*', 'A.code', 'A.name AS principle_name')
                                ->where('product.deleted_at', NULL)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

            }else{

                $search = $request->input('search.value');

                $tb = Product::leftJoin('principles AS A', 'A.id', '=', 'product.principle_id')
                                ->select('product.*', 'A.code', 'A.name AS principle_name')
                                ->where('product.id', 'LIKE', "%{$search}%")
                                ->orWhere('prod_number', 'LIKE', "%{$search}%")
                                ->orWhere('prod_name', 'LIKE', "%{$search}%");

                $posts = $tb->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

                $totalFiltered = $tb->count();

            }

            if(!empty($posts)){

                $no  = $start + 1;
                $row = 0;

                foreach($posts as $key => $a){

                	$stock = 0;
                	$created_at = '-';
                	$created_by = '-';
                	$updated_at = '-';
                	$updated_by = '-';

                	$get_stock = StockProduct::where([
                		'warehouse_id' => $request->_wh,
                		'product_id'   => $a->id
                	])->first();

                	if($get_stock){
                		$stock      = $get_stock->stock;
                		$created_at = _customDate($get_stock->created_at);
                		$created_by = $get_stock->created_by;
                		$updated_at = _customDate($get_stock->updated_at);
                		$updated_by = $get_stock->updated_by;
                	}

                    $d['no']        = $no++;
                    $d['id']        = $a->id;
                    $d['warehouse'] = Warehouse::find($request->_wh)->name;
                    $d['produk']	= $a->prod_name;
                    $d['prod_num']  = $a->prod_number;
                    $d['principle']  = $a->principle_name;
                    $d['stock']     = $stock;
                    $d['prod_type_id'] = $a->prod_type_id;
                    $d['created_at'] = $created_at;
                    $d['created_by'] = $created_by;
                    $d['updated_at'] = $updated_at;
                    $d['updated_by'] = $updated_by;

                    $row++;
                    $data[] = $d;

                }

            }

            $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

			echo json_encode($json_data);


        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
