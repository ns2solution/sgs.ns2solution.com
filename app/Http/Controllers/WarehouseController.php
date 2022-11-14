<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Warehouse;
use App\Place;

class WarehouseController extends Controller
{
	public function get($id = null)
    {
    	try{

    		if($id){
                $warehouse = Warehouse::select('id', 'short', 'name', 'code')->where('id', $id)->where('status',1)->get();
            }else {
                $warehouse = Warehouse::select('id', 'short', 'name', 'code')->where('status',1)->get();
			}

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $warehouse
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

	public function createUpdate(Request $request)
    {
    	try{

    		$data = [
    			'short'	=> $request->data['short'],
    			'name'	=> $request->data['name'],
    			'code'	=> $request->data['code']
    		];

    		if($request->data['id'] == ''){

    			Warehouse::create($data);

    			$message = 'Warehouse berhasil ditambahkan.';

    		}else{

    			Warehouse::where('id', $request->data['id'])->update($data);

    			$message = 'Data warehouse berhasil diperbarui.';

    		}

    		return response()->json([
                'message' => $message,
                'data'    => $data
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

	public function delete(Request $request)
    {
        try{

            $id = $request->data['id'];
            $by = $request->data['by'];

            $wh = Warehouse::find($id);

            if(!$wh){
                return response()->json([
                    'message' => "Warehouse tidak ditemukan."
                ], 404);
            }

            // Place::where('warehouse_id', $id)->delete();
            $wh->delete();

            return response()->json([
                'message' => 'Warehouse berhasil dihapus.',
                'data'    => $request->data
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
	}
	
	public function updateStatus(Request $request)
	{
		try{
			// return $request->mode;
			if($request->mode == 'false'){
				// return 'masuk sini';
				$wr = Warehouse::find($request->id);
				$wr->status = 1;
				$wr->save();
			}else{
				$wr = Warehouse::find($request->id);
				$wr->status = 0;
				$wr->save();
			}

			return response()->json([
                'message' => 'Status Warehouse Berhasil.',
                'data'    => $request->data
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

    		$columns = [null, 'id', 'short', 'name', 'code', 'status'];

	    	$limit = $request->input('length');
	        $start = $request->input('start');
	        $order = $columns[$request->input('order.0.column')];
	        $dir   = $request->input('order.0.dir');

	        $data          = array();
	        $totalData     = Warehouse::where('deleted_at', NULL)->count();
	        $totalFiltered = $totalData;
	        $posts	       = '';

	    	if(empty($request->input('search.value'))){

	    		$posts = Warehouse::where('deleted_at', NULL)
	    						->offset($start)
	    						->limit($limit)
	    						->orderBy($order, $dir)
	    						->get();

	    	}else{

	    		$search = $request->input('search.value');

	            $tb = Warehouse::where('id', 'LIKE', "%{$search}%")
	            				->orWhere('short', 'LIKE', "%{$search}%")
	            				->orWhere('name', 'LIKE', "%{$search}%")
	            				->orWhere('code', 'LIKE', "%{$search}%");

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

	        		$d['id']	= $a->id;
	        		$d['short']	= $a->short;
	        		$d['name']	= $a->name;
	        		$d['code']	= $a->code;
	        		$d['status']	= $a->status;

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
