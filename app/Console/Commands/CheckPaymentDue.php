<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Order;
use App\Payment;
use App\OrderPayment;
use App\OrderItem;
use App\StockProduct;
use App\MasterOrderStatus;
use App\User;
use App\UserProfile;
use App\Setting;
use App\HistoryInOutPoint;
use \App\AkumulasiPointPerMonth;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\DB;

use Exception;


class CheckPaymentDue extends Command
{

    public const MIN = '-', PLUS = '+', IS_FALSE = 'false';

    protected $signature = 'checkpaymentdue';

    protected $description = 'Buat ngecek pembayaran kadaluarsa, nanti buat dibalikin stocknya, sekalian penambahan point';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        try {

            $order_payment = OrderPayment::where('payment_status', 'unpaid')
                                        ->where('payment_due', '<=', _time())
                                        ->get();
            if($order_payment) {

                foreach ($order_payment as $a) {
                    
                    $orders = Order::where('order_payment_id', $a->id)->get();
                    
                    foreach($orders as $b) {
        
                        $user_id = $b->user_id;

                        $order_details = OrderItem::where('order_id', $b->id)->get();


                        foreach($order_details as $c) {
                                
                            $stock = StockProduct::where([
                                'warehouse_id' => $c->order->warehouse_id,
                                'product_id'   => $c->product_id
                            ])->first();
            
                            
                            $diff_qty = $c->total_item;
        
                            StockProduct::where('id', $stock->id)->update(['stock'=>(int)$stock->stock + $diff_qty]);
                        }

                        $b->status = 8; // Pesanan Dibatalkan
                        $b->save();

                        $user = User::find($user_id);

                        $status_name = MasterOrderStatus::find(8)->name;
                        $this->initNotification($status_name ? $status_name :  'Pesanan Dibatalkan', $b->no_po, $user->device_token);


                    }

                    $a->payment_status = 'refund';
                    $a->save();
                    $a->delete();
        
                }
            }

        
            /* ------------------------------- Ulah Tahun ------------------------------- */

            

            $PROFILES = UserProfile::whereRaw("DATE_FORMAT(birth_date,'%m-%d') = DATE_FORMAT(now(),'%m-%d')")->get();
            $POINT_BD = Setting::first()->point_birthday;
        
            if(count($PROFILES) > 0) {
                
                foreach($PROFILES as $profile) {
                    $profile->point = ($profile->point + $POINT_BD); 

                    // $basic  = new \Nexmo\Client\Credentials\Basic('183462a2', 'jai4wwNlzHFIRKGC');
                    // $client = new \Nexmo\Client($basic);
                    
                    // $message = $client->message()->send([
                    //     'to' => '6282163998825',
                    //     'from' => 'Test',
                    //     'text' => 'Test'.($profile->point + $POINT_BD)
                    // ]);

                    $profile->save();


                    $log = [
                        'type'			=> SELF::PLUS,
                        'user_id'		=> $profile->user_id,
                        'total'     	=> $POINT_BD,
                        'message'		=> 'Penambahan Point Setiap Tahun',
                    ];
        
                    HistoryInOutPoint::create($log);
                    $this->initNotification('Penambahan Point', 'Penambahan point ulang tahun sebsar'.$POINT_BD, $profile->user->device_token);

                }
            }


                    
            /* ------------------------------- Akumulasi Point setiap bulan  ------------------------------- */

                
            $__time = [
                'now'   => __toMilisecond( date("Y-m-d") ),
                'end'   => __toMilisecond( date("Y-m-t", __toMilisecond(date("Y-m-d")) / 1000) )
            ];

            $__getAccumulations = [
                'all'       => AkumulasiPointPerMonth::select( 'akumulasi_point_per_month.id', 'order_id', DB::raw('status AS status_order'), 'akumulasi_point_per_month.user_id', DB::raw('amount AS total_in_acc') , DB::raw('total_price AS total_in_order')) ->leftJoin('order','order.id','=','akumulasi_point_per_month.order_id')->where('is_checked_with_cron', 'false')->get(),
                'groupBy'   => AkumulasiPointPerMonth::select( 'id', 'user_id' ) ->where('is_checked_with_cron', 'false')->groupBy('user_id')->get(),  
            ];
    
            if($__time['now'] === $__time['end']) {
                

                $transactionOfUsers = [];
                $idOfAccumulations = [];
                
                // push id accumulations by unchecked with cron
                foreach ($__getAccumulations['all'] as &$a) {
    
                    array_push($idOfAccumulations, $a->id);
                
                }

                // push transaction all user by unchecked with cron    
                foreach ($__getAccumulations['groupBy'] as $a) {
                
                    array_push($transactionOfUsers, (object) ['user_id' => $a->user_id, 'total' => 0]);
                }
    
                
    
                // calculate total by user
                foreach ($__getAccumulations['all'] as &$a) {
    
                    // find order id
                    $order = Order::find($a->order_id);
    
                    if($order) {
    
                        $id     = $order->id;
                        $status = $order->status;
    
                        // apakah order ini dibatalkan?
                        if($status === 8) {
                            
                            // set total jadi ke 0, pada akumulasi yg memiliki order id ini
                            $a->total_in_acc = 0;
                            $a->status_order = 'Data ini tidak diproses!';
    
    
                        } else {
    
                            // apakah order ini ada transaksi sebagian yg dibatalkan?
                            if($a->total_in_acc !== $a->total_in_order) {
                                
                                $a->total_in_acc = $a->total_in_order;
    
                                $acc = AkumulasiPointPerMonth::find($a->id);
                                $acc->amount = $a->total_in_order;
                                $acc->update();
                            
                            }
    
                            $a->status_order = 'Data ini diproses!';
    
    
                        }
                        
                    }
    
                    foreach ($transactionOfUsers as &$b) {
    
                        if($a->user_id === $b->user_id) {
                            $b->total += $a->total_in_acc;
                        }
                    
                    }
                }

                // nambahin point ke user, run func kelipatan point
                foreach ($transactionOfUsers as $a) {
                    __kelipatanPoint($a->user_id, $a->total, 'OTHER');
                }

                // kalo udah dicek didelete dan di true
                foreach ($idOfAccumulations as $id) {

                    $acc = AkumulasiPointPerMonth::find($id);
                    $acc->is_checked_with_cron = true;
                    $acc->update();
    
                    AkumulasiPointPerMonth::find($id)->delete();
                
                }
    
            
            }


            
            $this->info('Command is working fine!');
        
        } catch (Exception $e) {

            $this->info($e->getMessage());

            __error([__FUNCTION__, $e->getMessage().' Line : '.$e->getLine()]);

        }

    }
}
