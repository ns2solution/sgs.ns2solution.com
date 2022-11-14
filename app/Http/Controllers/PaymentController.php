<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Midtrans\Notification;

use App\Order;
use App\Payment;
use App\OrderPayment;
use App\OrderItem;
use App\StockProduct;
use App\Setting;
use App\TopupWarpay;
use App\HistoryInOutWp;
use App\MasterOrderStatus;
use App\User;
use App\AkumulasiPointPerMonth;

class PaymentController extends Controller
{

	public const MIN = '-', PLUS = '+';

    public function notification(Request $request)
	{
		try {

			$payload      = $request->getContent();
			$notification = json_decode($payload);

			$this->initPaymentGateway();

			$statusCode = null;

			$payNotif = new Notification();
			$order    = OrderPayment::findOrFail($payNotif->order_id);

			if($order->isPaid()){
				return response()->json([
					'status'  => false,
					'message' => 'Invalid signature.',
				], 422);
			}

			$trans   = $payNotif->transaction_status;
			$type    = $payNotif->payment_type;
			$orderId = $payNotif->order_id;
			$fraud   = $payNotif->fraud_status;

			$vaNumber   = null;
			$vendorName = null;

			if(!empty($payNotif->va_numbers[0])){
				$vaNumber   = $payNotif->va_numbers[0]->va_number;
				$vendorName = $payNotif->va_numbers[0]->bank;
			}

			$payStatus = null;

			if ($trans == 'capture') {
				// For credit card transaction, we need to check whether transaction is challenge by FDS or not
				if ($type == 'credit_card') {
					if ($fraud == 'challenge') {
						// TODO set payment status in merchant's database to 'Challenge by FDS'
						// TODO merchant should decide whether this transaction is authorized or not in MAP
						$payStatus = Payment::CHALLENGE;
					} else {
						// TODO set payment status in merchant's database to 'Success'
						$payStatus = Payment::SUCCESS;
					}
				}
			} else if ($trans == 'settlement') {
				// TODO set payment status in merchant's database to 'Settlement'
				$payStatus = Payment::SETTLEMENT;
			} else if ($trans == 'pending') {
				// TODO set payment status in merchant's database to 'Pending'
				$payStatus = Payment::PENDING;
			} else if ($trans == 'deny') {
				// TODO set payment status in merchant's database to 'Denied'
				$payStatus = PAYMENT::DENY;
			} else if ($trans == 'expire') {
				// TODO set payment status in merchant's database to 'expire'
				

				/*******************************************************
				*******************   EXPIRE PAYMENT   *****************
				*******************************************************/

				$payStatus = PAYMENT::EXPIRE;
				
				$order_payment_id = $order->id;


				/*******************   ORDER   *****************/

				$orders = Order::where('order_payment_id', $order_payment_id)->get();
				
				if($orders) {

					foreach($orders as $a) {

						$user = User::find($a->user_id);

						$order_details = OrderItem::where('order_id', $a->id)->get();

						foreach($order_details as $b) {
								
							$stock = StockProduct::where([
								'warehouse_id' => $b->order->warehouse_id,
								'product_id'   => $b->product_id
							])->first();
			
							
							$diff_qty = $b->total_item;

							StockProduct::where('id', $stock->id)->update(['stock'=>(int)$stock->stock + $diff_qty]);
						}

						$a->status = 8; // Pesanan Dibatalkan
						$a->save();

						$status_name = MasterOrderStatus::find(8)->name;
						$this->initNotification($status_name, $a->no_po, $user->device_token);
					}

				}


				/*******************   WARPAY   *****************/

				$warpay = TopupWarpay::where('order_payment_id', $order_payment_id)->first();
				
				if($warpay) {
					
					$user = User::find($warpay->user_id);

					$warpay->status = 8; // Pesanan Dibatalkan
					$warpay->save();

					$this->initNotification('Topup Warpay', 'Topup warpay gagal, transaksi belum dibayar', $user->device_token);
				}

			} else if ($trans == 'cancel') {
				// TODO set payment status in merchant's database to 'Denied'
				$payStatus = PAYMENT::CANCEL;
			}

			$paymentParams = [
				'order_id'     => $order->id,
				'amount'       => $payNotif->gross_amount,
				'method'       => 'midtrans',
				'status'       => $payStatus,
				'token' 	   => $payNotif->transaction_id,
				'payloads' 	   => $payload,
				'payment_type' => $payNotif->payment_type,
				'va_number'    => $vaNumber,
				'vendor_name'  => $vendorName,
				'biller_code'  => $payNotif->biller_code,
				'bill_key'     => $payNotif->bill_key
			];

			$payment = Payment::create($paymentParams);

			if ($payStatus && $payment) {

				DB::transaction(
					function () use ($order, $payment) {
						if(in_array($payment->status, [Payment::SUCCESS, Payment::SETTLEMENT])){

							$order->payment_status = OrderPayment::PAID;
							$order->save();
							

							/*******************   ORDER   *****************/

							$__order = Order::where('order_payment_id', $order->id)->get();
							
							if(count($__order) !== 0) {


								// __kelipatanPoint($__order->first()->user_id, $__order->first()->total_price, 'MIDTRANS');

								$user = User::find($__order->first()->user_id);



								foreach($__order as $o) {
									
									$____update = Order::find($o->id);
									$____update->status = 2;
									$____update->save();

									AkumulasiPointPerMonth::create(
										[
											'order_id'	=> $o->id,
											'user_id'	=> $o->user_id,
											'amount'	=> $o->total_price,
											'type_transaction' => 'MIDTRANS',
										]
									);

								}


								$status_name = 'Pesanan Dibayar';// MasterOrderStatus::find(2)->name;
								$this->initNotification($status_name, 'Order berhasil dibayar', $user->device_token);
							}


							/*******************   WARPAY   *****************/

							$__warpay = TopupWarpay::where('order_payment_id', $order->id)->first();


							if($__warpay) {


								$GRAND_TOTAL= $__warpay->warpay && $__warpay->warpay->total ? $__warpay->warpay->total : $__warpay->total;
								
								$profile 	= $__warpay->user->profile;

								$wp_conv 	= floor($GRAND_TOTAL / $this->__convertionWarpay());
								$wp_total 	= $profile->warpay + $wp_conv;

								$__warpay->update([
									'status' => 6 // Pesanan Selesai
								]);

								$user = User::find($profile->user_id);

								$status_name = MasterOrderStatus::find(6)->name;
								$this->initNotification('Topup Warpay', 'Penambahan warpay sebesar '.$wp_conv.' ke akunmu', $user->device_token);

								
								$profile->update([
									'warpay' => $wp_total
								]);

								HistoryInOutWp::create(['type' => SELF::PLUS, 'user_id' => $__warpay->user->profile->user_id, 'total' => $wp_conv, 'warpay_prev' => $profile->warpay, 'by' => $__warpay->user->profile->user_id ]);

							}

						}
					}
				);

			}

			$message = 'Payment status is : '. $payStatus;

			return response()->json([
				'message' => $message
			], 200);
		
		} catch (\Exception $e) {

			__error([__FUNCTION__, $e->getMessage().' Line : '.$e->getLine()]);
			
		}
	}

	private function __convertionWarpay()
	{
		return (int)Setting::first()->convertion_warpay;
	}

	public function completed(Request $request)
	{
		$id    = $request->query('order_id');
		$order = OrderPayment::findOrFail($id);


		if ($order->payment_status == OrderPayment::UNPAID) {
            /* return response()->json([
                'message' => 'Order ' . $id . ' belum dibayar.',
            ], 200);
            */
            return redirect('pg-info/unpaid');
		}

        /* return response()->json([
            'message' => 'Order ' . $id . ' sudah dibayar.',
        ], 200); */

        return redirect('pg-info/paid');

    }

    public function unfinish(Request $request)
	{
		$id    = $request->query('order_id');
		$order = OrderPayment::findOrFail($id);


        /*return response()->json([
            'message' => 'Order ' . $id . ' proses pembayaran gagal .',
        ], 200);
        */

        return redirect('pg-info/unfinish');
    }

	public function failed(Request $request)
	{
		$id    = $request->query('order_id');
		$order = OrderPayment::findOrFail($id);

        /*return response()->json([
            'message' => 'Order ' . $id . ' proses pembayaran gagal .',
        ], 200);
        */

        return redirect('pg-info/failed');

	}
}
