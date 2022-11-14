<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use \Midtrans\Config;
use GuzzleHttp\Client as GuzzleClient;

class Controller extends BaseController
{
    //
    protected function initPaymentGateway()
	{
		// Set your Merchant Server Key
		Config::$serverKey = env('MIDTRANS_SERVER_KEY');
		// Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
		Config::$isProduction = (bool)env('MIDTRANS_PRODUCTION', false);
		// Set sanitization on (default)
		Config::$isSanitized = true;
		// Set 3DS transaction for credit card to true
		Config::$is3ds = true;
	}


	//https://www.decathlon.co.id/img/cms/delivery-page.jpg
	protected function initNotification($title = 'SGSWarrior | Notification Order', $content = null, $token, $uri = 'https://www.decathlon.co.id/img/cms/delivery-page.jpg') {

		$headers = [
			'postman-token' => 'fec7c571-ed63-9fee-cae9-114c274da178',
			'cache-control' => 'no-cache',
			'authorization' => 'key=AAAAlM_TrXo:APA91bG2kdsO6suukbSUOqFKezu82l9QudE833edorq2RV0LDHBMeyHgpwcA9DvTRTTqcVyOrnpaxvl8Us-nFfDKDiyF7YWjPv-WPrAk3Sstv39sybUAt_kPMm3SLXMujhg4nCrgIMTU',
			'content-type' => 'application/json'
		];
		
		$client = new GuzzleClient([
			'headers' => $headers
		]);
		
		$body = '{
			"to" : "'.$token.'",
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

	protected function convertZEROtoPST($id) {
		if($id == '0') {
			return 'PST';
		}

		return $id;
	}

	protected function convertPSTtoZERO($id) {
		if($id == 'PST') {
			return 0;
		}

		return $id;
	}
}
