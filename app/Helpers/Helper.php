<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\ErrorLog;
use App\Setting;
use App\UserProfile;
use App\HistoryInOutPoint;
use GuzzleHttp\Client as GuzzleClient;

/*
	-------------------------
	START SEN ENCRYPTION V1
	author: sndjrf
	FREE BROK :p
	-------------------------
*/

function _________keyEncryption($param = null)
{
	$key = ['f', 'r', 'o', 'n', 't', 'c', 'm', 'e', 'z', 's'];
	$sym = [
			'a', 'b', 'd', 'g', 'h', 'i', 'j', 'k', 'l', 'p', 'q', 'u', 'v', 'w', 'x', 'y',
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
		   ];

	if($param == 'sym'){
		return $sym;
	}else{
		return $key;
	}
}

function _encryptNumber($str)
{
	$data = str_split($str);
	$temp = null;

	$key = _________keyEncryption();
	$sym = _________keyEncryption('sym');

	foreach($data as $a){
		$seq = rand(4, 8);
		for($x = 0; $x <= $seq; $x++){
			$ran = rand(0, 50);
			$ran < 25 ? $ron = strtoupper($sym[rand(0, 25)]) : $ron = strtolower($sym[rand(0, 25)]);
			$temp = $temp . $ron;
		}

		$temp .= $key[$a];

		$seq = rand(4, 8);
		for($x = 0; $x <= $seq; $x++) {$temp = $temp . $sym[rand(0, 25)]; }
	}

	$data = $temp;
	return $data;
}

function round_up($num)
{
	return (int)$num + 1;
}

function _decryptNumber($str)
{
	$data = str_split(strtolower($str));
	$temp = null;

	$key = _________keyEncryption();

	foreach($data as $a){
		in_array($a, $key) ? $temp .= array_search($a, $key) : '';
	}

	$data = $temp;
	return $data;
}

/*
	-------------------------
	END FUNCTION
	-------------------------
*/

function _generateToken($length = 50)
{
    $c 	  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cl	  = strlen($c);
    $data = '';

    for ($i = 0; $i < $length; $i++) {
        $data .= $c[rand(0, $cl - 1)];
    }

    return $data;
}

function _randomAlpha($length = 2)
{
    $c 	  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cl	  = strlen($c);
    $data = '';

    for ($i = 0; $i < $length; $i++) {
        $data .= $c[rand(0, $cl - 1)];
    }

    return $data;
}

function _generateOTP($length = 4)
{
    $c 	  = '0123456789';
    $cl	  = strlen($c);
    $data = '';

    for ($i = 0; $i < $length; $i++) {
        $data .= $c[rand(0, $cl - 1)];
    }

    $data = _encryptNumber($data);

    return $data;
}

function _time()
{
	$time = Carbon::now()->format('Y.m.d H:i:s');

	return $time;
}

function ms($arg){
    return json_encode($arg);
}

function _uploadFile($file, $path)
{
	$dir  = 'assets/' . $path;

	if(!File::exists($dir)){
		File::makeDirectory($dir);
	}

	$name = sha1($file.time()) . '.' . $file->getClientOriginalExtension();
	$file->move($dir, $name);

    chmod($dir . '/' . $name, 775);
	$data = $dir . '/' . $name;

	return $data;
}

function _customDate($dt)
{
	if(!$dt){
		return '';
	}

	$month = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

	$tme = explode(' ', $dt);

	$waktu = explode(':', $tme[1]);
	$waktu = $waktu[0] . ':' . $waktu[1];

	$dt  = $tme[0];

	$con = explode('-', $dt);
	$con = $con[2] . ' ' . $month[(int)$con[1]] . ' ' . $con[0] . ' - ' . $waktu;

	return $con;
}

function _setCustomDate($dt)
{
	$con = strtotime(str_replace('/', '-', $dt));
	$res = date('d-m-Y', $con);

	return $res;
}

function _getBase64Size($base64Image)
{
	$size_in_bytes = (int) (strlen(rtrim($base64Image, '=')) * 3 / 4);
	$size_in_kb    = $size_in_bytes / 1024;
	$size_in_mb    = $size_in_kb / 1024;

	return $size_in_mb;
}

function _getOptionImageExtension()
{
	return [
		'jpg',
		'jpeg',
		'png'
	];
}

function _getOptionImageSize()
{
	return 2; // 2 MB
}

function _numberFormat($val)
{
	$val = number_format($val);
	$val = str_replace(',', '.', $val);

	return $val;
}


function _resolveNumberFormatIDR($val)
{
	$val = str_replace('.', '', $val);
	$val = str_replace(',', '.', $val);
	$val = str_replace('Rp.', '', $val);
	$val = str_replace('Rp', '', $val);
	//$val = number_format($val);
	return $val;
}

