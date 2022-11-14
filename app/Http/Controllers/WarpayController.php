<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Validator;
use Exception;

use App\User;
use App\Order;
use App\OrderItem;
use App\Warpay;
use App\Payment;
use Midtrans\Snap;
use App\UserProfile;
use App\OrderPayment;
use App\TopupWarpay;
use App\Setting;
use App\MasterOrderStatus;
use App\HistoryInOutWp;

class WarpayController extends Controller
{

	const WARPAY = ['warpay'];
	const MIN = '-', PLUS = '+';

	public function topUp(Request $req)
	{

        $rules = [
			'total' => 'required',
			'email' => 'required'
		];	

		$validator = Validator::make($req->all(), $rules);

		if ($validator->fails()) {

			return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
		
		}

		try{

			DB::beginTransaction();

			$user 			= User::where('email',$req->email)->first();
			$profile 		= $user->profile;
			$GRAND_TOTAL 	= $req->total;
			
			$order_payment  = OrderPayment::create([
				'grand_total' => $GRAND_TOTAL
			]);


			$data = [
				'status'           => 1,
				'user_id'          => $user->id,
				'order_payment_id' => $order_payment->id,
				'total'			   => $GRAND_TOTAL
			];

			$topup_wp                 = TopupWarpay::create($data);

			if(!$profile){

				DB::rollback();
				return __jsonResp(false, 'User Pofile tidak ditemukan', 500);
			
			}

			/*******************************************************
			************   Convertion Rp to Warpay   ***************
			*******************************************************/

			// $warpay_convertion = round( (int)$req->total / $this->__convertionWarpay(), PHP_ROUND_HALF_DOWN); // cara lama

			// $wp_conv 	= floor($GRAND_TOTAL / $this->__convertionWarpay());
			
			// $wp_total 	= (int) $profile->warpay + $wp_conv;

			// $profile->warpay =  $wp_total;


			/*******************************************************
			****************   MIDTRANS STARTING   *****************
			*******************************************************/

			$this->initPaymentGateway();

			$cs_detail              = [
				'first_name' => $user->fullname,
				'email'      => $user->email,
				'phone'      => $profile->phone
			];

			$params                 = [
				'enable_payments' => Payment::PAYMENT_CHANNELS,
				'transaction_details' => [
					'order_id'     => $order_payment->id,
					'gross_amount' => $GRAND_TOTAL
				],
				'customer_details' => $cs_detail,
				'expiry' => [
					'start_time' => (new \DateTime(date('c')))->format("Y-m-d H:i:s T"),
					'unit'       => Payment::EXPIRY_UNIT['hour'], //hour
					'duration'   => Payment::EXPIRY_DURATION
				]
			];

			$orderDate              = date('Y-m-d H:i:s');

			$paymentDue             = (new \DateTime($orderDate))->modify('+'.Payment::EXPIRY_DURATION.' hour')->format('Y-m-d H:i:s'); // hour

			$snap                   = Snap::createTransaction($params);

			if($snap->token){

				$order_payment->payment_due   = $paymentDue;
				$order_payment->payment_token = $snap->token;
				$order_payment->payment_url   = $snap->redirect_url;
				$order_payment->save();

			}else{

				throw new Exception('Midtrans Error.');

			}

			$order_payment->save();
			$profile->save();

			$status_name = MasterOrderStatus::find(1)->name;
			$this->initNotification($status_name, 'selesaikan pembayaran sebelum '.$paymentDue, $user->device_token);

			if(!$profile){
				DB::rollback();
				return __jsonResp(false, 'Warpay tidak bertambah', 500);
			}

			DB::commit();
			return __jsonResp(true, 'Transaksi Berhasil', 200, null, $order_payment);

		}catch(Exception $e){

			DB::rollback();
			return __jsonResp(false, 'Terdapat kesalahan pada sistem internal.', 500, $e->getMessage());
		
        }
	}

