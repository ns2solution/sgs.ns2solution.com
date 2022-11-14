<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use App\Place;

class PlaceController extends Controller
{
    public function get($provinsi_id = null)
    {
    	try{

    		if($provinsi_id){
                $place = Place::where('province_id', $provinsi_id)->get();
            }else {
                $place = Place::get();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $place
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
                'province_id'   => $request->data['prov_id'],
                'city_name'     => $request->data['place'],
                'warehouse_id'  => $request->data['wh_id'],
                'type'          => $request->data['type'],
                'postal_code'   => $request->data['postal_code']
            ];

            $data['province'] = DB::table('rajaongkir_province')
                                    ->where('province_id', $data['province_id'])
                                    ->first()
                                    ->province;

            if($request->data['id'] == ''){

                Place::create($data);

                $message = 'Place berhasil ditambahkan.';

            }else{

                Place::where('city_id', $request->data['id'])->update($data);

                $message = 'Data place berhasil diperbarui.';

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

            $pl = Place::find($id);

            if(!$pl){
                return response()->json([
                    'message' => "Place tidak ditemukan."
                ], 404);
            }

            $pl->forceDelete();

            return response()->json([
                'message' => 'Place berhasil dihapus.',
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

            $columns = [null, 'city_id', 'warehouse_id', 'province', 'type', 'city_name', 'created_at', 'updated_at'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Place::where('deleted_at', NULL)->count();
            $totalFiltered = $totalData;
            $posts         = '';

            if(empty($request->input('search.value'))){

                $posts = Place::where('deleted_at', NULL)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

            }else{

                $search = $request->input('search.value');

                $tb = Place::where('city_id', 'LIKE', "%{$search}%")
                                ->orWhere('province', 'LIKE', "%{$search}%")
                                ->orWhere('city_name', 'LIKE', "%{$search}%")
                                ->orWhere('warehouse_id', 'LIKE', "%{$search}%");

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

                    $d['id']           = $a->city_id;
                    $d['warehouse_id'] = $a->warehouse_id;
                    $d['provinsi']     = $a->province;
                    $d['type']         = $a->type;
                    $d['city_name']    = $a->city_name;
                    $d['postal_code']  = $a->postal_code;
                    $d['updated_at'] = _customDate($a->updated_at);

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
