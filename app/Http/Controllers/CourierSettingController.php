<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Validator;
use Exception;

use App\Courier;
use App\CourierSetting;
use App\CourierService;
use App\CourierServiceSetting;

class CourierSettingController extends Controller
{
    public function get($id = null)
    {
    	try {

            if($id){
                $courier = Courier::findOrFail($id);
            }else{
                $courier = Courier::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $courier
            ], 200);
            
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function getSetting($warehouse_id)
    {
    	try {

    		$data = [];

    		$GET_COURIER = Courier::all();

    		foreach($GET_COURIER as $index => $a){

    			$GET_SETTING = CourierSetting::where([
    				'warehouse_id' => $warehouse_id,
    				'courier_id'   => $a->id
    			])->first();

    			$temp = [
    				'id'           => '',
    				'warehouse_id' => (int)$warehouse_id,
    				'courier_id'   => $a->id,
    				'value'        => 'off',
    				'courier_name' => $a->name,
    				'courier_logo' => $a->logo
    			];

    			if($GET_SETTING){

    				$temp['id']    = $GET_SETTING->id;
    				$temp['value'] = $GET_SETTING->value;

    			}

    			array_push($data, $temp);

    		}

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $data
            ], 200);
            
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }


	public function getService($courier_id, $warehouse_id)
    {
    	try {

    		$data = [];

			$GET_COURIER_SERVICE = CourierService::where('courier_id', $courier_id)->get();

    		foreach($GET_COURIER_SERVICE as $index => $a){

    			$GET_SETTING = CourierServiceSetting::where([
    				'warehouse_id' => $warehouse_id,
    				'courier_service_id'   => $a->id
				])->first();
				
				
    			$temp = [
    				'id'           => '',
    				'warehouse_id' => (int)$warehouse_id,
    				'courier_service_id'   => $a->id,
    				'value'        => 'off',
    				'courier_service_code' => $a->service_code,
    				'courier_service_name' => $a->service_name
    			];

    			if($GET_SETTING){

    				$temp['id']    = $GET_SETTING->id;
    				$temp['value'] = $GET_SETTING->value;

    			}

    			array_push($data, $temp);

    		}

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $data
            ], 200);
            
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }


	public function updateService(Request $request) {
		
		try {

    			CourierServiceSetting::updateOrCreate([
    				'courier_service_id'   => $request->courier_service_id,
    				'warehouse_id' => $request->warehouse_id
    			],[
    				'value'		   => $request->value
    			]);

				return response()->json([
					'message' => 'Pengaturan berhasil disimpan.',
					'data'    => $request->all()
				], 200);

    	} catch (Exception $e) {

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
	}

    public function update(Request $request)
    {
    	try {

    		$GET_COURIER = Courier::all();

    		foreach($GET_COURIER as $index => $a){

    			$value = 'off';

    			if($request->{'cb_' . $a->id}){
    				$value = $request->{'cb_' . $a->id};
    			}

    			CourierSetting::updateOrCreate([
    				'courier_id'   => $a->id,
    				'warehouse_id' => $request->warehouse_id
    			],[
    				'value'		   => $value
    			]);

    		}

    		return response()->json([
                'message' => 'Pengaturan berhasil disimpan.',
                'data'    => $request->all()
            ], 200);

    	} catch (Exception $e) {

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }
}
