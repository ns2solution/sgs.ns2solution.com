<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Product;
use App\Category;
use App\Place;
use App\StockProduct;
use App\TopProduct;
use App\ProductPoint;

use Exception;
use Validator;

class ProductController extends Controller
{

    public function create_update_top_product(Request $req) {

        $rules = [
            'product_id'          => 'required',
            'active'              => 'required'
        ];

        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
        }

       
        try {

            $top_product = TopProduct::updateOrCreate([
                'product_id'   => $req->product_id
            ],[
                'active'       => $req->active,
            ]);

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $top_product);
            
        } catch (Exception $e) {

            return __jsonResp(false, 'Terdapat kesalahan pada sistem internal.', 500, $e);

        }        

    
    }


    public function create_update_product_point(Request $req) {

        $rules = [
            'product_id'          => 'required',
            'active'              => 'required'
        ];

        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
        }
       
        try {

            $product_point = ProductPoint::updateOrCreate([
                'product_id'   => $req->product_id
            ],[
                'active'       => $req->active,
            ]);

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $product_point);
            
        } catch (Exception $e) {

            return __jsonResp(false, 'Terdapat kesalahan pada sistem internal.', 500, $e);

        }        

    
    }


    // by table
    // public function get_top_product() {

    //     try {

    //         $product = DB::table('product')
    //         ->leftJoin('product_image', function ($join) {
    //             $join->on('product.id', '=', 'product_image.id_product')
    //                  ->on(
    //                     'product_image.id',
    //                     '=',
    //                     DB::raw("(select min(`id`) from product_image where product.id = product_image.id_product)")
    //                  );
    //         })
    //         ->join('top_product AS B', 'B.product_id', '=', 'product.id')
    //         ->where('B.active', 1)
    //         ->select(
    //             'product.*', 'product_image.path AS image'
    //         )->get();

    //         return __jsonResp(true, 'Data Berhasil diambil', 200, null, $product);

    //     } catch (Exception $e) {

    //         return __jsonResp(false, $e->getMessage(), 500, $e);

    //     }

    // }

     // by terlaris
     public function get_top_product() {

        try {

            $product = DB::table('product')
            ->join('order_item AS A', 'A.product_id', '=', 'product.id')
            ->join('order AS B', 'B.id', '=', 'A.order_id')
            ->leftJoin('product_image', function ($join) {
                $join->on('product.id', '=', 'product_image.id_product')
                     ->on(
                        'product_image.id',
                        '=',
                        DB::raw("(select min(`id`) from product_image where product.id = product_image.id_product)")
                     );
            })
            ->select(
                'product.*', 'product_image.path AS image',
                DB::raw('sum(total_item) as total_order ')
            )
            ->whereNotNull('B.user_id')
            ->whereNull('A.deleted_at')
            ->whereNotIn('B.status', [1, 2, 8])
            ->groupBy('product_id')
            ->orderBy('total_order', 'DESC')
            ->get();

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $product);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }

    }


    public function get_product_point() {

        try {

            $product = DB::table('product')
            ->leftJoin('product_image', function ($join) {
                $join->on('product.id', '=', 'product_image.id_product')
                     ->on(
                        'product_image.id',
                        '=',
                        DB::raw("(select min(`id`) from product_image where product.id = product_image.id_product)")
                     );
            })
            ->join('product_point AS B', 'B.product_id', '=', 'product.id')
            ->where('B.active', 1)
            ->select(
                'product.*', 'product_image.path AS image'
            )->get();

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $product);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }

    }


    public function find_top_product($id) {
        
        try {

            if($id) {
                
                $top_product = TopProduct::where('product_id', $id)->first();
                
                if(!$top_product) {

                    return __jsonResp(false, 'Data tidak ada', 200, null, $top_product);
                
                }

                return __jsonResp(true, 'Data Berhasil diambil', 200, null, $top_product);

            } else {

                return __jsonResp(false, 'ID tidak diketahui', 200, null, $top_product);
            
            }
            
        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }        

    
    }


    public function find_product_point($id) {
        
        try {

            if($id) {
                
                $product = DB::table('product')
                    ->leftJoin('product_image', function ($join) {
                        $join->on('product.id', '=', 'product_image.id_product')
                            ->on(
                                'product_image.id',
                                '=',
                                DB::raw("(select min(`id`) from product_image where product.id = product_image.id_product)")
                            );
                    })
                    ->join('product_point AS B', 'B.product_id', '=', 'product.id')
                    ->where('B.active', 1)
                    ->where('product.id', $id)
                    ->select(
                        'product.*', 'product_image.path AS image'
                    )->first();

                
                if(!$product) {

                    return __jsonResp(false, 'Data tidak ada', 200, null, $product);
                
                }

                return __jsonResp(true, 'Data Berhasil diambil', 200, null, $product);

            } else {

                return __jsonResp(false, 'ID tidak diketahui', 200, null, $product);
            
            }
            
        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }        

    
    }

    public function get($id = null)
    {
        try {
            if ($id) {
                $product = Product::findOrFail($id);
            } else {
                $product = Product::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function get_by_product_id(Request $request, $id = null)
    {
        try {
            if ($id) {
                $product = Product::where('id', $request->id)->get();
            } else {
                $product = Product::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function get_by_subcategory_id(Request $request, $id = null)
    {
        try {
            if ($id) {
                $product = Product::where('sub_category_id', $request->id)->get();
            } else {
                $product = Product::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product,
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
                $product = DB::table('product_image')->where('id_product',$id)->get();
            } else {
                $product = DB::table('product_image')->get();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $product,
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
            $columns = [null, null, 'id', 'prod_number', 'prod_name', 'b_category_name', 'prod_base_price', 'prod_gram', 'c_principle_name', 'd_brand_name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Product::count();
            $totalFiltered = $totalData;
            $posts	       = '';

            if (empty($request->input('search.value'))) {
                $posts = Product::leftJoin('category AS b', 'product.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product.*',
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

                $tb = Product::where('product.id', 'LIKE', "%{$search}%")
                ->orWhere('product.prod_name', 'LIKE', "%{$search}%")
                ->orWhere('product.prod_number', 'LIKE', "%{$search}%")
                ->orWhere('product.prod_gram', 'LIKE', "%{$search}%")
                ->orWhere('b.category_name', 'LIKE', "%{$search}%")
                ->orWhere('c.name', 'LIKE', "%{$search}%")
                ->orWhere('d.brand_name', 'LIKE', "%{$search}%")
                ->orWhere('e.fullname', 'LIKE', "%{$search}%")
                ->orWhere('f.fullname', 'LIKE', "%{$search}%")
                ->leftJoin('category AS b', 'product.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product.*',
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
                    $d['min_poin'] = $a->min_poin;
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

    public function dataTable2(Request $request)
    {
        try {
            $columns = [null, null, 'id', 'prod_number', 'prod_name', 'b_category_name', 'prod_base_price', 'prod_gram', 'c_principle_name', 'd_brand_name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Product::count();
            $totalFiltered = $totalData;
            $posts	       = '';

            if (empty($request->input('search.value'))) {
                $posts = Product::leftJoin('category AS b', 'product.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product.*',
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

                $tb = Product::where('product.id', 'LIKE', "%{$search}%")
                ->orWhere('product.prod_name', 'LIKE', "%{$search}%")
                ->orWhere('product.prod_number', 'LIKE', "%{$search}%")
                ->orWhere('product.prod_gram', 'LIKE', "%{$search}%")
                ->orWhere('b.category_name', 'LIKE', "%{$search}%")
                ->orWhere('c.name', 'LIKE', "%{$search}%")
                ->orWhere('d.brand_name', 'LIKE', "%{$search}%")
                ->orWhere('e.fullname', 'LIKE', "%{$search}%")
                ->orWhere('f.fullname', 'LIKE', "%{$search}%")
                ->leftJoin('category AS b', 'product.category_id', '=', 'b.id')
                ->leftJoin('principles AS c', 'product.principle_id', '=', 'c.id')
                ->leftJoin('brands AS d', 'product.brand_id', '=', 'd.id_brand')
                ->leftJoin('users AS e', 'product.created_by', '=', 'e.id')
                ->leftJoin('users AS f', 'product.updated_by', '=', 'f.id')
                ->leftJoin('category AS sub', 'product.sub_category_id', '=', 'sub.id')
                ->leftJoin('product_type', 'product.prod_type_id', '=', 'product_type.id')
                ->select(
                    'product.*',
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

                    $is_top_product = false;
                    $top_product = TopProduct::where('product_id', $a->id)->first(); 
    
                    if($top_product) {

                        if($top_product->active == 1) {
                            $is_top_product =  true;
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
                    $d['min_poin'] = $a->min_poin;
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
                    $d['is_top_product'] = $is_top_product;

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

    public function get_mobile(Request $request)
    {
        try{

            $product = DB::table('product');

            $product->leftJoin('principles AS A',   'A.id', '=', 'product.principle_id')
            ->leftJoin('brands         AS B', 'B.id_brand',   '=', 'product.brand_id')
            ->leftJoin('category       AS C', 'C.id',         '=', 'product.sub_category_id')
            ->leftJoin('category       AS D', 'D.id',         '=', 'C.parent_id')
            ->leftJoin('product_type   AS E', 'E.id',         '=', 'product.prod_type_id')
            ->leftJoin('product_status AS F', 'F.id',         '=', 'product.prod_status_id')
            ->leftJoin('settings', function ($join) {
                $join->on('settings.id', '=', DB::raw("'" . 1 . "'"));
            })
            ->leftJoin('warehouse', function ($join) use ($request) {
                $join->on('warehouse.id', '=', DB::raw("'" . $request->warehouse_id . "'"));
            })
            ->leftJoin('master_stock', function ($join) use ($request) {
                $join->on('master_stock.warehouse_id', '=', DB::raw("'" . $request->warehouse_id . "'"));
                $join->on('master_stock.product_id',   '=', 'product.id');
            })
            ->leftJoin('product_image', function ($join) {
                $join->on('product.id', '=', 'product_image.id_product')
                     ->on(
                        'product_image.id',
                        '=',
                        DB::raw("(select min(`id`) from product_image where product.id = product_image.id_product)")
                     );
            })
            ->select(
                'product.*',
                DB::raw("CONCAT('Rp ', FORMAT(product.prod_base_price, 0, 'de_DE')) AS prod_base_price"),
                'A.code AS principle_code',             'A.name AS principle_name',
                'B.code AS brand_code',                 'B.brand_name',
                'C.category_name AS sub_category_name', 'C.id AS sub_category_id',
                'D.category_name',                      'D.id AS category_id',
                'E.product_type',
                'F.status_name',
                DB::raw('IFNULL( product_image.path, "assets/product_image/_blank.jpg") as prod_image'),
                'warehouse.id       AS warehouse_id', 'warehouse.name AS warehouse_name',
                DB::raw('FLOOR(product.prod_base_price / settings.convertion_warpay) as prod_warpay'),
                DB::raw('IFNULL( master_stock.stock, 0) as stock')
            )
            ->where('product.prod_type_id', $request->prod_type_id)
            ->where('product.prod_status_id', '1')
            ->where('product.deleted_at', '=', null)
            ->where('master_stock.deleted_at', '=', null);

            if(isset($request->subcategory_id) && $request->subcategory_id != ''){

                $product->where('sub_category_id', $request->subcategory_id);

            }

            if(isset($request->product_id) && $request->product_id != ''){

                $product->where('product.id', $request->product_id);

            }

            if(isset($request->prod_name) && $request->prod_name != ''){

                // $product->whereRaw('SOUNDEX(product.prod_name)', 'SOUNDEX(' . $request->prod_name . ')');
                $product->where('product.prod_name', 'LIKE', '%' . $request->prod_name . '%');
            }

            $prod = $product->orderBy('product.prod_name', 'ASC')->get();

            if($prod){
                foreach($prod as &$pr){
                    $gambar = [];
                    $image = DB::table('product_image')
                                ->select('path')
                                ->where('deleted_at','=',null)
                                ->where('id_product',$pr->id)
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

            $product = Product::create($request->except('token', 'email', 'by', 'path','id','warehouse_id','stock','warehouse_id_pending'));
            // $product->id;
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
                    $insertImage = DB::table('product_image')->insert([
                        ['id_product' => $product->id, 'path' => $dir . '/' . $name]
                    ]);
                }
            }

            if($request->has('prod_type_id')){
                if($request->prod_type_id == 2){
                    //store to stock
                    $data = $request->except('token', 'email', 'by', 'prod_number', 'prod_name', 'category_id', 'sub_category_id', 'prod_description', 'prod_type_id', 'prod_status_id', 'prod_modal_price', 'prod_base_price', 'prod_gram', 'principle_id', 'diskon', 'brand_id', 'path','id','warehouse_id_pending');
                    $data['product_id'] = $product->id;

                    // StockProduct::create($data);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Berhasil Tambah Product',
                'data'    => $product
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
            $product = Product::where('id', $id)->update($request->except('token', 'email', 'by', 'path','id','warehouse_id','stock','warehouse_id_pending'));

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
                    $insertImage = DB::table('product_image')->insert([
                        ['id_product' => $id, 'path' => $dir . '/' . $name]
                    ]);
                }
            }

            
            if($request->has('prod_type_id')){
                if($request->prod_type_id == 2){
                    //store to stock
                    //cek stock with product id
                    $data = $request->except('token', 'email', 'by', 'prod_number', 'prod_name', 'category_id', 'sub_category_id', 'prod_description', 'prod_type_id', 'prod_status_id', 'prod_modal_price', 'prod_base_price', 'prod_gram', 'principle_id', 'diskon', 'brand_id', 'path','id','warehouse_id_pending');
                    $data['product_id'] = $id;
                    // $stockProduct = StockProduct::where([['product_id','=',$id],['warehouse_id','=',$request->warehouse_id_pending]]);
                    
                    // $stockProduct->update($data);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Update Product',
                'data' => $product
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
            $product = DB::table('product')->where('id', $id);
            $product->delete();
            $product_new = $product;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Hapus Product',
                'data'    => $product
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'status' => false,
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }

    public function delete_image(Request $request, $id)
    {
        try {
            $product = DB::table('product_image')->where('id', $id);
            $product->delete();
            $product_new = $product;

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Hapus Product',
                'data'    => $product
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'status' => false,
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }

    

    public function getRequest(Request $request)
    {

    	try{


			if($request->has('id')){

                $data = Product::where([
                    ['id','=',$request->id],
                    ['deleted_at','=',NULL]
                ])
                ->with('stock','stock.warehouse','type')
                ->first();
            
            }elseif($request->has('id') && $request->has('warehouse_id')){
                
                $data = Product::where(function (Builder $query) {
                    return $query->where([
                        ['deleted_at','=',NULL],
                        ['master_stock.warehouse_id', '=', $request->warehouse_id],
                        ['master_stock.product_id', '=', $request->id],
                        ['prod_type_id', '=', 2] // Type Promo
                    ]);
                })
                ->with('stock','stock.warehouse','type')
                ->get();

            } else if($request->has('prod_type_id')) {
                
                $data = Product::where([
                    ['prod_type_id','=',$request->prod_type_id]
                ])
                ->with('stock','stock.warehouse','type')
                ->get();

                // return $data;
            
            } else{
            
                $data = Product::where([
                    ['deleted_at','=',NULL],
                    ['prod_type_id', '=', 2] // Type Promo
                ])
                ->with('stock','stock.warehouse','type')
                ->get();
            
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
}