	private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
    }

	public function transferToWarrior(Request $request, $type)
    {
		$rules = [
			'user_id' => 'required',
			'warpay' => 'required',
		];	

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {

			return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
		
		}

		DB::beginTransaction();

		try{

			if($type == 'kredit'){

				$profile = UserProfile::where('user_id', $request->user_id)->first();
				$profile->warpay = ($profile->warpay + $request->warpay);
				$profile->update();

				HistoryInOutWp::create(['type' => SELF::PLUS, 'user_id' => $request->user_id, 'total' => $request->warpay, 'warpay_prev' => $profile->warpay, 'by' => $request->by]);

				DB::commit();
				return __jsonResp(true, 'Transaksi Berhasil', 200, null, $profile);
			
			} else if($type == 'debit') {
				
				$profile = UserProfile::where('user_id', $request->user_id)->first();

				if($profile->warpay >= $request->warpay) {
					$profile->warpay = ($profile->warpay - $request->warpay);
				} else {
					return __jsonResp(false, 'Transaksi dibatalkan, Nominal terlalu besar', 500, null, null);					
				}

				$profile->update();

				HistoryInOutWp::create(['type' => SELF::MIN, 'user_id' => $request->user_id, 'total' => $request->warpay, 'warpay_prev' => $profile->warpay, 'by' => $request->by]);

				DB::commit();
				return __jsonResp(true, 'Transaksi Berhasil', 200, null, $profile);
			

			}
		} catch (Exception $e) {
			
			DB::rollback();
			return __jsonResp(false, 'Terdapat kesalahan pada sistem internal.', 500, $e->getMessage());
		
		}
    }

	
	public function get(Request $req) {
		try {

			$warpay = [];
			$query 	= Warpay::orderBy('total', 'ASC');

			foreach ($query->get() as $val) {

				$val->total_ = _numberFormat($val->total);
				$val->warpay = floor( $val->total / $this->__convertionWarpay() );

				array_push($warpay, $val);

			}

			return __jsonResp(true, 'Data Berhasil diambil', 200, null, $warpay);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
	}


	public function history(Request $req) {

		try {

			$user 			= User::where('email',$req->email)->first();

			$warpay = [];

			$wp_out = Order::select(
							// '(-)', ' Rp.' , FORMAT(final_total, 0), ' (',FLOOR(final_total / 250), ') WP'
							'id',
							'no_po',
							'total_ongkir',
							DB::raw("
								CONCAT(
									' Rp.', FORMAT(final_total, 0)
								) total_rp
							"),
							'final_total AS total_ori',
							// DB::raw("
							// 		CONCAT(
							// 			'(-) ', FLOOR(final_total / " . $this->__convertionWarpay() . "), ' WP'
							// 		) AS warpay
							// "),
							DB::raw('DATE_FORMAT(order.created_at, "%d-%m-%Y %T") AS date'),
							)
							->where('user_id', $user->id)
							->where('payment_type', SELF::WARPAY)
							->whereNotIn('status', [1, 8])
							->orderBy('order.created_at', 'DESC')
							->get();

			foreach($wp_out as $v) {
				$v['order_item'] = OrderItem::where('order_id', $v->id)->get();
			}


			foreach($wp_out as $a) {

				$a['FINAL_ONGKIR_WARPAY'] = (floor((int)$a->total_ongkir / $this->__convertionWarpay()));


				foreach($a['order_item'] as $b) {

					$warpay_convertion = floor( (int)$b->price / $this->__convertionWarpay());
					$total_warpay = (int)$b->total_item * $warpay_convertion;
	
					$a['GRAND_TOTAL_WARPAY'] += (int)$total_warpay;
	
				}
			}


			foreach($wp_out as $v) {
				$v['GRAND_TOTAL_PRICE_AND_ONGKIR_WP'] = $v['GRAND_TOTAL_WARPAY'] + $v['FINAL_ONGKIR_WARPAY'] ;
			}

							

			$wp_in	= TopupWarpay::select(
							// '(+)', ' Rp.', FORMAT(grand_total, 0)
							DB::raw("
								IF(topup_warpay.status = 6, 'Dibayar', 'Belum dibayar') AS status
							"),
							DB::raw("
								IF(topup_warpay.status = 6, null, A.payment_url) AS payment_url
							"),
							DB::raw("
								CONCAT(
									' Rp.', FORMAT(grand_total, 0)
								) total_rp
							"),
							'grand_total AS total_ori',
							DB::raw("
								CONCAT(
									'(+) ', FLOOR(grand_total / " . $this->__convertionWarpay() . "), ' WP'
								) warpay
							"),
							DB::raw('DATE_FORMAT(A.created_at, "%d-%m-%Y %T") AS date'),
						)
						->where('user_id', $user->id)
						->leftJoin('order_payment AS A', 'topup_warpay.order_payment_id', '=', 'A.id')
						->orderBy('topup_warpay.created_at', 'DESC')
						->get();
			

			
			foreach ($wp_out as $val) {

				$val->info 			= false;
				$val->status 		= null;
				$val->payment_url 	= null;
				$val->warpay		= '(-) '. $val['GRAND_TOTAL_PRICE_AND_ONGKIR_WP']. ' WP';

				array_push($warpay, $val);
			}

			foreach ($wp_in as $val) {

				$val->info 	= true;
				$val->no_po = null;
				$val->id 	= null;

				array_push($warpay, $val);
			}

			usort($warpay, function($a, $b) {
				return strtotime($a->date) < strtotime($b->date);
			});

			return __jsonResp(true, 'Data Berhasil diambil', 200, null, $warpay);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
	}


	public function find($id, Request $req) {
		try {

			$warpay = Warpay::findOrFail($id);
			
			return __jsonResp(true, 'Data Berhasil diambil', 200, null, $warpay);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
	}


	public function storeOrUpdate(Request $req) {

		$rules = [
            'total'              => 'required|unique:warpay'
        ];

        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
        }

		try {

			$warpay = Warpay::updateOrCreate([
				'id'   => $req->id
			],[
				'total'       => $req->total,
			]);
			
			return __jsonResp(true, 'Data Berhasil diambil', 200, null, $warpay);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
	}


	public function delete($id, Request $req) {
		try {

			$warpay = Warpay::findOrFail($id);
			$warpay->delete();

			return __jsonResp(true, 'Data Berhasil dihapus', 200, null, $warpay);

        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);

        }
	}

}
