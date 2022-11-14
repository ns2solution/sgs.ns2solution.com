<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Validator;
use Exception;

use App\Brand;

class BrandController extends Controller
{
    public function get(Request $request, $id = null)
    {
        try {
            if ($id) {
                $brand = Brand::findOrFail($id);
            } else {
                if(isset($request->principle)){
                    $brand = Brand::where('principle_id',$request->principle)->get();
                }else{
                    $brand = Brand::all();
                }
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $brand
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function dataTable(Request $request)
    {
        try {
            $columns = ['id_brand', null, 'code', 'brand_name', 'principle', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Brand::count();
            $totalFiltered = $totalData;
            $posts	       = '';

            if (empty($request->input('search.value'))) {

                $posts = Brand::leftJoin('principles', 'principles.id', '=', 'brands.principle_id')
                        ->select(
                            'brands.*', 'principles.name AS principle'
                        )
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

            } else {

                $search = $request->input('search.value');

                $tb = Brand::leftJoin('principles', 'principles.id', '=', 'brands.principle_id')
                        ->select(
                            'brands.*', 'principles.name AS principle'
                        )
                        ->where('brand_name', 'LIKE', "%{$search}%")
                        ->orWhere('id_brand', 'LIKE', "%{$search}%")
                        ->orWhere('brands.code', 'LIKE', "%{$search}%")
                        ->orWhere('principles.name', 'LIKE', "%{$search}%");

                $posts = $tb->offset($start)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();

                $totalFiltered = $tb->count();

            }

            if (!empty($posts)) {
                $no  = $start + 1;
                $row = 0;

                foreach ($posts as $a) {

                    $d['no']         = $no++;
                    $d['id_brand']   = $a->id_brand;
                    $d['brand_logo'] = $a->brand_logo;
                    $d['brand_name'] = $a->brand_name;
                    $d['created_at'] = _customDate($a->created_at);
                    $d['created_by'] = $a->created_by;
                    $d['updated_at'] = _customDate($a->updated_at);
                    $d['updated_by'] = $a->updated_by;

                    $d['principle']  = $a->principle;
                    $d['code']       = $a->code;

                    $row++;
                    $data[] = $d;

                }
            }

            $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

            return json_encode($json_data);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {

        $rules = [
            'brand_name'   => 'required'
        ];

        $rules['logo'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $by = $request->by;
            $request['created_by'] = $by;

            if($request->file('logo')) {
                $upload = _uploadFile($request->file('logo'), 'brand');
                $request['brand_logo'] = $upload;
            }

            $brand = Brand::create($request->except('token', 'email', 'id_brand', 'logo'));

            return response()->json([
                'status' => true,
                'message' => 'Brand berhasil ditambahkan.',
                'data'    => $brand
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }

    }

    public function update(Request $request, $id)
    {

        $rules = [
            'brand_name'   => 'required'
        ];

        if($request->file('logo')) {
            $rules['logo'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        try {

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $by = $request->by;
            $request['updated_by'] = $by;

            if($request->file('logo')) {
                $upload = _uploadFile($request->file('logo'), 'brand');
                $request['brand_logo'] = $upload;
            }

            $brand = Brand::where('id_brand', $id)->update($request->except('token', 'email', 'by', 'logo'));

            return response()->json([
                'status' => true,
                'message' => 'Data brand berhasil diperbarui.',
                'data'    => $brand
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function delete(Request $request, $id)
    {
        try {
            
            $brand = Brand::where('id_brand', $id);
            $brand->delete();

            return response()->json([
                'status' => true,
                'message' => 'Brand berhasil dihapus.',
                'data'    => $brand
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
