<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Validator;
use Exception;

use App\Promosi;
use App\PromosiItem;
use App\PromosiType;
use App\PromosiInfoBundle;
use App\Setting;

class PromosiNewController extends Controller
{
	private function __getTimeNow()
    {
        return Carbon::now('Asia/Jakarta');
    }

    private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
	}

    public function GET_List(Request $request)
    {
    	try{

    		$now = $this->__getTimeNow();

    		$data = Promosi::select(
    			'promosi.id', 'promosi.promosi_name AS name', 'promosi.promosi_image AS image', 'promosi.promosi_type AS type_id',
    			'A.promosi_type AS type',
    			'B.id AS warehouse_id', 'B.name AS warehouse'
    		)
    		->leftJoin('promosi_type AS A', 'A.id', '=', 'promosi.promosi_type')
    		->leftJoin('warehouse    AS B', 'B.id', '=', 'promosi.warehouse_id')
    		->where('promosi.start_date', '<=', $now)
    		->where('promosi.end_date',   '>=', $now);

    		if(isset($request->promotion_id)){
    			$data = $data->where('promosi.id', $request->promotion_id);
    		}

    		$data = $data->get();

    		if(count($data) === 0){
    			return response()->json([
    				'message' => 'Promotion not found.',
    				'data'    => '0'
    			], 200);
    		}

    		return response()->json([
                'message' => 'OK.',
                'data'    => $data
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
	}
	

	public function find($id) {

		try {

            $promosi = Promosi::find($id);

            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $promosi);
            
        } catch (Exception $e) {

            return __jsonResp(false, 'Terdapat kesalahan pada sistem internal.', 500, $e);

        }    
	}

    public function GET_Item(Request $request, $product_id = null)
    {
    	try{

    		$now = $this->__getTimeNow();

    		$CHECK_PROMOTION_TYPE = Promosi::find($request->promotion_id);

    		if($CHECK_PROMOTION_TYPE){

    			$data = Promosi::leftJoin('promosi_item   AS A', 'A.promosi_id', '=', 'promosi.id')
	    		->leftJoin('master_stock   AS B', 'B.id',         '=', 'A.stock_id')
	    		->leftJoin('product        AS C', 'C.id',         '=', 'B.product_id')
	    		->leftJoin('principles     AS D', 'D.id',         '=', 'C.principle_id')
	    		->leftJoin('brands         AS E', 'E.id_brand',   '=', 'C.brand_id')
	            ->leftJoin('category       AS F', 'F.id',         '=', 'C.sub_category_id')
	            ->leftJoin('category       AS G', 'G.id',         '=', 'F.parent_id')
	            ->leftJoin('product_type   AS H', 'H.id',         '=', 'C.prod_type_id')
	            ->leftJoin('product_status AS I', 'I.id',         '=', 'C.prod_status_id')
	            ->leftJoin('warehouse      AS J', 'J.id',         '=', 'B.warehouse_id')
	            ->leftJoin('promosi_type   AS K', 'K.id',         '=', 'promosi.promosi_type')
	            ->leftJoin('settings', function ($join) {
	                $join->on('settings.id', '=', DB::raw("'" . 1 . "'"));
	            })
	            ->leftJoin('product_image', function ($join) {
	                $join->on('C.id', '=', 'product_image.id_product')
	                     ->on(
	                        'product_image.id',
	                        '=',
	                        DB::raw("(select min(`id`) from product_image where C.id = product_image.id_product)")
	                     );
	            })
	            ->select(
	            	'promosi.start_date', 'promosi.end_date',
	    			'C.*',
	                DB::raw("CONCAT('Rp ', FORMAT(C.prod_base_price, 0, 'de_DE')) AS prod_base_price"),
	                'C.prod_base_price AS prod_base_price_ori',
	                'D.code AS principle_code', 'D.name AS principle_name',
	                'E.code AS brand_code', 'E.brand_name',
	                'F.category_name AS sub_category_name', 'F.id AS sub_category_id',
	                'G.category_name', 'G.id AS category_id',
	                'H.product_type',
	                'I.status_name',
	                DB::raw('IFNULL( product_image.path, "assets/product_image/_blank.jpg") as prod_image'),
	                'J.id AS warehouse_id', 'J.name AS warehouse_name',
	                DB::raw('FLOOR(C.prod_base_price / settings.convertion_warpay) as prod_warpay'),
	                DB::raw('IFNULL( A.stock_promosi, 0) as stock'),
	                DB::raw("CONCAT('Rp ', FORMAT(A.fix_value, 0, 'de_DE')) AS prod_promo_price"),
	                DB::raw('FLOOR(A.fix_value / settings.convertion_warpay) as prod_promo_warpay'),
	                'promosi.id AS promosi_id', 'promosi.promosi_name',
	                'A.stock_promosi AS promosi_stock',
	                'K.promosi_type AS promosi_info'
	    		)
	    		->where('promosi.start_date', '<=', $now)
	    		->where('promosi.end_date',   '>=', $now)
	    		->where('promosi.id', $request->promotion_id)
	    		->where('C.prod_type_id', '2') // Tipe Promo
	    		->where('C.prod_status_id', '1') // Status Aktif
				->where('C.deleted_at', '=', null)
				->where('A.deleted_at', '=', null)
	            ->orderBy('C.prod_name', 'ASC');

	            if($product_id !== null){
	            	$data = $data->where('C.id', $product_id);
	            }

				if($request->prod_name){
	            	$data = $data->where('C.prod_name', 'LIKE', "%{$request->prod_name}%");
	            }
				

	            $data = $data->get();

	    		if($data){
	                foreach($data as &$pr){

	                    $gambar = [];

	                    $image  = DB::table('product_image')
	                                ->select('path')
	                                ->where('deleted_at', '=', null)
	                                ->where('id_product', $pr->id)
	                                ->get();

	                    if(empty($image)){
	                        $pr->image = '';
	                    }else{
	                        foreach($image as $im){
	                            array_push($gambar, url(''). '/' .$im->path);
	                        }
	                        $pr->image = $gambar;
	                    }

	                    Carbon::setLocale('id');

	                    $pr['time_remaining'] = Carbon::parse($pr->end_date)->diffForhumans(['parts' => 2]);
	                    $pr['time_remaining'] = str_replace('dari sekarang', 'lagi', $pr['time_remaining']);

	                }
	            }
    			
    			if($CHECK_PROMOTION_TYPE->promosi_type === 1){

    				/*
						==============================================================================
						---------------------------------- BUNDLING ----------------------------------
						==============================================================================
    				*/

					$temp = $data[0];

					$__ARR = [
						'prod_modal_price', 'prod_base_price', 'prod_base_price_ori',
						'prod_gram', 'prod_description', 'prod_number', 'prod_barcode_number',
						'prod_universal_number', 'principle_id', 'category_id',
						'sub_category_id', 'prod_reguler_id', 'brand_id', 'diskon', 'min_poin',
						'principle_code', 'principle_name', 'brand_code', 'brand_name',
						'sub_category_name', 'category_name', 'prod_image', 'image'
					];

					foreach($__ARR as $a){
						$temp[$a] = null;
					}

					$temp['category_name']	  = $temp['product_type'];
					$temp['prod_description'] = $temp['promosi_name'] . "\n";
					$temp['prod_image']		  = $CHECK_PROMOTION_TYPE->promosi_image;

					foreach($data as $index => $a){

						$temp['prod_modal_price']    += $a->prod_modal_price;
						$temp['prod_base_price_ori'] += $a->prod_base_price_ori;
						$temp['prod_gram']			 += $a->prod_gram;

						$temp['prod_description'] .= "\n" . ($index + 1) . '. ' . $a->prod_name . ' (' . $a->promosi_stock . ')';

					}

					$temp['prod_name']	       = $temp['promosi_name'];
					
					$temp['prod_base_price']   = 'Rp ' . str_replace(',', '.', number_format($temp['prod_base_price_ori']));
					$temp['prod_warpay']	   = floor((int)$temp['prod_base_price_ori'] / $this->__convertionWarpay());

					$temp['prod_promo_price']  = 'Rp ' . str_replace(',', '.', number_format($CHECK_PROMOTION_TYPE->total_value));
					$temp['prod_promo_warpay'] = floor((int)$CHECK_PROMOTION_TYPE->total_value / $this->__convertionWarpay());

					$temp['stock'] = $CHECK_PROMOTION_TYPE->total_bundle;
					$temp['promosi_info'] = PromosiInfoBundle::find($CHECK_PROMOTION_TYPE->info_bundle_id)->info_bundle;

					$data = [$temp];

    			}

    		}else{

    			return response()->json([
    				'message' => 'Promotion not found.',
    				'data'    => '0'
    			], 404);

    		}

    		return response()->json([
                'message' => 'OK.',
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