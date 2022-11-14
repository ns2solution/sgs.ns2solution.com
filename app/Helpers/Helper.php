<?php

use Illuminate\Support\Facades\Session;

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

function _getDataJSON($url, $method = 'GET')
{
	$url = env('API_URL') . '/' . $url;
	$req = ['token' => Session::get('token'), 'email' => Session::get('email')];

	$option = [
		"ssl" => [
			"verify_peer"      => false,
			"verify_peer_name" => false,
		]
	];

	if($method == 'POST'){

		$option['https'] = [
    		'method' 		   => 'POST',
    		'header'  		   => 'Content-Type: application/x-www-form-urlencoded',
    		'content' 		   => http_build_query($req)
    	];

	}else{

		$url = $url . '?token=' . $req['token'] . '&email=' . $req['email'];

	}

	$data = file_get_contents($url, false, stream_context_create($option));
	$data = json_decode($data);

	return $data;
}

function _getPhotoProfile($url)
{
	$url = env('API_URL') . '/' . $url;

	$option = [
		"ssl" => [
			"verify_peer"      => false,
			"verify_peer_name" => false,
		],
	];

	$img = @file_get_contents($url, false, stream_context_create($option));

	if(!$img){
		return asset('assets/main/img/blank_user.jpg');
	}

	return $url;
}

function _settingSidebar()
{
	/*
        --- R  O  L  E ---
        1 : Super
        2 : Admin Pusat
        3 : Admin Gudang
        4 : Buyer
        ------------------
    */

	$setting = [
		// 'dashboard'     => [1, 2, 3],
		'dashboard/shoppingreport'     => [1, 2, 3],
		'dashboard/brandreport'     => [1, 2, 3],
		'dashboard/perwhreport'     => [1, 2, 3],
		'dashboard/shoppingperwarrreport'     => [1, 2, 3],
		'dashboard/mutationwarreport'     => [1, 2, 3],
		'dashboard/saldowpreport'     => [1, 2, 3],
		'users'         => [1, 2],
		'profile'       => [],
		'buyers'		=> [1, 2],
		'buyers-view'	=> [3],
		'category'      => [1, 2],
		'products'       => [1, 2],
		'product-view'  => [3],
		'produk-poin'   => [1, 2],
		'warehouse'     => [1, 2],
		'principle'     => [1, 2],
		'brand'         => [1, 2],
		'promosi'       => [1, 2],
		'add-point'     => [1, 2],
		'stocks'         => [1, 2, 3],
		'convertion'    => [1],
		'topup-wp'	    => [1],
		'terms-condition'=> [1],
		'order'         => [1, 2, 3],
		'courier'       => [1, 2, 3],
		'alasan'        => [1, 2],
		'top-product'	=> [1, 2],
		'stockproductpoint' => [1],
		'product-point' => [1],
		'topproductpoint'	=> [1],
		'order-point'   => [1],
		'transfer-wp'         => [1, 2],
	];

	return $setting;
}

function _checkSidebar($URL)
{
	$ROLE    = Session::get('user')->role;
	$SETTING = _settingSidebar();
	
	if(array_key_exists($URL, $SETTING)){

		if(!in_array($ROLE, $SETTING[$URL])){
			return false;
		}

	}else{
		return false;
	}

	return true;
}