function format_datetime_to_db($date)
{
	# code...
	return Carbon::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d H:i');
}

function format_datetime_from_db($date)
{
	# code...
	return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y H:i:s');
}

function format_date_to_timeonly($date)
{
	# code...
	return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('H:i');
}

function format_date_to_dateonly($date)
{
	# code...
	return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y');
}

function format_date_to_timeonly_expt_s($date)
{
	# code...
	return Carbon::createFromFormat('Y-m-d H:i:', $date)->format('H:i');
}

function format_date_to_dateonly_expt_s($date)
{
	# code...
	return Carbon::createFromFormat('Y-m-d H:i', $date)->format('d/m/Y');
}

function get_date_now()
{
	# code...
	return Carbon::now()->format('d/m/Y');
}

function get_time_now()
{
	# code...
	return Carbon::now()->format('H:i:s');
}

function _add_line($str)
{
	$str = $str . addslashes("\n"). ' ';
	return $str;
}

function _con($key)
{
	$config = [
		'RAJAONGKIR-URL' => 'https://pro.rajaongkir.com/',
		'RAJAONGKIR-KEY' => '6d31abba199c1322be67a36ac770f812'
	];

	return $config[$key];
}

function _log($param) {
	return \Log::info($param);
}


function __jsonResp($bool = false, $msg, $code = 200, $catc_err = null, $data = null) {

	if($code != 200) {

		\Log::info($catc_err);

		return response()->json([
			'status' 	=> $bool,
			'message' 	=> $msg,
			'error'   	=> $catc_err
		], $code);
	
	}

	return response()->json([
		'status' => $bool,
		'message' => $msg,
		'data'	  => $data
	], $code);

}


function __error($data)
{
	$data = [
		'path'     => explode("\\", explode('@', app('Illuminate\Http\Request')->route()[1]['uses'])[0])[3],
		'method' => $data[0],
		'error'	   => $data[1]
	];

	ErrorLog::insert($data);
}


function __kelipatanPoint($user_id, $amount, $type_bayar = null) {
	
	$title = 'Penambahan Point';
	$uri = 'https://www.decathlon.co.id/img/cms/delivery-page.jpg';
	$content = '';
	$headers = [
		'postman-token' => 'fec7c571-ed63-9fee-cae9-114c274da178',
		'cache-control' => 'no-cache',
		'authorization' => 'key=AAAAlM_TrXo:APA91bG2kdsO6suukbSUOqFKezu82l9QudE833edorq2RV0LDHBMeyHgpwcA9DvTRTTqcVyOrnpaxvl8Us-nFfDKDiyF7YWjPv-WPrAk3Sstv39sybUAt_kPMm3SLXMujhg4nCrgIMTU',
		'content-type' => 'application/json'
	];
	
	$client = new GuzzleClient([
		'headers' => $headers
	]);
	

	$point = [50, 100, 150 ];
	$set_price = [500000, 1000000, 1500000];
	$total = ( $type_bayar == 'WARPAY' ) ? (int) ( $amount * Setting::first()->convertion_warpay ) : (int) $amount;
	$profile = UserProfile::where('user_id', $user_id)->first();
	$device_token = $profile->user->device_token;
	$current_point =  $profile->point;
	$grab_point = 0 ;
	$grab_price = 0;
	
	if ($total >= $set_price[0] && $total < $set_price[1]):
		
		$profile->point =  ( $current_point + $point[0] );
		$profile->save();

		$content = 'Penambahan Point sebesar '.$point[0].' ke akunmu';
		$grab_point = $point[0];
		$grab_price = $set_price[0];

	elseif ($total >= $set_price[1] && $total < $set_price[2]):

		$profile->point =  ( $current_point + $point[1] );
		$profile->save();

		$content = 'Penambahan Point sebesar '.$point[1].' ke akunmu';
		$grab_point = $point[1];
		$grab_price = $set_price[1];

	elseif ($total >= $set_price[2]):

		$profile->point =  ( $current_point + $point[2] );
		$profile->save();

		$content = 'Penambahan Point sebesar '.$point[2].' ke akunmu';
		$grab_point = $point[2];
		$grab_price = $set_price[2];

	else:

	endif;

	if($grab_point > 0) {

		HistoryInOutPoint::create(['type' => '+', 'user_id' => $user_id, 'total' => $grab_point, 'message' => 'Penambahan Point Kelipatan Belanja'.$grab_price]);

		$body = '{
			"to" : "'.$device_token.'",
			"collapse_key" : "type_a",
			"notification" : {
				"body" : "'.$content.'",
				"title": "'.$title.'",
				"image": "'.$uri.'"
			},
		}';
		
		$r = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
			'body' => $body
		]);

		$response = $r->getBody()->getContents();
	}

}

function __toMilisecond($date) {
	return (strtotime($date) * 1000);
}