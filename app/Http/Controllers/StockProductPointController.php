<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\StockProductPoint;
use App\Warehouse;
use App\ProductPoint;
use Exception;

class StockProductPointController extends Controller
{

    public function dataTable(Request $request)
    {
        try{

            $columns = [null, null, 'id', null, 'prod_number', 'prod_name', 'A.name', null, null, null, null, null];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = ProductPoint::where('deleted_at', NULL)->count();
            $totalFiltered = $totalData;
            $posts         = '';

            if(empty($request->input('search.value'))){

                $posts = ProductPoint::leftJoin('principles AS A', 'A.id', '=', 'product_point.principle_id')
                                ->select('product_point.*', 'A.code', 'A.name AS principle_name')
                                ->where('product_point.deleted_at', NULL)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

            }else{

                $search = $request->input('search.value');

                $tb = ProductPoint::leftJoin('principles AS A', 'A.id', '=', 'product_point.principle_id')
                                ->select('product_point.*', 'A.code', 'A.name AS principle_name')
                                ->where('id', 'LIKE', "%{$search}%")
                                ->orWhere('prod_number', 'LIKE', "%{$search}%")
                                ->orWhere('prod_name', 'LIKE', "%{$search}%")
                                ->orWhere('A.name', 'LIKE', "%{$search}%");

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

                	$get_stock = StockProductPoint::where([
                		'warehouse_id' => $request->_wh,
                		'product_point_id'   => $a->id
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

    public function update(Request $request)
    {
        try{

            $request['stock'] = (int)str_replace('.', '', $request->stock);

            StockProductPoint::updateOrCreate([
                'warehouse_id' => $request->warehouse_id,
                'product_point_id'   => $request->id
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
}
