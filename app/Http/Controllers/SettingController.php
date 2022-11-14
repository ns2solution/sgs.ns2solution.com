<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Exception;

use App\Setting;

class SettingController extends Controller
{
    public function get()
    {
    	try {

            $setting = Setting::first();

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $setting
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

            if(isset($request->convertion_warpay) && $request->convertion_warpay != ''){

                $request['convertion_warpay'] = str_replace('.', '', $request->convertion_warpay);

            }
            
            if(isset($request->point_birthday) && $request->point_birthday != ''){

                $request['point_birthday'] = str_replace('.', '', $request->point_birthday);

            }

            $setting = Setting::find(1);
            $setting->maintenance_apps = $request->has('maintenance_apps') && $request->maintenance_apps != '' ? $request->maintenance_apps : $setting->maintenance_apps;
            $setting->maintenance_mobile = $request->has('maintenance_mobile') && $request->maintenance_mobile != '' ? $request->maintenance_mobile : $setting->maintenance_mobile;
            $setting->point_birthday = $request->has('point_birthday') && $request->point_birthday != '' ? $request->point_birthday : $setting->point_birthday;
            $setting->convertion_warpay = $request->has('convertion_warpay') && $request->convertion_warpay != '' ? $request->convertion_warpay : $setting->convertion_warpay;
            $setting->save();

            return response()->json([
                'status' => true,
                'message' => 'Pengaturan berhasil diperbarui.',
                'data'    => $setting
            ], 200);

		} catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
