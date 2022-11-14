<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;

use App\User;
use App\Setting;
use App\UserProfile;
use App\LogPoint;
use DB;

use Log;

class UpdatePoint extends Job
{

    public function __construct()
    {
        //
    }

    public function handle()
    {
        //get all user & birth
        $users = User::where('active',1)->whereNotNull('token')->get();
        $setting = Setting::find(1);

        foreach($users as $user){

            $user_id = $user->id;
            $point   = $setting->point_birthday;
            $message = 'Hadiah ulang tahun menambahkan point sebesar <b>'. $point . '</b> ke user dengan id '.$user->id;
            $by		 = $user->id;

            DB::transaction(function () use($user_id, $point, $message, $by){

            	$user_profile = UserProfile::where('user_id', $user_id)->first();

	            $currentPoint = (int)$user_profile->point;
	            $finalPoint   = $currentPoint + $point;

	            $user_profile->point = $finalPoint;
	            $user_profile->save();

	            $log = [
	            	'user_id'		=> $user_id,
	            	'point'			=> $point,
	            	'total_before'	=> $currentPoint,
	            	'total_after'	=> $finalPoint,
	            	'type'			=> '+',
	            	'message'		=> $message,
	            	'by'			=> $by
	            ];

	            LogPoint::create($log);

            });
            Log::info('excecute user-id : '.$user->id);
        }
        
    }
}
