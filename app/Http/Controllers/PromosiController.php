<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Promosi;
use App\PromosiItem;
use App\Product;
use App\StockProduct;

use Exception;
use Validator;
use DB;

class PromosiController extends Controller
{
    public function dataTable(Request $request)
    {

        try{

            $columns = [null, 'no','id','promosi_name',null,null,'promosi_type',null,null,'total_value','stock_promosi','created_at','updated_at'];

	    	$limit = $request->input('length');
	        $start = $request->input('start');
	        $order = $request->input('order.0.column') == 0 ? 'promosi_type' : $columns[$request->input('order.0.column')];
	        $dir   = $request->input('order.0.dir');

	        $data          = array();
	        $totalData     = Promosi::where('deleted_at', NULL)->count();
	        $totalFiltered = $totalData;
            $posts	       = '';

            // return $totalData;

	    	if(empty($request->input('search.value'))){

                $posts = Promosi::select(
                    'promosi.*',
                    'promosi_info_bundle.info_bundle as info_bundle'
                )
                ->leftJoin('promosi_info_bundle','promosi_info_bundle.id','=','promosi.info_bundle_id')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

	    	}else{

                $search = $request->input('search.value');

                $tb = Promosi::select(
                    'promosi.*',
                    'promosi_info_bundle.info_bundle as info_bundle'
                )
                ->leftJoin('promosi_info_bundle','promosi_info_bundle.id','=','promosi.info_bundle_id')
                ->where('id', 'LIKE', "%{$search}%")
                ->orWhere('promosi_name', 'LIKE', "%{$search}%");

	            $posts = $tb->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

	            $totalFiltered = $tb->count();

	        }

	        if(!empty($posts)){

	        	$no  = $start + 1;
                $row = 0;

                // return $posts;

	        	foreach($posts as $a){
	        		$d['no'] = $no++;
	        		$d['id'] = $a->id;
	        		$d['promosi_name'] = $a->promosi_name;
	        		$d['promosi_image'] = $a->promosi_image;
                    $d['promosi_type'] = $a->promosi_type;
                    $d['info_bundle'] = isset($a->info_bundle) ? $a->info_bundle : null;
                    $d['info_bundle_id'] = isset($a->info_bundle_id) ? $a->info_bundle_id : null;
                    $d['warehouse_id'] = $a->warehouse_id;
                    $d['warehouse_name'] = $a->warehouse->short .'-'. $a->warehouse->name;
                    $d['total_value'] = $a->total_value;
                    $d['start_date_view'] = format_datetime_from_db($a->start_date);
                    $d['end_date_view'] = format_datetime_from_db($a->end_date);
	        		$d['start_date'] = format_date_to_dateonly( $a->start_date);
                    $d['end_date'] = format_date_to_dateonly($a->end_date);
                    $d['start_time'] = format_date_to_timeonly($a->start_date);
                    $d['end_time'] = format_date_to_timeonly($a->end_date);
	        		$d['created_at'] = _customDate($a->created_at);
	        		$d['created_by'] = $a->created_by;
	        		$d['updated_at'] = _customDate($a->updated_at);
                    $d['updated_by'] = $a->updated_by;
                    //$promosi_item = PromosiItem::where('promosi_id',$a->id)->with('product')->orderBy('created_at')->get();
                    //$d['promosi_item'] = $promosi_item;

                    //get total stock promo
                    $total = 0;
                    foreach($a->promosi_item as $sp){
                        $total += floatval($sp->stock_promosi);
                    }
                    $d['stock_promosi'] = $a->promosi_type == 2 ? $total : $a->total_bundle;

	        		$row++;
	        		$data[] = $d;
	        	}

	        }

	        $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

            //echo json_encode($json_data);
            return response()->json($json_data, 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function get(Request $request)
    {
        # code...

        try{

            if($request->id){
                $data = Promosi::where('id',$request->id)->with('warehouse','promosi_item','promosi_item.stock','promosi_item.stock.product')->first();
            }else{
                $data = Promosi::with('warehouse','promosi_item','promosi_item.stock','promosi_item.stock.product')->get();
            }

	        return response()->json([
                'status' => true,
                'data' => $data
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}

    }

    public function get_mobile(Request $request)
    {
        try{

            if($request->has('id')){
                $promosi = Promosi::where('id',$request->id)->with(
                    'warehouse',
                    'bundle',
                    'promosi_type_detail',
                    'promosi_item',
                    'promosi_item.stock',
                    'promosi_item.stock.product',
                    'promosi_item.stock.product.type',
                    'promosi_item.stock.product.brand',
                    'promosi_item.stock.product.category',
                    'promosi_item.stock.product.principle',
                    'promosi_item.stock.product.status',
                    'promosi_item.stock.product.image')
                    ->first();
            }
            else{
                $promosi = Promosi::with(
                    'warehouse',
                    'bundle',
                    'promosi_type_detail',
                    'promosi_item',
                    'promosi_item.stock',
                    'promosi_item.stock.product',
                    'promosi_item.stock.product.type',
                    'promosi_item.stock.product.brand',
                    'promosi_item.stock.product.category',
                    'promosi_item.stock.product.sub_category',
                    'promosi_item.stock.product.principle',
                    'promosi_item.stock.product.status',
                    'promosi_item.stock.product.image')
                    ->get();
            }

            $d = [];
            $p = [];
            $r = [];

            if($request->has('id') && empty($request->has('promosi_item_id')) && isset($promosi)){
                
                if($promosi->promosi_type == 1){
                    //add validasi
                    if($promosi->promosi_item->count() > 0 ){
                        foreach($promosi->promosi_item as $pi){
                            if(empty($pi->stock->product)){
                                continue;
                            }
                        }
                    }
                    //add promosi
                    $d['id'] = $promosi->id;
                    $d["prod_number"] = null;
                    $d["prod_barcode_number"]=null;
                    $d["prod_universal_number"]= null;
                    $d["prod_name"]= $promosi->promosi_name;
                    $d["promosi_price"]= 'Rp '._numberFormat($promosi->total_value);
                    $d["promosi_price_origin"]= $promosi->total_value;
                    $d["prod_base_price"]="";
                    $d["prod_base_origin"]="";
                    //total product prod_gram
                    $d["prod_gram"]="";
                    //total product prod_satuan
                    $d["prod_satuan"]="";
                    //total product principle_id
                    $d["principle_id"]="";
                    //total product principle_id
                    $d["prod_description"]=_add_line("PROMOSI NAME ");

                    $d["brand_id"]="";
            
                    $d["principle_code"]="";
                    $d["principle_name"]="";
                    $d["brand_code"]="";
                    $d["brand_name"]="";
                    $d["sub_category_name"] = "";
                    $d["category_name"] = "";
                    $d["product_type"]="";
                    $d["status_name"]="";
                    $d["category_id"]= null;
                    $d["sub_category_id"]= null;
                    $d["promosi_type_id"]= $promosi->promosi_type;
                    $d["promosi_type_name"]= $promosi->promosi_type_detail->promosi_type;
                    $d["prod_status_id"]= 1;
                    $d["promosi_image"]= isset($promosi->promosi_image) ? $promosi->promosi_image : "assets/product_image/_blank.jpg";
                    //$d["prod_image"]= count($pi->stock->product->image) ? $pi->stock->product->image->first()->path : "assets/product_image/_blank.jpg";
                    $d["min_poin"]= null;
                    $d["start_date"]= $promosi->start_date;
                    $d["end_date"]= $promosi->end_date;
                    $d["created_at"]= $promosi->created_at;
                    $d["created_by"]= $promosi->created_by;
                    $d["updated_at"]= $promosi->updated_at;
                    $d["updated_by"]= $promosi->updated_by;
                    $d["deleted_at"]= $promosi->deleted_at;
                    $d["deleted_by"]= $promosi->deleted_by;
                    $d["warehouse_id"]= $promosi->warehouse_id;
                    $d["warehouse_name"]= $promosi->warehouse->name;
                    $d["prod_warpay"]= 0;
                    $d["info_bundle_id"]= isset($promosi->info_bundle_id) ? $promosi->info_bundle_id :null;
                    $d["info_bundle_name"]= isset($promosi->bundle->info_bundle) ? $promosi->bundle->info_bundle : null;
                    $d["stock"]= $promosi->total_bundle;
                    $d["image"]=[];
                    
                    $d["product"] = [];
                    foreach($promosi->promosi_item as $pi){
                        //total prod_base_price => add multiple by item
                        $d["prod_base_price"].= _add_line('Rp '. _numberFormat($pi->stock->product->prod_base_price));
                        $d["prod_base_origin"].= _add_line('Rp '. _numberFormat($pi->stock->product->prod_base_price));
                        //total product prod_gram => add multiple by item
                        $d["prod_gram"]= $pi->stock->product->prod_gram;
                        //total product prod_satuan => add multiple by item
                        $d["prod_satuan"]= $pi->stock->product->prod_satuan;
                        //total product principle_id => add multiple by item
                        $d["principle_id"]= $pi->stock->product->principle_id;
                        $d["principle_code"]= $pi->stock->product->principle->code;
                        $d["principle_name"]= $pi->stock->product->principle->name;
                        //total product principle_id => add multiple by item
                        $d["prod_description"] .= _add_line('- ('.$pi->stock_promosi .') x '. $pi->stock->product->prod_name);
                        
                        $d["brand_id"] = $pi->stock->product->brand_id;

                        $d["brand_code"] = $pi->stock->product->brand->brand_code;
                        $d["brand_name"] = $pi->stock->product->brand->brand_name;
                        $d["sub_category_name"] = $pi->stock->product->sub_category->category_name;
                        $d["category_name"] = $promosi->promosi_type_detail->promosi_type;
                        $d["product_type"] = $pi->stock->product->type->product_type;
                        $d["status_name"] = $pi->stock->product->status->status_name;
                        
                        $p['promosi_item_id'] = $pi->id;
                        $p["prod_id"] = $pi->stock->product->id;
                        $p["prod_number"] = $pi->stock->product->prod_number;
                        $p["prod_barcode_number"]= $pi->stock->product->prod_barcode_number;
                        $p["prod_universal_number"]= $pi->stock->product->prod_universal_number;
                        $p["prod_name"]= $pi->stock->product->prod_name;
                        $p["prod_base_price"]= 'Rp '._numberFormat($pi->stock->product->prod_base_price);
                        $p["prod_base_price_origin"]= $pi->stock->product->prod_base_price;
                        $p["prod_gram"]= $pi->stock->product->prod_gram;
                        $p["prod_satuan"]= $pi->stock->product->prod_satuan;
                        $p["prod_reguler_id"]= $pi->stock->product->prod_reguler_id;
                        $p["principle_id"]= $pi->stock->product->principle_id;
                        $p["category_id"]= $pi->stock->product->category_id;
                        $p["sub_category_id"]= $pi->stock->product->sub_category_id;
                        $p["prod_description"]= $pi->stock->product->prod_description;
                        $p["promosi_type_id"]= $promosi->promosi_type;
                        $p["prod_status_id"]= $pi->stock->product->prod_status_id;
                        $p["brand_id"]= $pi->stock->product->brand_id;
                        $p["min_poin"]= $pi->stock->product->min_poin;
                        $p["created_at"]= $pi->created_at;
                        $p["created_by"]= $pi->created_by;
                        $p["updated_at"]= $pi->updated_at;
                        $p["updated_by"]= $pi->updated_by;
                        $p["deleted_at"]= $pi->deleted_at;
                        $p["deleted_by"]= $pi->deleted_by;
                        $p["principle_code"]= $pi->stock->product->principle->principle_code;
                        $p["principle_name"]= $pi->stock->product->principle->principle_name;
                        $p["brand_code"]= $pi->stock->product->brand->brand_code;
                        $p["brand_name"]= $pi->stock->product->brand->brand_name;
                        $p["sub_category_name"]= $pi->stock->product->sub_category->category_name;
                        $p["category_name"]= $pi->stock->product->category->category_name;
                        $p["product_type"]= $pi->stock->product->type->product_type;
                        $p["status_name"]= $pi->stock->product->status->status_name;
                        $p["promosi_image"]= isset($promosi->promosi_image) ? $promosi->promosi_image : "assets/product_image/_blank.jpg";
                        $p["prod_image"]= count($pi->stock->product->image) ? $pi->stock->product->image->first()->path : "assets/product_image/_blank.jpg";
                        $p["warehouse_id"]= $promosi->warehouse_id;
                        $p["warehouse_name"]= $promosi->warehouse->name;
                        $p["prod_warpay"]= 0;
                        $p["stock"]= $pi->stock_promosi;
                        $p["image"]=[];
                        $d["product"][] = $p;
                    }
                    //add image
                    $r[] = $d;
                }
                else{
                    //promosi item
                    
                    foreach($promosi->promosi_item as $pi){
                        //add validasi
                        if(empty($pi->stock->product)){
                            continue;
                        }

                        $d['id'] = $promosi->id;
                        $d['promosi_item_id'] = $pi->id;
                        $d['prod_id'] = $pi->stock->product->id;
                        $d["prod_number"] = $pi->stock->product->prod_number;
                        $d["prod_barcode_number"]= $pi->stock->product->prod_barcode_number;
                        $d["prod_universal_number"]= $pi->stock->product->prod_universal_number;
                        $d["prod_name"]= $pi->stock->product->prod_name;
                        $d["prod_base_price"]= 'Rp '._numberFormat($pi->stock->product->prod_base_price);
                        $d["prod_base_price_origin"]= $pi->stock->product->prod_base_price;
                        $d["prod_gram"]= $pi->stock->product->prod_gram;
                        $d["prod_satuan"]= $pi->stock->product->prod_satuan;
                        $d["prod_reguler_id"]= $pi->stock->product->prod_reguler_id;
                        $d["principle_id"]= $pi->stock->product->principle_id;
                        $d["category_id"]= null;
                        $d["sub_category_id"]= null;
                        $d["prod_description"]= _add_line($pi->stock->product->prod_name);
                        $d["promosi_type_id"]= $promosi->promosi_type;
                        $d["promosi_type_name"]= $promosi->promosi_type_detail->promosi_type;
                        $d["prod_status_id"]= $pi->stock->product->prod_status_id;
                        $d["brand_id"]= $pi->stock->product->brand_id;
                        $d["min_poin"]= $pi->stock->product->min_poin;
                        $d["start_date"]= $promosi->start_date;
                        $d["end_date"]= $promosi->end_date;
                        $d["created_at"]= $pi->created_at;
                        $d["created_by"]= $pi->created_by;
                        $d["updated_at"]= $pi->updated_at;
                        $d["updated_by"]= $pi->updated_by;
                        $d["deleted_at"]= $pi->deleted_at;
                        $d["deleted_by"]= $pi->deleted_by;
                        $d["principle_code"]= $pi->stock->product->principle->principle_code;
                        $d["principle_name"]= $pi->stock->product->principle->principle_name;
                        $d["brand_code"]= $pi->stock->product->brand->brand_code;
                        $d["brand_name"]= $pi->stock->product->brand->brand_name;
                        $d["sub_category_name"]= $pi->stock->product->sub_category->category_name;
                        $d["category_name"]= $pi->stock->product->category->category_name;
                        $d["product_type"]= $pi->stock->product->type->product_type;
                        $d["status_name"]= $pi->stock->product->status->status_name;
                        $d["promosi_image"]= isset($promosi->promosi_image) ? $promosi->promosi_image : "assets/product_image/_blank.jpg";
                        $d["prod_image"]= count($pi->stock->product->image) ? $pi->stock->product->image->first()->path : "assets/product_image/_blank.jpg";
                        $d["warehouse_id"]= $promosi->warehouse_id;
                        $d["warehouse_name"]= $promosi->warehouse->name;
                        $d["prod_warpay"]= 0;
                        $d["stock"]= $pi->stock_promosi;
                        $d["info_bundle_id"]= isset($pi->info_bundle_id) ? $pi->info_bundle_id :null;
                        $d["info_bundle_name"]= isset($pi->info_bundle_id) ? $pi->bundle->info_bundle : null;
                        $d["image"]=[];
                        //add image
                        $r[] = $d;
                    } 
                }
            }elseif($request->has('id') && $request->has('promosi_item_id')){
                //promosi item
                //return response()->json([
                //    'status' => true,
                //    'message' => 'Data berhasil diambil.',
                //    'data' => $request->all()
                //], 200);
                //dd($promosi);
                $prom = $promosi;
                foreach($prom->promosi_item as $pi){
                    //add validasi
                    if(empty($pi->stock->product)){
                        continue;
                    }
                    if($pi->id == $request->promosi_item_id){
                        $d['id'] = $prom->id;
                        $d['promosi_item_id'] = $pi->id;
                        $d['prod_id'] = $pi->stock->product->id;
                        $d["prod_number"] = $pi->stock->product->prod_number;
                        $d["prod_barcode_number"]= $pi->stock->product->prod_barcode_number;
                        $d["prod_universal_number"]= $pi->stock->product->prod_universal_number;
                        $d["prod_name"]= $pi->stock->product->prod_name;
                        $d["prod_base_price"]= 'Rp '._numberFormat($pi->stock->product->prod_base_price);
                        $d["prod_base_price_origin"]= $pi->stock->product->prod_base_price;
                        $d["prod_gram"]= $pi->stock->product->prod_gram;
                        $d["prod_satuan"]= $pi->stock->product->prod_satuan;
                        $d["prod_reguler_id"]= $pi->stock->product->prod_reguler_id;
                        $d["principle_id"]= $pi->stock->product->principle_id;
                        $d["category_id"]= 'parcel';
                        $d["sub_category_id"]= 'parcel';
                        $d["prod_description"]= $prom->promosi_name;
                        $d["promosi_type_id"]= $prom->promosi_type;
                        $d["promosi_type_name"]= $prom->promosi_type_detail->promosi_type;
                        $d["prod_status_id"]= $pi->stock->product->prod_status_id;
                        $d["brand_id"]= $pi->stock->product->brand_id;
                        $d["min_poin"]= $pi->stock->product->min_poin;
                        $d["start_date"]= $prom->start_date;
                        $d["end_date"]= $prom->end_date;
                        $d["created_at"]= $pi->created_at;
                        $d["created_by"]= $pi->created_by;
                        $d["updated_at"]= $pi->updated_at;
                        $d["updated_by"]= $pi->updated_by;
                        $d["deleted_at"]= $pi->deleted_at;
                        $d["deleted_by"]= $pi->deleted_by;
                        $d["principle_code"]= $pi->stock->product->principle->principle_code;
                        $d["principle_name"]= $pi->stock->product->principle->principle_name;
                        $d["brand_code"]= $pi->stock->product->brand->brand_code;
                        $d["brand_name"]= $pi->stock->product->brand->brand_name;
                        $d["sub_category_name"]= $pi->stock->product->sub_category->category_name;
                        
                        $d["category_name"] = $prom->promosi_type_detail->promosi_type;
                        $d["product_type"]= $pi->stock->product->type->product_type;
                        $d["status_name"]= $pi->stock->product->status->status_name;
                        $d["promosi_image"]= isset($prom->promosi_image) ? $prom->promosi_image : "assets/product_image/_blank.jpg";
                        $d["prod_image"]= count($pi->stock->product->image) ? $pi->stock->product->image->first()->path : "assets/product_image/_blank.jpg";
                        $d["warehouse_id"]= $prom->warehouse_id;
                        $d["warehouse_name"]= $prom->warehouse->name;
                        $d["prod_warpay"]= 0;
                        $d["stock"]= $pi->stock_promosi;
                        $d["info_bundle_id"]= isset($pi->info_bundle_id) ? $pi->info_bundle_id :null;
                        $d["info_bundle_name"]= isset($pi->info_bundle_id) ? $pi->bundle->info_bundle : null;
                        $d["image"]=[];
                        //add image
                        $r[] = $d;
                    }
                    
                } 
            }else{
                foreach($promosi as $prom){
                    //add validasi
                    if($prom->promosi_item->count() > 0 ){
                        foreach($prom->promosi_item as $pi){
                            if(empty($pi->stock->product)){
                                continue;
                            }
                        }
                    }else{
                        continue;
                    }
                    //add promosi
                    $d['id'] = $prom->id;
                    $d["prod_number"] = null;
                    $d["prod_barcode_number"]=null;
                    $d["prod_universal_number"]= null;
                    $d["prod_name"]= $prom->promosi_name;
                    $d["promosi_price"]= 'Rp '._numberFormat($prom->total_value);
                    $d["promosi_price_origin"]= $prom->total_value;
                    $d["prod_base_price"]="";
                    $d["prod_base_origin"]="";
                    //total product prod_gram
                    $d["prod_gram"]="";
                    //total product prod_satuan
                    $d["prod_satuan"]="";
                    //total product principle_id
                    $d["principle_id"]="";
                    //total product principle_id
                    $d["prod_description"]=_add_line("PROMO NAME ");

                    $d["brand_id"]="";
            
                    $d["principle_code"]="";
                    $d["principle_name"]="";
                    $d["brand_code"]="";
                    $d["brand_name"]="";
                    $d["sub_category_name"] = "";
                    $d["category_name"] = "";
                    $d["product_type"]="";
                    $d["status_name"]="";
                    
                    
                    $d["category_id"]= 'parcel';
                    $d["sub_category_id"]= 'parcel';
                    
                    $d["promosi_type_id"]= $prom->promosi_type;
                    $d["promosi_type_name"]= $prom->promosi_type_detail->promosi_type;
                    $d["prod_status_id"]= 1;
                    $d["promosi_image"]= isset($prom->promosi_image) ? $prom->promosi_image : "assets/product_image/_blank.jpg";
                    //$d["prod_image"]= count($pi->stock->product->image) ? $pi->stock->product->image->first()->path : "assets/product_image/_blank.jpg";
                    
                    $d["min_poin"]= null;
                    $d["start_date"]= $prom->start_date;
                    $d["end_date"]= $prom->end_date;
                    $d["created_at"]= $prom->created_at;
                    $d["created_by"]= $prom->created_by;
                    $d["updated_at"]= $prom->updated_at;
                    $d["updated_by"]= $prom->updated_by;
                    $d["deleted_at"]= $prom->deleted_at;
                    $d["deleted_by"]= $prom->deleted_by;
                    
                    $d["warehouse_id"]= $prom->warehouse_id;
                    $d["warehouse_name"]= $prom->warehouse->name;
                    $d["info_bundle_id"]= isset($prom->info_bundle_id) ? $prom->info_bundle_id :null;
                    $d["info_bundle_name"]= isset($prom->bundle->info_bundle) ? $prom->bundle->info_bundle : null;
                    $d["prod_warpay"]= 0;
                    $d["stock"]= $prom->total_bundle;
                    $d["image"]=[];
                    
                    $d["product"] = [];
                    foreach($prom->promosi_item as $pi){
                        $d["prod_base_price"].= _add_line('Rp '. _numberFormat($pi->stock->product->prod_base_price));
                        $d["prod_base_origin"].= _add_line($pi->stock->product->prod_base_price);
                        //total product prod_gram => add multiple by item
                        $d["prod_gram"]= $pi->stock->product->prod_gram;
                        //total product prod_satuan => add multiple by item
                        $d["prod_satuan"]= $pi->stock->product->prod_satuan;
                        //total product principle_id => add multiple by item
                        $d["principle_id"]= $pi->stock->product->principle_id;
                        $d["principle_code"]= $pi->stock->product->principle->code;
                        $d["principle_name"]= $pi->stock->product->principle->name;
                        //total product principle_id => add multiple by item
                        $d["prod_description"] .= _add_line('- ('.$pi->stock_promosi .') x '. $pi->stock->product->prod_name);
                        
                        $d["brand_id"] = $pi->stock->product->brand_id;
                        $d["brand_code"] = $pi->stock->product->brand->brand_code;
                        $d["brand_name"] = $pi->stock->product->brand->brand_name;
                        $d["sub_category_name"] = $pi->stock->product->sub_category->category_name;
                        $d["category_name"] = $pi->stock->product->category->category_name;
                        $d["product_type"] = $pi->stock->product->type->product_type;
                        $d["status_name"] = $pi->stock->product->status->status_name;
                    
                        $p['promosi_item_id'] = $pi->id;
                        $p['prod_id'] = $pi->stock->product->id;
                        $p["prod_number"] = $pi->stock->product->prod_number;
                        $p["prod_barcode_number"]= $pi->stock->product->prod_barcode_number;
                        $p["prod_universal_number"]= $pi->stock->product->prod_universal_number;
                        $p["prod_name"]= $pi->stock->product->prod_name;
                        $p["prod_base_price"]= 'Rp '._numberFormat($pi->stock->product->prod_base_price);
                        $p["prod_base_price_origin"]= $pi->stock->product->prod_base_price;
                        $p["prod_gram"]= $pi->stock->product->prod_gram;
                        $p["prod_satuan"]= $pi->stock->product->prod_satuan;
                        $p["prod_reguler_id"]= $pi->stock->product->prod_reguler_id;
                        $p["principle_id"]= $pi->stock->product->principle_id;
                        $p["category_id"]= $pi->stock->product->category_id;
                        $p["sub_category_id"]= $pi->stock->product->sub_category_id;
                        $p["prod_description"]= $pi->stock->product->prod_description;
                        $p["promosi_type_id"]= $prom->promosi_type;
                        $p["promosi_type_name"]= $prom->promosi_type_detail->promosi_type;
                        $p["prod_status_id"]= $pi->stock->product->prod_status_id;
                        $p["brand_id"]= $pi->stock->product->brand_id;
                        $p["min_poin"]= $pi->stock->product->min_poin;
                        $p["created_at"]= $pi->created_at;
                        $p["created_by"]= $pi->created_by;
                        $p["updated_at"]= $pi->updated_at;
                        $p["updated_by"]= $pi->updated_by;
                        $p["deleted_at"]= $pi->deleted_at;
                        $p["deleted_by"]= $pi->deleted_by;
                        $p["principle_code"]= $pi->stock->product->principle->principle_code;
                        $p["principle_name"]= $pi->stock->product->principle->principle_name;
                        $p["brand_code"]= $pi->stock->product->brand->brand_code;
                        $p["brand_name"]= $pi->stock->product->brand->brand_name;
                        $p["sub_category_name"]= $pi->stock->product->sub_category->category_name;
                        $p["category_name"] = $prom->promosi_type_detail->promosi_type;
                        $p["product_type"]= $pi->stock->product->type->product_type;
                        $p["status_name"]= $pi->stock->product->status->status_name;
                        $p["promosi_image"]= isset($prom->promosi_image) ? $prom->promosi_image : "assets/product_image/_blank.jpg";
                        $d["prod_image"]= count($pi->stock->product->image) ? $pi->stock->product->image->first()->path : "assets/product_image/_blank.jpg";
                        $p["warehouse_id"]= $prom->warehouse_id;
                        $p["warehouse_name"]= $prom->warehouse->name;
                        $p["prod_warpay"]= 0;
                        $p["stock"]= $pi->stock_promosi;
                        $p["info_bundle_id"]= isset($pi->info_bundle_id) ? $pi->info_bundle_id :null;
                        $p["info_bundle_name"]= isset($pi->info_bundle_id) ? $pi->bundle->info_bundle : null;
                        $p["image"]=[];
                        $d["product"][] = $p;
                    }
                    //add image
                    $r[] = $d;
                    

                }
            }


	        return response()->json([
                'status' => true,
                'message' => 'Data berhasil diambil.',
                'data' => $r
            ], 200);

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
            'warehouse_id'   => 'required',
            'stock_id.*'   => 'required',
            'start_date'   => 'required',
            'end_date'   => 'required',

            'start_date'   => 'required',
            'promosi_type'   => 'required',
        ];

        

        $rules['promosi_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            //inisitalisasi variabel
            $promosi_item_data = [];
            $value_item_data = [];
            $total_harga = 0;

            //fetch data
            $data = $request->except('_token','token', 'email','method', 'promosi_image','stock_id','stock','type','value');

            //modify value data
            $date_start = $request->start_date.' '.$request->start_time;
            $date_end = $request->end_date.' '.$request->end_time;
            $data['start_date'] = format_datetime_to_db($date_start);
            $data['end_date'] = format_datetime_to_db($date_end);
            $data['created_by'] = $request->by;
            $data['total_bundle'] = isset($data['total_bundle']) ? _resolveNumberFormatIDR($data['total_bundle']) : 0;

            //upload file
            if($request->file('promosi_image')) {
                $upload = _uploadFile($request->file('promosi_image'), 'Promosi');
                $data['promosi_image'] = $upload;
            }
            
            //create promosi
            $promosi = Promosi::create($data);

            //fetch data
            $data = $request->except('token', 'email','method', 'promosi_image','promosi_name','start_date','end_date','start_time','end_time','promosi_type','total_bundle','info_bundle_id');

            //get data bundeling and set harga
            foreach($data['value'] as $val){
                if($val == "" ) continue;
                $value_item_data[] = $val;
            }

            foreach($data['stock_id'] as $key => $val){
                //modify value data
                if($request->has('type')){
                    $promosi_item_data['stock_id'] = $val;
                    $promosi_item_data['promosi_id'] = $promosi->id;
                    $promosi_item_data['stock_promosi'] = isset($data['stock']) ? $data['stock'][$key] : null;
                    $promosi_item_data['type'] = $data['type'][$key];
                    $promosi_item_data['value'] = isset($value_item_data[$key]) ? _resolveNumberFormatIDR($value_item_data[$key]) : null;
                    $promosi_item_data['fix_value'] = _resolveNumberFormatIDR($data['fix_value'][$key]);
                    $promosi_item_data['created_by'] = $request->by;
                    $total_harga += isset($data['fix_value']) ? _resolveNumberFormatIDR($data['fix_value'][$key]) : 0;
                }else{
                    $promosi_item_data['stock_id'] = $val;
                    $promosi_item_data['promosi_id'] = $promosi->id;
                    $promosi_item_data['stock_promosi'] = isset($data['stock']) ? $data['stock'][$key] : null;
                    $promosi_item_data['type'] = 1;
                    $promosi_item_data['value'] = 0;
                    $promosi_item_data['fix_value'] = 0;
                    $promosi_item_data['created_by'] = $request->by;
                    $total_harga = isset($data['value']) ? _resolveNumberFormatIDR($data['value'][0]) : 0;

                }
                PromosiItem::create($promosi_item_data);
            }

            //update promosi
            //Promosi::where('id', $promosi->id)->update(['total_value'=>$total_harga]);
            $promosi->update(['total_value'=>$total_harga]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Tambah Promosi',
                'data'=>$request->all()
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
        $rules = [
            'warehouse_id'   => 'required',
            'stock_id.*'   => 'required',
            'start_date'   => 'required',
            'end_date'   => 'required',
            //'value.*'   => 'required',
            'start_date'   => 'required',
            'promosi_type'   => 'required',
        ];

        $rules['promosi_image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';

        try {
                DB::beginTransaction();

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => ucfirst($validator->errors()->first())
                    ], 400);
                }

                //inisitalisasi variabel
                $promosi_item_data = [];
                $value_item_data = [];
                $total_harga = 0;

                $promosi = Promosi::findOrFail($id);

                //fetch data
                $data = $request->except('_token','by','token', 'email','method', 'promosi_image','stock_id','stock','type','value');

                //modify value data
                $date_start = $request->start_date.' '.$request->start_time;
                $date_end = $request->end_date.' '.$request->end_time;
                $data['start_date'] = format_datetime_to_db($date_start);
                $data['end_date'] = format_datetime_to_db($date_end);
                $data['created_by'] = $request->by;
                unset($data['start_time']);
                unset($data['end_time']);
                $data['total_bundle'] = isset($data['total_bundle']) ? _resolveNumberFormatIDR($data['total_bundle']) : 0;

                //upload file
                if($request->file('promosi_image')) {
                    $upload = _uploadFile($request->file('promosi_image'), 'Promosi');
                    $data['promosi_image'] = $upload;
                }

                //update data
                $promosi->update($data);

                //$update_promosi = Promosi::where('id', $id)->update($data);
                //fetch data
                $data = $request->except('token', 'email','method', 'promosi_image','promosi_name','start_date','end_date','start_time','end_time','promosi_type','total_bundle','info_bundle_id');

                PromosiItem::where('promosi_id',$id)->delete();
                //$promosi_items_get = PromosiItem::where('promosi_id','=',$id)->get();

                //get data bundeling and set harga

                foreach($data['value'] as $val){
                    if($val == "" ) continue;
                    $value_item_data[] = $val;
                }

                foreach($data['stock_id'] as $key => $val){
                    //jika item sesuai dengan id item maka update data
                    if($request->has('type')){
                        $promosi_item_data['stock_id'] = $val;
                        $promosi_item_data['promosi_id'] = $id;
                        $promosi_item_data['stock_promosi'] = isset($data['stock']) ? $data['stock'][$key] : null;
                        $promosi_item_data['type'] = $data['type'][$key];
                        $promosi_item_data['value'] = isset($value_item_data[$key]) ? _resolveNumberFormatIDR($value_item_data[$key]) : null;
                        $promosi_item_data['fix_value'] = _resolveNumberFormatIDR($data['fix_value'][$key]);
                        $promosi_item_data['created_by'] = $request->by;
                        $total_harga += isset($data['fix_value']) ? _resolveNumberFormatIDR($data['fix_value'][$key]) : 0;
                    }else{
                        $promosi_item_data['stock_id'] = $val;
                        $promosi_item_data['promosi_id'] = $id;
                        $promosi_item_data['stock_promosi'] = isset($data['stock']) ? $data['stock'][$key] : null;
                        $promosi_item_data['type'] = 1;
                        $promosi_item_data['value'] = 0;
                        $promosi_item_data['fix_value'] = 0;
                        $promosi_item_data['created_by'] = $request->by;
                        $total_harga = isset($data['value']) ? _resolveNumberFormatIDR($data['value'][0]) : 0;
    
                    }
                    PromosiItem::create($promosi_item_data);
                    //jika item sesuai dengan id item maka insert data
                }

                //update data
                $promosi->update(['total_value' => $total_harga]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil Edit Promosi'
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

    public function delete(Request $request, $id){

        try{
            DB::beginTransaction();

            $promosi = Promosi::find($id);
            $promosi->delete();

            PromosiItem::where('promosi_id',$id)->delete();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil Hapus Kategori'
            ], 200);

        }catch(Exception $e){
            DB::rollback();

            return response()->json([
        		'message' => 'Terdapat kesalahan pada sistem internal.',
        		'error'   => $e->getMessage()
        	], 500);

        }
    }

    
}
