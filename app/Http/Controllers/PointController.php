<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Validator;
use Exception;

use App\User;
use App\UserProfile;
use App\LogPoint;
use App\HistoryInOutPoint;

use App\Jobs\UpdatePoint;
use Log;

class PointController extends Controller
{
    public const MIN = '-', PLUS = '+';

   	public function add(Request $request)
   	{
   		$rules = [
            'user_id' => 'required|regex:/^\S*$/u',
            'point'   => 'required|max:7|regex:/^\S*$/u',
            'message' => 'required|max:150',
        ];

   		try {

   			$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $user_id = $request->user_id;
            $point   = $request->has('point') ? (int)str_replace('.', '', $request->point) : 0;
            $message = $request->message;

            DB::transaction(function () use($user_id, $point, $message){

            	$user = UserProfile::where('user_id', $user_id)->first();

	            $currentPoint = (int)$user->point;
	            $finalPoint   = $currentPoint + $point;

	            $user->point = $finalPoint;
	            $user->save();

	            $log = [
	            	'type'			=> SELF::PLUS,
	            	'user_id'		=> $user_id,
	            	'total'     	=> $point,
	            	'message'		=> $message,
	            ];

                // LogPoint::create($log);
                HistoryInOutPoint::create($log);

            });

            return __jsonResp(true, 'Point berhasil ditambahkan ke user dengan id ' . $user_id , 200, null, $request->all());
            
        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);


        }
    }

    public function trigger_birthday()
    {
        try{

            dispatch(new UpdatePoint)->onQueue('trigger_birthday');
           
            Log::info('Dispatched point ');
           
            return __jsonResp(true, 'Queueing', 200, null);

        }catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
        
    }


    public function history(Request $req) {

		try {

			$user 			= User::where('email',$req->email)->first();


            $point          = DB::table('history_in_out_point AS Z')
                            ->select(
                                'Z.id',
                                'Z.type',
                                DB::raw("
                                CONCAT(
                                    '(', Z.type ,') ', Z.total
                                ) point
                                "),
                                DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y %T") AS date'),
                            )
                            ->where('Z.user_id', $user->id)
                            ->orderBy('Z.created_at', 'DESC')
                            ->get();

			return __jsonResp(true, 'Data Berhasil diambil', 200, null, $point);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
        
	}
}