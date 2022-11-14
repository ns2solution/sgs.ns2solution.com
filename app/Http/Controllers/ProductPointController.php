<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Category;
use App\Place;
use App\StockProduct;
use App\TopProductPoint;
use App\ProductPoint;

use Exception;
use Validator;


class ProductPointController extends Controller
{
    public function create_update_top_product_point(Request $req) {

        $rules = [
            'product_point_id'          => 'required',
            'active'              => 'required'
        ];

        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
        }

       
        try {

            $top_product_point = TopProductPoint::updateOrCreate([
                'product_point_id'   => $req->product_point_id
            ],[
                'active'       => $req->active,
            ]);

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $top_product_point);
            
        } catch (Exception $e) {

            return __jsonResp(false, 'Terdapat kesalahan pada sistem internal.', 500, $e);

        }        

    
    }



    public function find_top_product_point($id) {
        
        try {

            if($id) {
                
                $top_product_point = TopProductPoint::where('product_point_id', $id)->first();
                
                if(!$top_product_point) {

                    return __jsonResp(false, 'Data tidak ada', 200, null, $top_product_point);
                
                }

                return __jsonResp(true, 'Data Berhasil diambil', 200, null, $top_product_point);

            } else {

                return __jsonResp(false, 'ID tidak diketahui', 200, null, $top_product_point);
            
            }
            
        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }        

    
    }


    public function get_top_product_point(Request $request) {

        $request['warehouse_id'] = $request->warehouse_id ? $request->warehouse_id : 0;

        try {

            $product_point = DB::table('product_point AS Z')
            ->leftJoin('warehouse', function ($join) use ($request) {
                $join->on('warehouse.id', '=', DB::raw("'" . $request->warehouse_id . "'"));
            })
            ->leftJoin('master_stock_product_point', function ($join) use ($request) {
                $join->on('master_stock_product_point.warehouse_id', '=', DB::raw("'" . $request->warehouse_id . "'"));
                $join->on('master_stock_product_point.product_point_id',   '=', 'Z.id');
            })
            ->leftJoin('product_point_image', function ($join) {
                $join->on('Z.id', '=', 'product_point_image.product_point_id')
                     ->on(
                        'product_point_image.id',
                        '=',
                        DB::raw("(select min(`id`) from product_point_image where Z.id = product_point_image.product_point_id)")
                     );
            })
            ->join('top_product_point AS B', 'B.product_point_id', '=', 'Z.id')
            ->where('B.active', 1)
            ->select(
                'Z.*', 'product_point_image.path AS image',
                DB::raw('IFNULL( master_stock_product_point.stock, 0) as stock')
            )->get();

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $product_point);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }

    }


    public function get_mobile(Request $request)
    {

        $request['warehouse_id'] = $request->warehouse_id ? $request->warehouse_id : 0;

        try{

            $product_point = DB::table('product_point AS Z');

            $product_point->leftJoin('principles AS A',   'A.id', '=', 'Z.principle_id')
            ->leftJoin('brands         AS B', 'B.id_brand',   '=', 'Z.brand_id')
            ->leftJoin('category       AS C', 'C.id',         '=', 'Z.sub_category_id')
            ->leftJoin('category       AS D', 'D.id',         '=', 'C.parent_id')
            ->leftJoin('product_type   AS E', 'E.id',         '=', 'Z.prod_type_id')
            ->leftJoin('product_status AS F', 'F.id',         '=', 'Z.prod_status_id')
            ->leftJoin('settings', function ($join) {
                $join->on('settings.id', '=', DB::raw("'" . 1 . "'"));
            })
            ->leftJoin('warehouse', function ($join) use ($request) {
                $join->on('warehouse.id', '=', DB::raw("'" . $request->warehouse_id . "'"));
            })
            ->leftJoin('master_stock_product_point', function ($join) use ($request) {
                $join->on('master_stock_product_point.warehouse_id', '=', DB::raw("'" . $request->warehouse_id . "'"));
                $join->on('master_stock_product_point.product_point_id',   '=', 'Z.id');
            })
            ->leftJoin('product_point_image', function ($join) {
                $join->on('Z.id', '=', 'product_point_image.product_point_id')
                     ->on(
                        'product_point_image.id',
                        '=',
                        DB::raw("(select min(`id`) from product_point_image where Z.id = product_point_image.product_point_id)")
                     );
            })
            ->select(
                'Z.*',
                DB::raw("CONCAT('Rp ', FORMAT(Z.prod_base_price, 0, 'de_DE')) AS prod_base_price"),
                'A.code AS principle_code',             'A.name AS principle_name',
                'B.code AS brand_code',                 'B.brand_name',
                'C.category_name AS sub_category_name', 'C.id AS sub_category_id',
                'D.category_name',                      'D.id AS category_id',
                'E.product_type',
                'F.status_name',
                DB::raw('IFNULL( product_point_image.path, "assets/product_image/_blank.jpg") as prod_image'),
                'warehouse.id       AS warehouse_id', 'warehouse.name AS warehouse_name',
                DB::raw('FLOOR(Z.prod_base_price / settings.convertion_warpay) as prod_warpay'),
                DB::raw('IFNULL( master_stock_product_point.stock, 0) as stock')
            )
            // ->where('Z.prod_type_id', $request->prod_type_id)
            ->where('Z.prod_status_id', '1')
            ->where('Z.deleted_at', '=', null);

            if(isset($request->subcategory_id) && $request->subcategory_id != ''){

                $product_point->where('sub_category_id', $request->subcategory_id);

            }

            if(isset($request->product_point_id) && $request->product_point_id != ''){

                $product_point->where('Z.id', $request->product_point_id);

            }

            if(isset($request->prod_name) && $request->prod_name != ''){

                // $product_point->whereRaw('SOUNDEX(Z.prod_name)', 'SOUNDEX(' . $request->prod_name . ')');
                $product_point->where('Z.prod_name', 'LIKE', '%' . $request->prod_name . '%');
            }

            $prod = $product_point->orderBy('Z.prod_name', 'ASC')->get();

            if($prod){
                foreach($prod as &$pr){
                    $gambar = [];
                    $image = DB::table('product_point_image')
                                ->select('path')
                                ->where('deleted_at','=',null)
                                ->where('product_point_id',$pr->id)
                                ->get();

                    if(empty($image)){
                        $pr->image = '';
                    }else{
                        foreach($image as $im){
                            array_push($gambar, url(''). '/' .$im->path);
                        }
                        $pr->image = $gambar;
                    }
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil diambil.',
                'data'    => $prod
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'status'  => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }


    public function get($id = null)
    {
        try {
            if ($id) {
                $product_point = ProductPoint::findOrFail($id);
            } else {
                $product_point = ProductPoint::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product_point,
            ], 200);
        } catch (Exception $e) {
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
				$data = ProductPoint::where([['id','=',$request->id],['deleted_at','=',NULL]])->with('stock','stock.warehouse','type')->first();
            }elseif($request->has('id') && $request->has('warehouse_id')){
                $data = ProductPoint::where(function (Builder $query) {
                    return $query->where([['deleted_at','=',NULL],['master_stock_product_point.warehouse_id', '=', $request->warehouse_id],['master_stock_product_point.product_point_id', '=', $request->id]]);
                })->with('stock','stock.warehouse','type')->get();
            }else{
                $data = ProductPoint::where([['deleted_at','=',NULL]])->with('stock','stock.warehouse','type')->get();
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

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $rules = [
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            // var_dump($request->file('path'));
            

            $by = $request->by;
            $request['updated_by'] = $by;
            $request['prod_base_price'] = substr(str_replace(array(',','.'),'', $request->prod_base_price),0,-2);
            $request['prod_modal_price'] = substr(str_replace(array(',','.'),'', $request->prod_modal_price),0,-2);
            $product_point = ProductPoint::where('id', $id)->update($request->except('token', 'email', 'by', 'path','id','warehouse_id','stock','warehouse_id_pending'));

            if($request->hasFile('path'))
            {
                // return "masuk sini";
                $dir  = 'assets/product_image';

                if(!File::exists($dir)){
                    File::makeDirectory($dir);
                }

                foreach($request->file('path') as $image){
                    $name = sha1($image.time()) . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path().'/assets/product_image/', $name);

                    // $data[] = $dir . '/' . $name;
                    $insertImage = DB::table('product_point_image')->insert([
                        ['product_point_id' => $id, 'path' => $dir . '/' . $name]
                    ]);
                }
            }

            
            if($request->has('prod_type_id')){
                if($request->prod_type_id == 2){
                    //store to stock
                    //cek stock with product id
                    $data = $request->except('token', 'email', 'by', 'prod_number', 'prod_name', 'category_id', 'sub_category_id', 'prod_description', 'prod_type_id', 'prod_status_id', 'prod_modal_price', 'prod_base_price', 'prod_gram', 'principle_id', 'diskon', 'brand_id', 'path','id','warehouse_id_pending');
                    $data['product_point_id'] = $id;
                    $stockProduct = StockProductPoint::where([['product_point_id','=',$id],['warehouse_id','=',$request->warehouse_id_pending]]);
                    
                    $stockProduct->update($data);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Update Product',
                'data' => $product_point
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function delete(Request $request, $id)
    {
        try {
            $product_point = DB::table('product_point')->where('id', $id);
            $product_point->delete();
            $product_point_new = $product_point;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Hapus Product Point',
                'data'    => $product_point
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'status' => false,
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }

    public function dataTable2(Request $request)
    {
        try {
            $columns = [null, null, 'id', 'prod_number', 'prod_name', 'b_category_name', 'prod_base_point', 'prod_gram', 'c_principle_name', 'd_brand_name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = ProductPoint::count();
            $totalFiltered = $totalData;
            $posts	       = '';

            if (empty($request->input('search.value'))) {
                $posts = ProductPoint::leftJoin('category AS b', 'product_point.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product_point.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product_point.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product_point.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product_point.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product_point.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product_point.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product_point.*',
                    'b.category_name AS b_category_name',
                    'b.parent_id AS b_parent_id',
                    'b.category_image AS b_category_image',
                    'c.code AS c_principle_kode',
                    'c.name AS c_principle_name',
                    'd.id_brand AS d_id_brand',
                    'd.brand_name AS d_brand_name',
                    'e.fullname AS e_fullname',
                    'f.fullname AS f_fullname',
                    'sub.category_name AS sub_category_name',
                    'product_type.product_type AS product_type_name',
                )
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            } else {
                $search = $request->input('search.value');

                $tb = ProductPoint::where('product_point.id', 'LIKE', "%{$search}%")
                ->orWhere('product_point.prod_name', 'LIKE', "%{$search}%")
                ->orWhere('product_point.prod_number', 'LIKE', "%{$search}%")
                ->orWhere('product_point.prod_gram', 'LIKE', "%{$search}%")
                ->orWhere('b.category_name', 'LIKE', "%{$search}%")
                ->orWhere('c.name', 'LIKE', "%{$search}%")
                ->orWhere('d.brand_name', 'LIKE', "%{$search}%")
                ->orWhere('e.fullname', 'LIKE', "%{$search}%")
                ->orWhere('f.fullname', 'LIKE', "%{$search}%")
                ->leftJoin('category AS b', 'product_point.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product_point.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product_point.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product_point.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product_point.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product_point.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product_point.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product_point.*',
                    'b.category_name AS b_category_name',
                    'sub.category_name AS sub_category_name',
                    'b.parent_id AS b_parent_id',
                    'b.category_image AS b_category_image',
                    'c.code AS c_principle_kode',
                    'c.name AS c_principle_name',
                    'd.id_brand AS d_id_brand',
                    'd.brand_name AS d_brand_name',
                    'e.fullname AS e_fullname',
                    'f.fullname AS f_fullname',
                    'product_type.product_type AS product_type_name',
                );

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

                    $is_top_product_point = false;
                    $top_product_point = TopProductPoint::where('product_point_id', $a->id)->first(); 
    
                    if($top_product_point) {

                        if($top_product_point->active == 1) {
                            $is_top_product_point =  true;
                        }
                    
                    };

                    $d['no'] = $no++;
                    $d['id'] = $a->id;
                    $d['prod_number'] = $a->prod_number;
                    $d['prod_barcode_number'] = $a->prod_barcode_number;
                    $d['prod_universal_number'] = $a->prod_universal_number;
                    $d['prod_name'] = $a->prod_name;
                    $d['prod_base_price'] = $a->prod_base_price;
                    $d['prod_modal_price'] = $a->prod_modal_price ? $a->prod_modal_price : 0 ;
                    $d['prod_gram'] = $a->prod_gram;
                    $d['principle_id'] = $a->principle_id;
                    $d['category_id'] = $a->category_id;
                    $d['sub_category_id'] = $a->sub_category_id;
                    $d['prod_type_id'] = $a->prod_type_id;
                    $d['prod_status_id'] = $a->prod_status_id;
                    $d['brand_id'] = $a->brand_id;
                    $d['prod_base_point'] = $a->prod_base_point;
                    $d['created_at'] = _customDate($a->created_at);
                    $d['created_by'] = $a->e_fullname;
                    $d['updated_at'] = _customDate($a->updated_at);
                    $d['updated_by'] = $a->f_fullname;
                    // addition column
                    $d['b_category_name'] = $a->b_category_name;
                    $d['sub_category_name'] = $a->sub_category_name;
                    $d['c_principle_name'] = $a->c_principle_name;
                    $d['d_brand_name'] = $a->d_brand_name;
                    $d['diskon'] = $a->diskon ? $a->diskon : 0 ;
                    $d['harga_diskon'] = $a->diskon ? $a->prod_modal_price - (($a->diskon/100) * $a->prod_modal_price): $a->prod_modal_price ;
                    $d['product_type_name'] = $a->product_type_name;
                    $d['prod_description'] = $a->prod_description;
                    $d['prod_satuan'] = $a->prod_satuan;
                    $d['is_top_product_point'] = $is_top_product_point;

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


    public function dataTable(Request $request) {
        try {
            $columns = [null, null, 'id', 'prod_number', 'prod_name', 'b_category_name', 'prod_base_point', 'prod_gram', 'c_principle_name', 'd_brand_name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = ProductPoint::count();
            $totalFiltered = $totalData;
            $posts	       = '';

            if (empty($request->input('search.value'))) {
                $posts = ProductPoint::leftJoin('category AS b', 'product_point.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product_point.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product_point.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product_point.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product_point.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product_point.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product_point.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product_point.*',
                    'b.category_name AS b_category_name',
                    'b.parent_id AS b_parent_id',
                    'b.category_image AS b_category_image',
                    'c.code AS c_principle_kode',
                    'c.name AS c_principle_name',
                    'd.id_brand AS d_id_brand',
                    'd.brand_name AS d_brand_name',
                    'e.fullname AS e_fullname',
                    'f.fullname AS f_fullname',
                    'sub.category_name AS sub_category_name',
                    'product_type.product_type AS product_type_name',
                )
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            } else {
                $search = $request->input('search.value');

                $tb = ProductPoint::where('product_point.id', 'LIKE', "%{$search}%")
                ->orWhere('product_point.prod_name', 'LIKE', "%{$search}%")
                ->orWhere('product_point.prod_number', 'LIKE', "%{$search}%")
                ->orWhere('product_point.prod_gram', 'LIKE', "%{$search}%")
                ->orWhere('b.category_name', 'LIKE', "%{$search}%")
                ->orWhere('c.name', 'LIKE', "%{$search}%")
                ->orWhere('d.brand_name', 'LIKE', "%{$search}%")
                ->orWhere('e.fullname', 'LIKE', "%{$search}%")
                ->orWhere('f.fullname', 'LIKE', "%{$search}%")
                ->leftJoin('category AS b', 'product_point.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product_point.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product_point.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product_point.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product_point.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product_point.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product_point.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product_point.*',
                    'b.category_name AS b_category_name',
                    'sub.category_name AS sub_category_name',
                    'b.parent_id AS b_parent_id',
                    'b.category_image AS b_category_image',
                    'c.code AS c_principle_kode',
                    'c.name AS c_principle_name',
                    'd.id_brand AS d_id_brand',
                    'd.brand_name AS d_brand_name',
                    'e.fullname AS e_fullname',
                    'f.fullname AS f_fullname',
                    'product_type.product_type AS product_type_name',
                );

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
                    $d['no'] = $no++;
                    $d['id'] = $a->id;
                    $d['prod_number'] = $a->prod_number;
                    $d['prod_barcode_number'] = $a->prod_barcode_number;
                    $d['prod_universal_number'] = $a->prod_universal_number;
                    $d['prod_name'] = $a->prod_name;
                    $d['prod_base_price'] = $a->prod_base_price;
                    $d['prod_modal_price'] = $a->prod_modal_price ? $a->prod_modal_price : 0 ;
                    $d['prod_gram'] = $a->prod_gram;
                    $d['principle_id'] = $a->principle_id;
                    $d['category_id'] = $a->category_id;
                    $d['sub_category_id'] = $a->sub_category_id;
                    $d['prod_type_id'] = $a->prod_type_id;
                    $d['prod_status_id'] = $a->prod_status_id;
                    $d['brand_id'] = $a->brand_id;
                    $d['prod_base_point'] = $a->prod_base_point;
                    $d['created_at'] = _customDate($a->created_at);
                    $d['created_by'] = $a->e_fullname;
                    $d['updated_at'] = _customDate($a->updated_at);
                    $d['updated_by'] = $a->f_fullname;
                    // addition column
                    $d['b_category_name'] = $a->b_category_name;
                    $d['sub_category_name'] = $a->sub_category_name;
                    $d['c_principle_name'] = $a->c_principle_name;
                    $d['d_brand_name'] = $a->d_brand_name;
                    $d['diskon'] = $a->diskon ? $a->diskon : 0 ;
                    $d['harga_diskon'] = $a->diskon ? $a->prod_modal_price - (($a->diskon/100) * $a->prod_modal_price): $a->prod_modal_price ;
                    $d['product_type_name'] = $a->product_type_name;
                    $d['prod_description'] = $a->prod_description;
                    $d['prod_satuan'] = $a->prod_satuan;

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

        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            DB::beginTransaction();

            // $max_id_product = explode('/', Product::max('prod_number'));
            // $latest_id_product = array_shift($max_id_product);

            $by = $request->by;
            $request['created_by'] = $by;

            // if ($latest_id_product) {
            //     $request['prod_number'] = sprintf("%s/PRD/".strtoupper(date('M/y')), $latest_id_product + 1);
            //     $product = Product::create($request->except('token', 'email', 'by'));
            //     $product->id;
            // } else {
            //     $request['prod_number'] = sprintf("%s/PRD/".strtoupper(date('M/y')), 1);
            // }
            $request['prod_base_price'] = substr(str_replace(array(',','.'),'', $request->prod_base_price),0,-2);
            $request['prod_modal_price'] = substr(str_replace(array(',','.'),'', $request->prod_modal_price),0,-2);

            $product_point = ProductPoint::create($request->except('token', 'email', 'by', 'path','id','warehouse_id','stock','warehouse_id_pending'));
            // $product_point->id;
            // return $request->file('path');
            if($request->hasFile('path'))
            {
                // return "masuk sini";
                $dir  = 'assets/product_image';

                if(!File::exists($dir)){
                    File::makeDirectory($dir);
                }

                foreach($request->file('path') as $image){
                    $name = sha1($image.time()) . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path().'/assets/product_image/', $name);

                    // $data[] = $dir . '/' . $name;
                    $insertImage = DB::table('product_point_image')->insert([
                        ['product_point_id' => $product_point->id, 'path' => $dir . '/' . $name]
                    ]);
                }
            }

            if($request->has('prod_type_id')){
                if($request->prod_type_id == 2){
                    //store to stock
                    $data = $request->except('token', 'email', 'by', 'prod_number', 'prod_name', 'category_id', 'sub_category_id', 'prod_description', 'prod_type_id', 'prod_status_id', 'prod_modal_price', 'prod_base_price', 'prod_gram', 'principle_id', 'diskon', 'brand_id', 'path','id','warehouse_id_pending');
                    $data['product_point_id'] = $product_point->id;

                    StockProductPoint::create($data);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil Tambah Product Point',
                'data'    => $product_point
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
        ], 500);
        }
    }


    public function get_by_subcategory_id(Request $request, $id = null)
    {
        try {
            if ($id) {
                $product_point = ProductPoint::where('sub_category_id', $request->id)->get();
            } else {
                $product_point = ProductPoint::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product_point,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function get_by_product_point_id(Request $request, $id = null)
    {
        try {
            if ($id) {
                $product_point = ProductPoint::where('id', $request->id)->get();
            } else {
                $product_point = ProductPoint::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product_point,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function get_image(Request $request, $id)
    {
        try {
            if ($id) {
                $product_point = DB::table('product_point_image')->where('product_point_id',$id)->get();
            } else {
                $product_point = DB::table('product_point_image')->get();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product_point,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delete_image(Request $request, $id)
    {
        try {
            $product_point = DB::table('product_point_image')->where('id', $id);
            $product_point->delete();
            $product_point_new = $product_point;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Hapus Product Point',
                'data'    => $product_point
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
