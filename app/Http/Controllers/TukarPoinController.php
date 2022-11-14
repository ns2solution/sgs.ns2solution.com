<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\TukarPoin;
use Exception;
use Validator;

class TukarPoinController extends Controller
{
    public function dataTable(Request $request)
    {
    	try{

    		$columns = array('id', 'id');

	    	$limit = $request->input('length');
	        $start = $request->input('start');
	        $order = $columns[$request->input('order.0.column')];
	        $dir   = $request->input('order.0.dir');

	        $data          = array();
	        $totalData     = TukarPoin::where('deleted_at', NULL)->count();
	        $totalFiltered = $totalData;
	        $posts	       = '';

	    	if(empty($request->input('search.value'))){

	    		$posts = TukarPoin::where('deleted_at', NULL)
	    						->offset($start)
	    						->limit($limit)
	    						->orderBy($order, $dir)
	    						->get();

	    	}else{

	    		$search = $request->input('search.value');

	            $tb = TukarPoin::where('id', 'LIKE', "%{$search}%")
	            				->orWhere('fullname', 'LIKE', "%{$search}%")
	            				->orWhere('email', 'LIKE', "%{$search}%");

	            $posts = $tb->offset($start)
	            				->limit($limit)
	            				->orderBy($order, $dir)
	            				->get();

	            $totalFiltered = $tb->count();

	        }

	        if(!empty($posts)){

	        	$no  = $start + 1;
	        	$row = 0;

	        	foreach($posts as $a){
	        		$d['no'] = $no++;
	        		$d['id'] = $a->id;
	        		$d['title'] = $a->title;
	        		$d['product_image'] = $a->product_image;
	        		$d['product_name'] = $a->product_name;
	        		$d['warpay'] = $a->warpay;
	        		$d['type'] = $a->type;
	        		$d['min_poin'] = $a->min_poin;
	        		$d['stok'] = $a->stok;
	        		$d['status'] = $a->status;
	        		$d['created_at'] = _customDate($a->created_at);
	        		$d['created_by'] = $a->created_by;
	        		$d['updated_at'] = _customDate($a->updated_at);
	        		$d['updated_by'] = $a->updated_by;

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

    public function create(Request $request)
    {
        $rules = [
            'product_image' => 'required',
            'title' => 'required',
            'type' => 'required',
            'min_poin' => 'required',
        ];


        unset($request['token']);
        unset($request['email']);

        $upload = _uploadFile($request->file('product_image'), 'product_image');
        // return $request->data;

        try{
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $request->product_image = $upload;
            $data = [
                'title' => $request->title,
                'product_image' => $upload,
                'type' => $request->type,
                'min_poin' => str_replace('.', '', $request->min_poin),
                'stok' => str_replace('.', '', $request->stok)
            ];

            if($request->type == 1){
                $data['product_name'] = $request->product_name;
            }else{
                $data['warpay'] = str_replace('.', '', $request->warpay);
            }

            // return $data;

            TukarPoin::create($data);

            return response()->json([
                'message' => 'Berhasil Tambah Produk Poin',
                'data'    => $request->all()
            ], 200);

        }catch(Exception $e){

            return response()->json([
        		'message' => 'Terdapat kesalahan pada sistem internal.',
        		'error'   => $e->getMessage()
        	], 500);

        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required',
            'type' => 'required',
            'min_poin' => 'required',
        ];


        unset($request['token']);
        unset($request['email']);

        // $upload = _uploadFile($request->file('product_image'), 'product_image');
        // return $request->data;

        try{
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $data = [
                'title' => $request->title,
                'type' => $request->type,
                'min_poin' => $request->min_poin
            ];

            if($request->file('product_image')){
                $data['product_image'] = _uploadFile($request->file('product_image'), 'product_image');
            }

            if($request->type == 1){
                $data['product_name'] = $request->product_name;
            }else{
                $data['warpay'] = $request->warpay;
            }


            $update = TukarPoin::where('id',$id)->update($data);

            if($update){
                return response()->json([
                    'message' => 'Berhasil Update Produk Poin',
                    'data'    => $request->all()
                ], 200);
            }else{
                return response()->json([
                    'message' => 'Gagal Update Produk Poin',
                    'data'    => $request->all()
                ], 200);

            }


        }catch(Exception $e){

            return response()->json([
        		'message' => 'Terdapat kesalahan pada sistem internal.',
        		'error'   => $e->getMessage()
        	], 500);

        }
    }

    public function delete(Request $request, $id){

        try{

            $category = TukarPoin::find($id);
            $category->delete();

            return response()->json([
                'message' => 'Berhasil Hapus Kategori',
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
