<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Validator;
use Exception;

use App\Cart;
use App\CartItem;
use App\Product;
use App\User;
use App\Order;
use App\OrderItem;
use App\OrderPayment;
use App\StockProduct;
use App\Warehouse;
use App\POPrinciple;
use App\Principle;
use App\UserProfile;
use App\MasterOrderStatus;
use App\Setting;
use App\Resi;
use App\ShipmentType;
use App\SelfPickedUp;
use App\UserAddress;
use App\HistoryInOutWp;
use App\HistoryInOutPoint;
use App\StockProductPoint;
use App\ProductPoint;
use App\Promosi;
use App\PromosiItem;
use App\ProductPointImage;
use App\ProductImage;
use App\AkumulasiPointPerMonth;
use App\Payment;
use Midtrans\Snap;
use Carbon\Carbon;

class OrderController extends Controller
{
    public const ALL_WAREHOUSE = 'all-warehouse', MIN = '-', PLUS = '+';

    public const payment_type = [
        1 => 'transfer',
        2 => 'warpay',
        3 => 'point'
    ];

    private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
    }

    public function checkout(Request $request)
    {

        /******************************************************
         ******************** TYPE PAYMENT  *******************
         ******************************************************
         *** 1. TRANSFER
         *** 2. WARPAY
         *** 3. POINT
        */

        $rules = [];

        $payment_type = $request->payment_type;

        switch (strtolower($payment_type)) {

            // *** TRANSFER *** //

            case self::payment_type[1]:

                DB::beginTransaction();

                try{

                    $user = User::where('email', $request->email)->first();
                    $cart = Cart::where('user_id', $user->id)->select('id', 'user_id')->first();

                    if(!$cart){
                        return response()->json([
                            'error' => "Checkout Gagal (ER01): Cart kosong."
                        ], 404);
                    }

                    $item = CartItem::where('cart_id', $cart->id);

                    if(count($item->get()) == 0){
                        return response()->json([
                            'error' => "Checkout Gagal (ER02): Cart kosong."
                        ], 404);
                    }

                    $PO           = [];
                    $PAYMENT      = [];
                    $PO_PRINCIPLE = [];
                    $GRAND_TOTAL  = 0;

                    $order_payment = OrderPayment::create([
                        'grand_total' => $GRAND_TOTAL
                    ]);

                    /* PROCESS */

                    foreach($item->get() as $index => $a){

                        $GET_STOCK = StockProduct::where([
                            'warehouse_id' => $a->warehouse_id,
                            'product_id'   => $a->product_id
                        ])->first();

                        if(!$GET_STOCK && $a->promosi_id === null){

                            DB::rollback();
                            return response()->json([
                                'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                            ], 404);

                        }else{

                            if($GET_STOCK->stock == 0 && $a->promosi_id === null){

                                DB::rollback();
                                return response()->json([
                                    'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                                ], 404);

                            }else{

                                if($a->total_item > $GET_STOCK->stock && $a->promosi_id === null){

                                    DB::rollback();
                                    return response()->json([
                                        'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' tersisa ' . $GET_STOCK->stock . '.'
                                    ], 404);

                                }else{

                                    /* VALIDASI WAKTU DAN STOK JIKA PROMOSI */

                                    $PROMOTION      = null;
                                    $PROMOTION_ITEM = null;

                                    if($a->promosi_id !== null){

                                        $PROMOTION = Promosi::where('id', $a->promosi_id)->first();

                                        if(!$PROMOTION){

                                            DB::rollback();
                                            return response()->json([
                                                'error' => 'Checkout Gagal: Terdapat kesalahan pada data promosi.'
                                            ], 404);

                                        }else{

                                            $__time = [
                                                'now'   => strtotime(Carbon::now()),
                                                'start' => strtotime($PROMOTION->start_date),
                                                'end'   => strtotime($PROMOTION->end_date)
                                            ];

                                            if($__time['now'] > $__time['start'] && $__time['now'] < $__time['end']){

                                                switch($PROMOTION->promosi_type){

                                                    /* BUNDLING */
                                                    case 1:

                                                        if($PROMOTION->total_bundle == 0){

                                                            DB::rollback();
                                                            return response()->json([
                                                                'error' => 'Checkout Gagal: Stok promosi ' . $PROMOTION->promosi_name . ' habis.'
                                                            ], 404);

                                                        }else{

                                                            if($a->total_item > $PROMOTION->total_bundle){

                                                                DB::rollback();
                                                                return response()->json([
                                                                    'error' => 'Checkout Gagal: Stok promosi ' . $PROMOTION->promosi_name . ' tersisa ' . $PROMOTION->total_bundle . '.'
                                                                ], 404);

                                                            }else{

                                                                $PROMOTION_ITEM = PromosiItem::where('promosi_id', $a->promosi_id)->get();

                                                                foreach($PROMOTION_ITEM as $b){

                                                                    $__stock = StockProduct::where('id', $b->stock_id)->first();

                                                                    if(!$__stock){

                                                                        DB::rollback();
                                                                        return response()->json([
                                                                            'error' => 'Checkout Gagal: Terdapat kesalahan pada data promosi item.'
                                                                        ], 404);

                                                                    }

                                                                    if($__stock->stock == 0){

                                                                        DB::rollback();
                                                                        return response()->json([
                                                                            'error' => 'Checkout Gagal: Stok produk promosi ' . $PROMOTION->promosi_name . ' habis.'
                                                                        ], 404);

                                                                    }else{

                                                                        if(((int)$b->stock_promosi * (int)$a->total_item) > $__stock->stock){

                                                                            DB::rollback();
                                                                            return response()->json([
                                                                                'error' => 'Checkout Gagal: Stok produk promosi ' . $PROMOTION->promosi_name . ' habis.'
                                                                            ], 404);

                                                                        }

                                                                    }

                                                                }

                                                            }

                                                        }

                                                        break;

                                                    /* DISKON */
                                                    case 2:

                                                        $PROMOTION_ITEM = PromosiItem::where([
                                                            'promosi_id' => $a->promosi_id,
                                                            'stock_id'   => $GET_STOCK->id
                                                        ])->first();

                                                        if(!$PROMOTION_ITEM){

                                                            DB::rollback();
                                                            return response()->json([
                                                                'error' => 'Checkout Gagal: Terdapat kesalahan pada data promosi item.'
                                                            ], 404);

                                                        }

                                                        if($PROMOTION_ITEM->stock_promosi == 0){

                                                            DB::rollback();
                                                            return response()->json([
                                                                'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                                                            ], 404);

                                                        }else{

                                                            if($a->total_item > $PROMOTION_ITEM->stock_promosi){

                                                                DB::rollback();
                                                                return response()->json([
                                                                    'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' tersisa ' . $PROMOTION_ITEM->stock_promosi . '.'
                                                                ], 404);

                                                            }else{

                                                                if($GET_STOCK->stock == 0){

                                                                    DB::rollback();
                                                                    return response()->json([
                                                                        'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                                                                    ], 404);

                                                                }else{

                                                                    if($a->total_item > $GET_STOCK->stock){

                                                                        DB::rollback();
                                                                        return response()->json([
                                                                            'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' tersisa ' . $GET_STOCK->stock . '.'
                                                                        ], 404);

                                                                    }

                                                                }
                                                                
                                                            }

                                                        }

                                                        break;

                                                }

                                            }else{

                                                DB::rollback();
                                                return response()->json([
                                                    'error' => 'Checkout Gagal: Waktu promosi ' . $PROMOTION->promosi_name . ' telah habis.'
                                                ], 400);

                                            }

                                        }

                                    }

                                    /* NEXT PROCESS */

                                    $price       = null;
                                    $total_price = null;

                                    if($a->promosi_id !== null){

                                        switch($PROMOTION->promosi_type){

                                            /* BUNDLING */
                                            case 1:

                                                $PROMOTION->total_bundle = (int)$PROMOTION->total_bundle - (int)$a->total_item;
                                                $PROMOTION->save();

                                                foreach($PROMOTION_ITEM as $b){

                                                    $__stock = StockProduct::find($b->stock_id);
                                                    $__stock->stock = (int)$__stock->stock - ((int)$b->stock_promosi * (int)$a->total_item);
                                                    $__stock->save();

                                                }

                                                $price       = (int)$PROMOTION->total_value;
                                                $total_price = (int)$a->total_item * (int)$PROMOTION->total_value;

                                                break;

                                            /* DISKON */
                                            case 2:

                                                $GET_STOCK->stock = (int)$GET_STOCK->stock - (int)$a->total_item;
                                                $GET_STOCK->save();

                                                $PROMOTION_ITEM->stock_promosi = (int)$PROMOTION_ITEM->stock_promosi - (int)$a->total_item;
                                                $PROMOTION_ITEM->save();

                                                $price       = (int)$PROMOTION_ITEM->fix_value;
                                                $total_price = (int)$a->total_item * (int)$PROMOTION_ITEM->fix_value;

                                                break;

                                        }

                                    }else{

                                        $GET_STOCK->stock = (int)$GET_STOCK->stock - (int)$a->total_item;
                                        $GET_STOCK->save();

                                        $price       = (int)$a->product->prod_base_price;
                                        $total_price = (int)$a->total_item * (int)$a->product->prod_base_price;

                                    }
                                    
                                    if(!array_key_exists($a->warehouse_id, $PO)){

                                        $param_where = [
                                            'cart_id'      => $cart->id,
                                            'warehouse_id' => $a->warehouse_id
                                        ];

                                        $CHECK_SHIPMENT    = ShipmentType::where($param_where)->first();
                                        $CHECK_SELF_PICKUP = SelfPickedUp::where($param_where)->first();

                                        if(!$CHECK_SHIPMENT){

                                            if(!$CHECK_SELF_PICKUP){

                                                DB::rollback();
                                                return response()->json([
                                                    'error' => "Pilih pengiriman dari warehouse " . Warehouse::find($a->warehouse_id)->name . "."
                                                ], 404);

                                            }else{

                                                if($CHECK_SELF_PICKUP->status_pick == 0){

                                                    DB::rollback();
                                                    return response()->json([
                                                        'error' => "Pilih pengiriman dari warehouse " . Warehouse::find($a->warehouse_id)->name . "."
                                                    ], 404);

                                                }
                                            }
                                        }

                                        $is_self_pickup = 0;
                                        $ongkir         = $CHECK_SHIPMENT ? (int)$CHECK_SHIPMENT->courier_ongkir : 0;

                                        if($CHECK_SELF_PICKUP){

                                            if($CHECK_SELF_PICKUP->status_pick == 1){

                                                $is_self_pickup = 1;
                                                $ongkir         = 0;

                                            }

                                        }

                                        $final_total  = $total_price + $ongkir;
                                        $GRAND_TOTAL += (int)$final_total;

                                        $data = [
                                            'no_po'            => '',
                                            'status'           => 1,
                                            'warehouse_id'     => $a->warehouse_id,
                                            'user_id'          => $user->id,
                                            'total_price'      => $total_price,
                                            'total_ongkir'     => $ongkir,
                                            'final_total'      => $final_total,
                                            'order_payment_id' => $order_payment->id,
                                            'payment_type'     => strtolower($payment_type),
                                            'is_pick'          => $is_self_pickup,
                                            'is_accept_refund' => $request->is_accept_refund
                                        ];

                                        /* DROPSHIPPER */
                                        
                                        $is_dropshipper = 0;
                                        
                                        if($request->is_dropshipper == 'true') {

                                            $is_dropshipper = 1;
                                            
                                            $rules['dropshipper_name']   = 'required';
                                            $rules['dropshipper_number'] = 'required';

                                            $validator = Validator::make($request->all(), $rules);

                                            if($validator->fails()){
                                                DB::rollback();
                                                return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
                                            }
                                    
                                            $data['dropshipper_name']   = $request->dropshipper_name;
                                            $data['dropshipper_number'] = $request->dropshipper_number;
                                        
                                        }

                                        $data['is_dropshipper'] = $is_dropshipper;

                                        $order            = Order::create($data);
                                        $data['order_id'] = $order->id;
                                        $data['no_po']    = $this->GeneratePO($a->warehouse_id, $user->id, $order->id);

                                        $order      = Order::where('id', $order->id)->first();
                                        $find_order = $order;

                                        $order->update(['no_po' => $data['no_po']]);

                                        /* UPDATE SHIPMENT */

                                        $shp_type = ShipmentType::where($param_where)->update([
                                            'order_id' => $find_order->id
                                        ]);

                                        $PO[$a->warehouse_id] = $data;

                                    }else{

                                        $order              = Order::find($PO[$a->warehouse_id]['order_id']);
                                        $order->total_price = (int)$order->total_price + $total_price;
                                        $order->final_total = (int)$order->final_total + $total_price;
                                        $order->save();

                                        $GRAND_TOTAL += (int)$total_price;

                                    }

                                    $order_id = $PO[$a->warehouse_id]['order_id'];

                                    $data_item = [
                                        'order_id'	  => $order_id,
                                        'product_id'  => $a->product->id,
                                        'total_item'  => $a->total_item,
                                        'price'		  => $price,
                                        'total_price' => $total_price
                                    ];

                                    if($a->promosi_id !== null){
                                        $data_item['promosi_id'] = $a->promosi_id;
                                    }

                                    OrderItem::create($data_item);

                                }

                            }

                        }

                    }

                    /* ======================== MIDTRANS ======================== */

                    $this->initPaymentGateway();

                    $cs = UserProfile::where('user_id', $user->id)->first();

                    $cs_detail = [
                        'first_name' => $user->fullname,
                        'email'      => $user->email,
                        'phone'      => $cs->phone
                    ];

                    $params = [
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

                    $orderDate  = date('Y-m-d H:i:s');
                    $paymentDue = (new \DateTime($orderDate))->modify('+'.Payment::EXPIRY_DURATION.' hour')->format('Y-m-d H:i:s');
                    $snap       = Snap::createTransaction($params);

                    if($snap->token){

                        $order_payment->payment_due   = $paymentDue;
                        $order_payment->payment_token = $snap->token;
                        $order_payment->payment_url   = $snap->redirect_url;
                        $order_payment->save();

                    }else{

                        throw new Exception('Midtrans Error.');

                    }

                    $order_payment->grand_total = $GRAND_TOTAL;
                    $order_payment->save();

                    $cart->delete();
                    $item->delete();

                    $status_name = MasterOrderStatus::find(1)->name;
                    $this->initNotification($status_name, 'Mohon selesaikan pembayaran sebelum ' . $paymentDue, $user->device_token);

                    DB::commit();

                    return response()->json([
                        'message'      => 'Checkout berhasil.',
                        'data' 	       => $PO,
                        'payment_data' => $order_payment
                    ], 200);

                }catch(Exception $e){

                    DB::rollback();

                    return response()->json([
                        'message' => 'Terdapat kesalahan pada sistem internal.',
                        'error'   => $e->getMessage()
                    ], 500);

                }

                break;


            // *** WARPAY *** //

            case self::payment_type[2]:

                DB::beginTransaction();

                try{

                    $user    = User::where('email', $request->email)->first();
                    $profile = UserProfile::where('user_id', $user->id)->first();
                    $cart    = Cart::where('user_id', $user->id)->select('id', 'user_id')->first();

                    if(!$cart){
                        return response()->json([
                            'error' => "Checkout Gagal (ER01): Cart kosong."
                        ], 404);
                    }

                    $item = CartItem::where('cart_id', $cart->id);

                    if(count($item->get()) == 0){
                        return response()->json([
                            'error' => "Checkout Gagal (ER02): Cart kosong."
                        ], 404);
                    }

                    $PO                 = [];
                    $GRAND_TOTAL        = 0;
                    $GRAND_TOTAL_ONGKIR = 0;
                    $PO_ID              = array();

                    /* PROCESS */

                    foreach($item->get() as $index => $a) {

                        $GET_STOCK      = StockProduct::where([
                            'warehouse_id' => $a->warehouse_id,
                            'product_id'   => $a->product_id
                        ])->first();

                        if(!$GET_STOCK && $a->promosi_id === null){

                            DB::rollback();
                            return response()->json([
                                'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                            ], 404);

                        }else{

                            if($GET_STOCK->stock == 0 && $a->promosi_id === null){

                                DB::rollback();
                                return response()->json([
                                    'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                                ], 404);

                            }else{

                                if($a->total_item > $GET_STOCK->stock && $a->promosi_id === null){

                                    DB::rollback();
                                    return response()->json([
                                        'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' tersisa ' . $GET_STOCK->stock . '.'
                                    ], 404);

                                }else{

                                    /* VALIDASI WAKTU DAN STOK JIKA PROMOSI */

                                    $PROMOTION      = null;
                                    $PROMOTION_ITEM = null;

                                    if($a->promosi_id !== null){

                                        $PROMOTION = Promosi::where('id', $a->promosi_id)->first();

                                        if(!$PROMOTION){

                                            DB::rollback();
                                            return response()->json([
                                                'error' => 'Checkout Gagal: Terdapat kesalahan pada data promosi.'
                                            ], 404);

                                        }else{

                                            $__time = [
                                                'now'   => strtotime(Carbon::now()),
                                                'start' => strtotime($PROMOTION->start_date),
                                                'end'   => strtotime($PROMOTION->end_date)
                                            ];

                                            if($__time['now'] > $__time['start'] && $__time['now'] < $__time['end']){

                                                switch($PROMOTION->promosi_type){

                                                    /* BUNDLING */
                                                    case 1:

                                                        if($PROMOTION->total_bundle == 0){

                                                            DB::rollback();
                                                            return response()->json([
                                                                'error' => 'Checkout Gagal: Stok promosi ' . $PROMOTION->promosi_name . ' habis.'
                                                            ], 404);

                                                        }else{

                                                            if($a->total_item > $PROMOTION->total_bundle){

                                                                DB::rollback();
                                                                return response()->json([
                                                                    'error' => 'Checkout Gagal: Stok promosi ' . $PROMOTION->promosi_name . ' tersisa ' . $PROMOTION->total_bundle . '.'
                                                                ], 404);

                                                            }else{

                                                                $PROMOTION_ITEM = PromosiItem::where('promosi_id', $a->promosi_id)->get();

                                                                foreach($PROMOTION_ITEM as $b){

                                                                    $__stock = StockProduct::where('id', $b->stock_id)->first();

                                                                    if(!$__stock){

                                                                        DB::rollback();
                                                                        return response()->json([
                                                                            'error' => 'Checkout Gagal: Terdapat kesalahan pada data promosi item.'
                                                                        ], 404);

                                                                    }

                                                                    if($__stock->stock == 0){

                                                                        DB::rollback();
                                                                        return response()->json([
                                                                            'error' => 'Checkout Gagal: Stok produk promosi ' . $PROMOTION->promosi_name . ' habis.'
                                                                        ], 404);

                                                                    }else{

                                                                        if(((int)$b->stock_promosi * (int)$a->total_item) > $__stock->stock){

                                                                            DB::rollback();
                                                                            return response()->json([
                                                                                'error' => 'Checkout Gagal: Stok produk promosi ' . $PROMOTION->promosi_name . ' habis.'
                                                                            ], 404);

                                                                        }

                                                                    }

                                                                }

                                                            }

                                                        }

                                                        break;

                                                    /* DISKON */
                                                    case 2:

                                                        $PROMOTION_ITEM = PromosiItem::where([
                                                            'promosi_id' => $a->promosi_id,
                                                            'stock_id'   => $GET_STOCK->id
                                                        ])->first();

                                                        if(!$PROMOTION_ITEM){

                                                            DB::rollback();
                                                            return response()->json([
                                                                'error' => 'Checkout Gagal: Terdapat kesalahan pada data promosi item.'
                                                            ], 404);

                                                        }

                                                        if($PROMOTION_ITEM->stock_promosi == 0){

                                                            DB::rollback();
                                                            return response()->json([
                                                                'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                                                            ], 404);

                                                        }else{

                                                            if($a->total_item > $PROMOTION_ITEM->stock_promosi){

                                                                DB::rollback();
                                                                return response()->json([
                                                                    'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' tersisa ' . $PROMOTION_ITEM->stock_promosi . '.'
                                                                ], 404);

                                                            }else{

                                                                if($GET_STOCK->stock == 0){

                                                                    DB::rollback();
                                                                    return response()->json([
                                                                        'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' habis.'
                                                                    ], 404);

                                                                }else{

                                                                    if($a->total_item > $GET_STOCK->stock){

                                                                        DB::rollback();
                                                                        return response()->json([
                                                                            'error' => 'Checkout Gagal: Stok produk ' . $a->product->prod_name . ' tersisa ' . $GET_STOCK->stock . '.'
                                                                        ], 404);

                                                                    }

                                                                }
                                                                
                                                            }

                                                        }

                                                        break;

                                                }

                                            }else{

                                                DB::rollback();
                                                return response()->json([
                                                    'error' => 'Checkout Gagal: Waktu promosi ' . $PROMOTION->promosi_name . ' telah habis.'
                                                ], 400);

                                            }

                                        }

                                    }

                                    /* NEXT PROCESS */

                                    $price       = null;
                                    $total_price = null;

                                    if($a->promosi_id !== null){

                                        switch($PROMOTION->promosi_type){

                                            /* BUNDLING */
                                            case 1:

                                                $PROMOTION->total_bundle = (int)$PROMOTION->total_bundle - (int)$a->total_item;
                                                $PROMOTION->save();

                                                foreach($PROMOTION_ITEM as $b){

                                                    $__stock = StockProduct::find($b->stock_id);
                                                    $__stock->stock = (int)$__stock->stock - ((int)$b->stock_promosi * (int)$a->total_item);
                                                    $__stock->save();

                                                }

                                                $price       = (int)$PROMOTION->total_value;
                                                $total_price = (int)$a->total_item * (int)$PROMOTION->total_value;

                                                break;

                                            /* DISKON */
                                            case 2:

                                                $GET_STOCK->stock = (int)$GET_STOCK->stock - (int)$a->total_item;
                                                $GET_STOCK->save();

                                                $PROMOTION_ITEM->stock_promosi = (int)$PROMOTION_ITEM->stock_promosi - (int)$a->total_item;
                                                $PROMOTION_ITEM->save();

                                                $price       = (int)$PROMOTION_ITEM->fix_value;
                                                $total_price = (int)$a->total_item * (int)$PROMOTION_ITEM->fix_value;

                                                break;

                                        }

                                    }else{

                                        $GET_STOCK->stock = (int)$GET_STOCK->stock - (int)$a->total_item;
                                        $GET_STOCK->save();

                                        $price       = (int)$a->product->prod_base_price;
                                        $total_price = (int)$a->total_item * (int)$a->product->prod_base_price;

                                    }


                                    $warpay_convertion = floor( (int)$price / $this->__convertionWarpay());
                                    $total_warpay      = (int)$a->total_item * $warpay_convertion;

                                    if(!array_key_exists($a->warehouse_id, $PO)){

                                        $param_where = [
                                            'cart_id'      => $cart->id,
                                            'warehouse_id' => $a->warehouse_id
                                        ];

                                        $CHECK_SHIPMENT    = ShipmentType::where($param_where)->first();
                                        $CHECK_SELF_PICKUP = SelfPickedUp::where($param_where)->first();

                                        if(!$CHECK_SHIPMENT){

                                            if(!$CHECK_SELF_PICKUP){

                                                DB::rollback();
                                                return response()->json([
                                                    'error' => "Pilih pengiriman dari warehouse " . Warehouse::find($a->warehouse_id)->name . "."
                                                ], 404);

                                            }else{

                                                if($CHECK_SELF_PICKUP->status_pick == 0){

                                                    DB::rollback();
                                                    return response()->json([
                                                        'error' => "Pilih pengiriman dari warehouse " . Warehouse::find($a->warehouse_id)->name . "."
                                                    ], 404);

                                                }
                                            }
                                        }

                                        $is_self_pickup = 0;
                                        $ongkir         = $CHECK_SHIPMENT ? (int)$CHECK_SHIPMENT->courier_ongkir : 0;

                                        if($CHECK_SELF_PICKUP){

                                            if($CHECK_SELF_PICKUP->status_pick == 1){

                                                $is_self_pickup = 1;
                                                $ongkir         = 0;

                                            }

                                        }

                                        $final_total  = $total_price + $ongkir;
                                        $GRAND_TOTAL += (int)$total_warpay;

                                        
                                        $GRAND_TOTAL_ONGKIR += floor((int)$ongkir / $this->__convertionWarpay());

                                        $data = [
                                            'no_po'        => '',
                                            'status'       => 1,
                                            'warehouse_id' => $a->warehouse_id,
                                            'user_id'      => $user->id,
                                            'total_price'  => $total_price,
                                            'total_ongkir' => $ongkir,
                                            'final_total'  => $final_total,
                                            'payment_type' => strtolower($payment_type),
                                            'is_pick'      => $is_self_pickup,
                                            'is_accept_refund' => $request->is_accept_refund
                                        ];

                                        /* DROPSHIPPER */
                                        
                                        $is_dropshipper = 0;
                                        
                                        if($request->is_dropshipper == 'true') {

                                            $is_dropshipper = 1;
                                            
                                            $rules['dropshipper_name']   = 'required';
                                            $rules['dropshipper_number'] = 'required';

                                            $validator = Validator::make($request->all(), $rules);

                                            if($validator->fails()){
                                                DB::rollback();
                                                return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
                                            }
                                    
                                            $data['dropshipper_name']   = $request->dropshipper_name;
                                            $data['dropshipper_number'] = $request->dropshipper_number;
                                        
                                        }

                                        $data['is_dropshipper'] = $is_dropshipper;

                                        $order            = Order::create($data);
                                        array_push($PO_ID, $order->id);
                                        $data['order_id'] = $order->id;
                                        $data['no_po']    = $this->GeneratePO($a->warehouse_id, $user->id, $order->id);

                                        $order      = Order::where('id', $order->id)->first();
                                        $find_order = $order;

                                        $order->update(['no_po' => $data['no_po']]);

                                        /* UPDATE SHIPMENT */

                                        $shp_type = ShipmentType::where($param_where)->update([
                                            'order_id' => $find_order->id
                                        ]);

                                        $PO[$a->warehouse_id] = $data;

                                    }else{

                                        $order              = Order::find($PO[$a->warehouse_id]['order_id']);
                                        $order->total_price = (int)$order->total_price + $total_price;
                                        $order->final_total = (int)$order->final_total + $total_price;
                                        $order->save();

                                        $GRAND_TOTAL += (int)$total_warpay;

                                    }

                                    $order_id = $PO[$a->warehouse_id]['order_id'];

                                    $data_item = [
                                        'order_id'		=> $order_id,
                                        'product_id'	=> $a->product->id,
                                        'total_item'	=> $a->total_item,
                                        'price'			=> $price,
                                        'total_price'	=> $total_price
                                    ];

                                    if($a->promosi_id !== null){
                                        $data_item['promosi_id'] = $a->promosi_id;
                                    }

                                   OrderItem::create($data_item);

                                }

                            }

                        }

                    }

                    /* PROCESS WARPAY USER */

                    if(($profile && $GRAND_TOTAL) && ($profile->warpay >= $GRAND_TOTAL)){

                        $param['UPDATE_USER_PROFILE'] = [
                            'warpay' => ($profile->warpay - ($GRAND_TOTAL + $GRAND_TOTAL_ONGKIR))
                        ];

                        foreach($PO_ID as $id){

                            $order         = Order::find($id);
                            $order->status = 2; // Menunggu Konfirmasi
                            $order->save();

                            AkumulasiPointPerMonth::create(
                                [
                                    'order_id'  => $id,
                                    'user_id'	=> $user->id,
                                    'amount'	=> $order->total_price,
                                    'type_transaction' => 'WARPAY',
                                ]
                            );

                        }

                        HistoryInOutWp::create([
                            'type'    => SELF::MIN,
                            'user_id' => $user->id,
                            'total'   => ($GRAND_TOTAL + $GRAND_TOTAL_ONGKIR),
                            'warpay_prev' => ($profile->warpay - ($GRAND_TOTAL + $GRAND_TOTAL_ONGKIR)),
                            'by'      => $user->id
                        ]);

                        $profile->update($param['UPDATE_USER_PROFILE']);

                        // _log($profile);
                        

                        // __kelipatanPoint($user->id, $GRAND_TOTAL), 'OTHER');

                        $status_name = 'Pesanan Dibayar';
                        $this->initNotification($status_name, 'Order berhasil dibayar', $user->device_token);

                    }else{

                        DB::rollback();
                        return response()->json([
                            'error' => "Checkout Gagal (ER03): Warpay tidak ada atau tidak cukup."
                        ], 500);

                    }

                    $cart->delete();
                    $item->delete();

                    DB::commit();

                    return response()->json([
                        'error' => 'Checkout berhasil.',
                        'data'  => $PO
                    ], 200);

                }catch(Exception $e){

                    DB::rollback();

                    return response()->json([
                        'message' => 'Terdapat kesalahan pada sistem internal.',
                        'error'   => $e->getMessage()
                    ], 500);

                }

            break;


            // *** POINT *** //
            case self::payment_type[3]:

                $rules = [
                    'id'              => 'required'
                ];
        
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
                }

                DB::beginTransaction();

                try{

                    $user               = User::where('email', $request->email)->first();

                    $profile            = UserProfile::where('user_id', $user->id)->first();

                    $user_point         = $profile->point;

                    $prod_id            = $request->id;

                    $total_item_order   = 1;

                    $wh_id              = $request->warehouse_id ? $request->warehouse_id : 0; 

                    $product            = ProductPoint::find($prod_id);

                    $PO_ID              = null;

                    $GRAND_TOTAL_POINT  = 0;


                    if(!$product){

                        DB::rollback();

                        return response()->json([
                            'error' => 'Checkout Gagal: produk tidak ditemukan.'
                        ], 404);

                    }

                    $GRAND_TOTAL_POINT  = ProductPoint::find($prod_id)->prod_base_point;


                    $GET_STOCK          = StockProductPoint::where([
                        'warehouse_id' => $wh_id,
                        'product_point_id'   => $product->id
                    ])->first();

                    if(!$GET_STOCK){

                        DB::rollback();

                        return response()->json([
                            'error' => 'Checkout Gagal: Stok produk ' . $product->prod_name . ' habis.'
                        ], 404);

                    }else{

                            if($GET_STOCK->stock == 0){

                                DB::rollback();

                                return response()->json([
                                    'error' => 'Checkout Gagal: Stok produk ' . $product->prod_name . ' habis.'
                                ], 404);

                            }else{

                                if($total_item_order > $GET_STOCK->stock){

                                    DB::rollback();

                                    return response()->json([
                                        'error' => 'Checkout Gagal: Stok produk ' . $product->prod_name . ' tersisa ' . $GET_STOCK->stock . '.'
                                    ], 404);

                                }else{

                                    // processing update stock product

                                    $GET_STOCK->stock       = (int)$GET_STOCK->stock - (int)$total_item_order;
                                    $GET_STOCK->save();

                                    $data = [
                                        'no_po'            => '',
                                        'status'           => 1,
                                        'warehouse_id'     => $wh_id,
                                        'user_id'          => $user->id,
                                        'total_price'      => $GRAND_TOTAL_POINT,
                                        'final_total'      => $GRAND_TOTAL_POINT,
                                        'payment_type'     => strtolower($payment_type),
                                    ];


                                    $order              = Order::create($data);

                                    $PO_ID = $order->id;

                                    // $data['no_po']      = $this->GeneratePO($wh_id, $user->id, $order->id);

                                    $order              = Order::where('id', $order->id)->first();
                                    // $order->update(['no_po' => $data['no_po']]);


                                    /* -------------------------------------------------------------------------- */
                                    /*                                 Order Item                                 */
                                    /* -------------------------------------------------------------------------- */

                                    $data_item = [
                                        'order_id'		=> $order->id,
                                        'product_id'	=> $product->id,
                                        'total_item'	=> $total_item_order,
                                        'price'			=> $GRAND_TOTAL_POINT,
                                        'total_price'	=> $GRAND_TOTAL_POINT
                                    ];

                                    OrderItem::create($data_item);

                                }

                            }

                    }

                    // pengurangan point user
                    if( ($profile && $GRAND_TOTAL_POINT) && ($user_point >= $GRAND_TOTAL_POINT)) {

                        // 1. proses pengurangan point
                        $param['UPDATE_USER_PROFILE'] = [
                                'point'          => ( $user_point - $GRAND_TOTAL_POINT)
                        ];

                        // 2. proses update status
                        $order                  = Order::find($PO_ID);
                        $order->status          = 2; // Menunggu Konfirmasi
                        $order->save();
                        
                        $status_name = 'Pesanan Dibayar';// MasterOrderStatus::find(2)->name;
                        $this->initNotification($status_name, $order->no_po, $user->device_token);
        
                        $profile->update($param['UPDATE_USER_PROFILE']);

                        // 3. History Out Point 
                        HistoryInOutPoint::create(['type' => SELF::MIN, 'user_id' => $user->id, 'total' => $GRAND_TOTAL_POINT, 'message' => 'Pembayaran Order']);


                    } else {
                        DB::rollback();

                        return response()->json([
                            'error' => "Checkout Gagal (ER03): Point tidak ada atau tidak cukup."
                        ], 500);
                    }


                    DB::commit();

                    return response()->json([
                        'error'       => 'Checkout berhasil.',
                        'data' 	        => $order,
                    ], 200);

                } catch(Exception $e){

                    DB::rollback();

                    return response()->json([
                        'message' => 'Terdapat kesalahan pada sistem internal.',
                        'error'   => $e->getMessage()
                    ], 500);

                }

            break;
        
        }


    }

    public function list(Request $request)
    {
    	try{

    		$user = User::where('email', $request->email)
    					->first();

    		$order = Order::leftJoin('order_item', function ($join) {
    			$join->on('order.id', '=', 'order_item.order_id')
    				 ->on(
    					'order_item.id',
    					'=',
    					DB::raw("(select min(`id`) from order_item where order.id = order_item.order_id)")
    				 );
            })
            // ->leftJoin('product             AS A', 'A.id', '=', 'order_item.product_id')
    		->leftJoin('master_order_status AS B', 'B.id', '=', 'order.status')
            ->leftJoin('warehouse           AS C', 'C.id', '=', 'order.warehouse_id')
    		// ->leftJoin('product_image', function ($join) {
    		// 	$join->on('A.id', '=', 'product_image.id_product')
    		// 		 ->on(
    		// 			'product_image.id',
    		// 			'=',
    		// 			DB::raw("(select min(`id`) from product_image where A.id = product_image.id_product)")
    		// 		 );
    		// })
    		->select(
    			'order.id',
                'order.total_ongkir',
                'order.status AS status_po',
                DB::raw("UPPER(order.payment_type) AS payment_type"),
                'order.no_po',
                'order.created_at',
    			DB::raw('DATE_FORMAT(order.created_at, "%d %b %Y") AS date'),
    			'order.final_total AS total_ori',
                // DB::raw("CONCAT('Rp ', FORMAT(order.final_total, 0, 'de_DE')) AS total"),
                DB::raw("FORMAT(order.final_total, 0, 'de_DE') AS total"),
    			// 'A.prod_name',
    			// DB::raw('IFNULL(product_image.path, "assets/product_image/_blank.jpg") AS prod_image'),
    			'B.status_name AS status',
                'C.id AS warehouse_id', 'C.name AS warehouse_name',
                'order_item.product_id'
    		)
    		->where([
    			'order.user_id' => $user->id
            ])
            ->orderBy('order.created_at', 'ASC')
    		->get();



    		if(count($order) == 0){
    			return response()->json([
    				'message' => "Transaksi kosong."
    			], 404);
    		}

    		foreach($order as $a){

    			$sisa = (int)OrderItem::select('order_id')->where('order_id', $a->id)->count();

    			if($sisa > 1){

    				$sisa 	   = $sisa - 1;
    				$a['info'] = '(+ ' . $sisa . ' Produk Lainnya)';

    			}else{

    				$a['info'] = '';

                }
                
                if($a->payment_type === 'TRANSFER') {
                    $a['total'] = 'Rp.'.$a->total;
                } 

                // dirubah ke bawah, lihat line 1373
                
                // else if($a->payment_type === 'WARPAY') {
                //     $a['total'] =  ceil((int)$a->total_ori/ $this->__convertionWarpay());
                // }
                
                if($a->payment_type === 'POINT') {

                    $point  = ProductPoint::find($a->product_id);
                    $point_image = isset($point) ? ProductPointImage::where('product_point_id', $point->id)->first() : null;

                    $a['prod_name'] = isset($point) ? $point->prod_name : null;
                    $a['path'] = isset($point_image) ? $point_image->path : "assets/product_image/_blank.jpg";
                    
                } else if($a->payment_type === 'TRANSFER' || $a->payment_type === 'WARPAY') {
                    
                    $prod = Product::find($a->product_id);
                    $prod_image = isset($prod) ? ProductImage::where('id_product', $prod->id)->first() : null;

                    $a['prod_name'] = isset($prod) ? $prod->prod_name : null;
                    $a['path'] = isset($prod_image) ? $prod_image->path : "assets/product_image/_blank.jpg";
                }

                unset($a['product_id']);


                // Push Order Item

                $a['order_item'] = OrderItem::where('order_id', $a->id)->get();

            }


            foreach($order as $a) {
                if($a->status_po === 8) {
                    foreach($a['order_item'] as &$b) {
                        $b->total_item = 1;
                    }
                }
            }



            foreach($order as $a) {

                if($a->payment_type === 'WARPAY') {

                    $a['ONGKIR_WARPAY'] = (floor((int)$a->total_ongkir / $this->__convertionWarpay()));

                    foreach($a['order_item'] as $b) {

                        $warpay_convertion = floor( (int)$b->price / $this->__convertionWarpay());
                        $total_warpay = (int)$b->total_item * $warpay_convertion;
        
                        $a['PRODUCT_WARPAY'] += (int)$total_warpay;
        
                    }
                }

			}


			foreach($order as $a) {

                if($a->payment_type === 'WARPAY') {

				    $a['total'] = $a['PRODUCT_WARPAY'] + $a['ONGKIR_WARPAY'];
                
                }
			
            }
            
    		return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $order
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function detail($id, Request $request)
    {
    	try{

    		$produk = Order::leftJoin('order_item', 'order_item.order_id', '=', 'order.id')
    		// ->leftJoin('product AS A', 'A.id', '=', 'order_item.product_id')
    		// ->leftJoin('product_image', function ($join) {
    		// 	$join->on('A.id', '=', 'product_image.id_product')
    		// 		 ->on(
    		// 			'product_image.id',
    		// 			'=',
    		// 			DB::raw("(select min(`id`) from product_image where A.id = product_image.id_product)")
    		// 		 );
            // })
            ->leftJoin('shipment_types', function ($join) {
                $join->on('shipment_types.order_id', '=', 'order.id');
            })
            ->leftJoin('couriers', function ($join) {
                $join->on('shipment_types.courier_id', '=', 'couriers.id');
            })
            ->leftJoin('resi', function ($join) {
                $join->on('resi.order_id', '=', 'order.id');
            })
    		->select(
    			'order_item.id',
    			'order_item.total_price AS total_ori',
                'order_item.price',
                // DB::raw("CONCAT('Rp ', FORMAT(order_item.total_price, 0, 'de_DE')) AS total"),
                DB::raw("FORMAT(order_item.total_price, 0, 'de_DE') AS total"),
                // 'A.id AS prod_id',
                // 'A.sub_category_id AS sub_categ_id',
    			// 'A.prod_name',
    			// DB::raw("CONCAT(order_item.total_item, ' Barang (', (A.prod_gram * order_item.total_item) / 1000, ' kg)') AS prod_info"),
    			// DB::raw('IFNULL(product_image.path, "assets/product_image/_blank.jpg") AS prod_image'),
                'couriers.name AS courier_name',
                'resi.number_resi',
                'order_item.product_id',
                'order_item.total_item'
            )
    		->where('order.id', '=',$id)
            // ->where('order_item.total_item', '<>', 0)
    		->get();

    		if(count($produk) == 0){
    			return response()->json([
    				'message' => "Transaksi kosong."
    			], 404);
    		}

    		$info = Order::leftJoin('master_order_status AS A', 'A.id', '=', 'order.status')
            ->leftJoin('warehouse       AS B', 'B.id',       '=', 'order.warehouse_id')
            ->leftJoin('resi            AS C', 'C.order_id', '=', 'order.id')
            ->leftjoin('shipment_types  AS D', 'D.order_id', '=', 'order.id')
            ->leftjoin('couriers        AS E', 'E.id',       '=', 'D.courier_id')
            ->leftjoin('user_address    AS F', 'F.id',       '=', 'D.user_address_id')
    		->select(
                'order.id',
                DB::raw("UPPER(order.payment_type) AS payment_type"),
                'order.status',
                'order.order_payment_id',
                'order.no_po',
                'order.total_price AS total_price_',
                'order.total_ongkir AS total_ongkir_',
                'order.final_total AS final_total_',
                DB::raw("FORMAT(order.final_total, 0, 'de_DE') AS final_total"),
    			DB::raw('CONCAT(DATE_FORMAT(order.created_at, "%d %b %Y %H:%i"), " WIB") AS date'),
    			'A.status_name',
                'B.id AS warehouse_id',
                'B.name AS warehouse_name',
                'E.name AS courier_name',
                'F.address AS user_address',
                DB::raw('IFNULL(C.number_resi, "") AS nomor_resi'),
                DB::raw('
                    CONCAT(
                        DATE_FORMAT(order.shipped_start_date, "%W %d %b %Y")
                        , " (",
                        DATE_FORMAT(order.shipped_start_date, "%H:%i")
                        ," - ",
                        DATE_FORMAT(order.shipped_end_date, "%H:%i")
                        ,")"
                    ) AS waktu_ambil
                ')
    		)
    		->where('order.id', $id)
            ->first();

            if($info->payment_type === 'TRANSFER') {

                $info->final_total = 'Rp '.$info->final_total;
            
            } else if($info->payment_type === 'WARPAY') {
            
                // $info->final_total =  (string)ceil((int)$info->final_total_/ $this->__convertionWarpay());
                // $info->total_ongkir = ceil((int)$info->total_ongkir/ $this->__convertionWarpay());
                // $info->total_price = ceil((int)$info->total_price/ $this->__convertionWarpay());
            
            }

            if($info->status == 1 && $info->order_payment_id) {
                
                $order_payment = OrderPayment::find($info->order_payment_id);

                if($order_payment) {
                    $info->payment_url = $order_payment->payment_url;
                }

            }

            if (isset($info->waktu_ambil)) {

                $waktu_ambil        =   explode(' ', $info->waktu_ambil);
                $day                =   explode(' ', $info->waktu_ambil)[0];
                $month              =   explode(' ', $info->waktu_ambil)[2];

                // convert day 
                switch($day) { case "Monday": $day = 'Senin'; break; case "Tuesday": $day = 'Selasa'; break; case "Wednesday": $day = 'Rabu'; break; case "Thursday": $day = 'Kamis'; break; case "Friday": $day = 'Jumat'; break; case "Saturday": $day = 'Sabtu'; break; case "Sunday": $day = 'Minggu'; break; default: break; }
                
                // convert month
                switch($month) { case "May": $month = 'Mei'; break; case "Aug": $month = 'Agu'; break; case "Oct": $month = 'Okt'; break; case "Dec": $month = 'Des'; break; default: break; }

                // reverse english -> indonesia
                $waktu_ambil[0] = $day;
                $waktu_ambil[2] = $month;

                $info->waktu_ambil = implode(' ', $waktu_ambil); 

            }

            unset($info['final_total_']);
            
            if($info->status !== 1) {
                $info->no_invoice = str_replace('PO-SGS', 'INV', $info->no_po);
                $info->url_invoice = env('APP_URL').'/print/invoice/'.$info->id; 
            } else {
                $info->no_invoice = '';
                $info->url_invoice = '';
            }

            // Pesanan dibatalkan 
            if($info->status === 8) {
                foreach($produk as &$a) {
                    $a->total_item = 1;
                }
            }

            foreach($produk as $a) {


                if($info->payment_type === 'TRANSFER') {

                    $info['total_price'] += (int)$a->total_item * $a->price ;
                    $info['total_ongkir'] = (int)$info->total_ongkir_;
                    
                    $a['total'] = 'Rp '.$a->total;
                    $a['total_ori'] = (int)$a->total_ori;
                
                }
                
                
                else if($info->payment_type === 'WARPAY') {

                    // cara lama
                    // $a['total'] = (string)ceil((int)$a->total_ori/ $this->__convertionWarpay());
                    // $a['total_ori'] = ceil((int)$a->total_ori/ $this->__convertionWarpay());

                    // cara baru
                    $warpay_convertion = floor( (int)$a->price / $this->__convertionWarpay());
                    $total_warpay = (int)$a->total_item * $warpay_convertion;
                    
                    $info['total_price'] += (int)$total_warpay;
                    $info['total_ongkir'] = (floor((int)$info->total_ongkir_ / $this->__convertionWarpay()));

                    $a['total'] = (string)(int)$total_warpay;
                    $a['total_ori'] = (int)$total_warpay;
                }


                if($info->payment_type === 'POINT') {

                    $point  = ProductPoint::find($a->product_id);
                    $point_image = ProductPointImage::where('product_point_id', $point->id)->first();

                    $a['prod_info'] = $a->total_item. ' Barang ('.($point->prod_gram * $a->total_item) / 1000 .' kg)';
                    $a['prod_id'] = isset($point) ? $point->id : null;
                    $a['prod_name'] = isset($point) ? $point->prod_name : null;
                    $a['prod_image'] = isset($point_image) ? $point_image->path : "assets/product_image/_blank.jpg";
                    $a['sub_categ_id'] = isset($point) ? $point->sub_category_id : null;
                    $a['is_promo'] = isset($point) && $point->prod_type_id !== 1 ? (boolean)true : false;
                    
                } else if($info->payment_type === 'TRANSFER' || $info->payment_type === 'WARPAY') {
                    
                    $prod = Product::find($a->product_id);
                    $prod_image = ProductImage::where('id_product', $prod->id)->first();

                    $a['prod_info'] = $a->total_item. ' Barang ('.($prod->prod_gram * $a->total_item) / 1000 .' kg)';
                    $a['prod_id'] = isset($prod) ? $prod->id : null;
                    $a['prod_name'] = isset($prod) ? $prod->prod_name : null;
                    $a['prod_image'] = isset($prod_image) ? $prod_image->path : "assets/product_image/_blank.jpg";
                    $a['sub_categ_id'] = isset($prod) ? $prod->sub_category_id : null;
                    $a['is_promo'] = isset($prod) && $prod->prod_type_id !== 1 ? (boolean)true : false;

                }

                unset($a['product_id']);

            }

            $info['final_total'] = (string) ($info['total_price'] + $info['total_ongkir']);

            
    		return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => [
                	'info'	 => $info,
                	'produk' => $produk
                ]
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function dataTable(Request $request)
    {

    	try{

            $columns = [null, null, 'id', 'no_po', null, null, 'status', 'is_accept_refund', 'buyer_name', 'buyer_id', 'cancel_msg', 'is_pick', 'shipped_start_date', 'created_at', 'updated_at'];

            $wh_id = $request->warehouse_id;
            $status_order = $request->status_order;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $limit = $request->input('length');
            $start = $request->input('start');

            $order = $columns[$request->input('order.0.column')];

            $dir   = $request->input('order.0.dir');

            $data          = [];
            $totalData = 0;

            /* case filter order list
                1. warehouse bukan all, start date || end date kosong & status order kosong
                2. warehouse all, start date || end date kosong & status order kosong
                3. warehouse bukan all, start date && end date ada & status order kosong
                4. warehouse all, start date && end date ada & status order kosong
                5. warehouse bukan all, start date && end date ada & status order ada
                6. warehouse all, start date && end date ada & status order ada
                7. warehouse bukan all, start date || end date kosong & status order ada
                8. warehouse all, start date || end date kosong & status order ada
            */

            $case_filter_order = [
                1 => $wh_id != self::ALL_WAREHOUSE && (empty($start_date) || empty($end_date)) && empty($status_order),
                2 => $wh_id == self::ALL_WAREHOUSE && (empty($start_date) || empty($end_date)) && empty($status_order),
                3 => $wh_id != self::ALL_WAREHOUSE && (!empty($start_date) && !empty($end_date)) && empty($status_order),
                4 => $wh_id == self::ALL_WAREHOUSE && (!empty($start_date) && !empty($end_date)) && empty($status_order),
                5 => $wh_id != self::ALL_WAREHOUSE && (!empty($start_date) && !empty($end_date)) && !empty($status_order),
                6 => $wh_id == self::ALL_WAREHOUSE && (!empty($start_date) && !empty($end_date)) && !empty($status_order),
                7 => $wh_id != self::ALL_WAREHOUSE && (empty($start_date) || empty($end_date)) && !empty($status_order),
                8 => $wh_id == self::ALL_WAREHOUSE && (empty($start_date) || empty($end_date)) && !empty($status_order),
            ];

            if($case_filter_order[1]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    where('warehouse_id', $wh_id)->
                                    count();

            } else if($case_filter_order[2]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    count();

            } else if($case_filter_order[3]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    where('warehouse_id', $wh_id)->
                                    whereBetween(DB::raw('created_at'), [$start_date, $end_date])->
                                    count();

            } else if($case_filter_order[4]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    whereBetween(DB::raw('created_at'), [$start_date, $end_date])->
                                    count();
            } else if($case_filter_order[5]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    where('warehouse_id', $wh_id)->
                                    whereBetween(DB::raw('created_at'), [$start_date, $end_date])->
                                    whereIn('status', $status_order)->
                                    count();
            } else if($case_filter_order[6]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    whereBetween(DB::raw('created_at'), [$start_date, $end_date])->
                                    whereIn('status', $status_order)->
                                    count();
            } else if($case_filter_order[7]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    where('warehouse_id', $wh_id)->
                                    whereIn('status', $status_order)->
                                    count();

            } else if($case_filter_order[8]) {
                
                $totalData = Order::where('deleted_at', NULL)->
                                    whereIn('status', $status_order)->
                                    count();
            }

            $totalFiltered = $totalData;

            $orders         = '';
            $db_table       = DB::table('order AS a')
                            ->select(
                                'a.id', 'a.cancel_msg', 'a.no_po', 'a.warehouse_id', 'a.is_pick', 'c.id AS prod_id', 'c.prod_number', 'c.prod_name', 'd.path AS prod_image', 'e.status_name AS status_po', 
                                'f.id AS buyer_id', 'f.fullname AS buyer_name',  'a.status',  'a.created_at', 'a.updated_at', 'a.shipped_start_date', 'a.shipped_end_date', 'a.warehouse_id',
                                'h.name AS courier_name', 'i.number_resi', 'j.address', 'j.subdistrict_id', 'g.user_address_id', 'j.phone AS user_profile_phone', 'f.fullname AS user_fullname',
                                'g.courier_service', 'a.payment_type', 'a.is_accept_refund', 'k.name as warehouse_name'
                            )
                            ->leftJoin('order_item AS b', function ($join) {
                                $join->on('a.id', '=', 'b.order_id')
                                    ->on(
                                        'b.id', '=', DB::raw("(SELECT min(id) FROM order_item  WHERE order_item.order_id = a.id)")
                                    );
                            })
                            ->leftJoin('product AS c', function ($join) {
                                $join->on('b.product_id', '=', 'c.id');
                            })
                            ->leftJoin('product_image AS d', function ($join) {
                                $join->on('c.id', '=', 'd.id_product')
                                    ->on(
                                        'd.id', '=', DB::raw("(SELECT min(id) FROM product_image WHERE id_product = c.id)")
                                    );
                            })
                            ->leftJoin('master_order_status AS e', function ($join) {
                                $join->on('a.status', '=', 'e.id');
                            })
                            ->leftJoin('users AS f', function ($join) {
                                $join->on('a.user_id', '=', 'f.id');
                            })
                            ->leftJoin('shipment_types AS g', function ($join) {
                                $join->on('a.id', '=', 'g.order_id');
                            })
                            ->leftJoin('couriers AS h', function ($join) {
                                $join->on('g.courier_id', '=', 'h.id');
                            })
                            ->leftJoin('resi AS i', function ($join) {
                                $join->on('a.id', '=', 'i.order_id');
                            })
                            ->leftJoin('user_profile AS j', function ($join) {
                                $join->on('j.user_id', '=', 'f.id');
                            })
                            ->leftJoin('warehouse AS k', function ($join) {
                                $join->on('k.id', '=', 'a.warehouse_id');
                            })
                            ->where('a.deleted_at', NULL);

            if(empty($request->input('search.value'))){

                if($case_filter_order[1]) {

                    _log("1");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[2]) {

                    _log("2");

                    $orders = $db_table
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[3]) {

                    _log("3");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();


                } else if($case_filter_order[4]) {

                    _log("4");

                    $orders = $db_table
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[5]) {

                    _log("5");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->whereIn('a.status', $status_order)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[6]) {

                    _log("6");
                    
                    $orders = $db_table
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->whereIn('a.status', $status_order)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[7]) {

                    _log("7");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->whereIn('a.status', $status_order)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[8]) {

                    _log("8");

                    $orders = $db_table
                        ->whereIn('a.status', $status_order)
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                }



            } else {

                $search = $request->input('search.value');

                if($case_filter_order[1]) {

                    _log("1");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[2]) {

                    _log("2");

                    $orders = $db_table
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[3]) {

                    _log("3");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[4]) {

                    _log("4");

                    $orders = $db_table
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();


                } else if($case_filter_order[5]) {

                    _log("5");

                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->whereIn('a.status', $status_order)
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[6]) {

                    _log("6");

                    $orders = $db_table
                        ->whereBetween(DB::raw('a.created_at'), [$start_date, $end_date])
                        ->whereIn('a.status', $status_order)
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[7]) {

                    _log("7");
                    
                    $orders = $db_table
                        ->where('a.warehouse_id', $wh_id)
                        ->whereIn('a.status', $status_order)
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                } else if($case_filter_order[8]) {

                    _log("8");
                    
                    $orders = $db_table
                        ->whereIn('a.status', $status_order)
                        ->where(function ($query) use ($search) {
                            $query->where('c.prod_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.no_po', 'LIKE', "%" . $search . "%");
                            $query->orWhere('e.status_name', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.created_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('a.updated_at', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.fullname', 'LIKE', "%" . $search . "%");
                            $query->orWhere('f.id', 'LIKE', "%" . $search . "%");
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();
                }

                $totalFiltered = $orders->count();

            }


            if(!empty($orders)){

                $no  = $start + 1;
                $row = 0;


                foreach($orders as $key => $val) {

                    $address = '';
                    $user_fullname = '';
                    $user_profile_phone = '';
                    
                    if($val->user_address_id) {
                     
                        $user_address   = UserAddress::find($val->user_address_id);
                        
                        $address = null;

                        if($user_address) {


                            $user_fullname       = $user_address->receiver_name;
                            $user_profile_phone  = $user_address->receiver_phone; 
                            
                            if($user_address->subdistrict_id) {

                                $sub    = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $user_address->subdistrict_id)->get()[0];
                                $address = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$user_address->address;

                            }

                        }
                    } else if($val->user_address_id !== 0 && $val->user_address_id < 1){
                        
                        $user_fullname       = $val->user_fullname;
                        $user_profile_phone  = $val->user_profile_phone; 
                        
                        $address             = null;

                        if(isset($val->subdistrict_id)) { 
                            $sub                 = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $val->subdistrict_id)->get()[0];
                            $address             = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$val->address;
                        }
                    } else {
                        
                        $user_fullname       = $val->user_fullname;
                        $user_profile_phone  = $val->user_profile_phone; 
                        
                        $address             = null;

                        if(isset($val->subdistrict_id)) { 
                            $sub                 = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $val->subdistrict_id)->get()[0];
                            $address             = $sub->province.', '.$sub->type.'.'.$sub->city.', '.$sub->subdistrict_name.', '.$val->address;
                        }
                    }


                    $d = [];

                    $d['no']                = $no++;
                    $d['id']                = $val->id;
                    $d['prod_image']        = $val->prod_image;
                    $d['prod_name']         = $val->prod_name;
                    $d['no_po']             = $val->no_po;
                    $d['no_po_principle']   = POPrinciple::where('order_id', $val->id)->select('no_po AS no_po_principle')->get();
                    $d['prod_number']       = $val->prod_number;
                    $d['status_po']         = $val->status_po;
                    $d['buyer_id']          = $val->buyer_id;
                    $d['buyer_name']        = $val->buyer_name;
                    $d['cancel_msg']        = $val->cancel_msg;
                    $d['is_pick']           = $val->is_pick;
                    $d['is_accept_refund']  = $val->is_accept_refund;
                    $d['courier_name']      = $val->courier_name;
                    $d['courier_service']   = $val->courier_service;
                    $d['number_resi']      = $val->number_resi;
                    // $d['shipped_date'] = \Carbon\Carbon::parse($val->shipped_start_date)->format('Y-m-d').' ('.\Carbon\Carbon::parse($val->shipped_start_date)->format('H:i').' - '.\Carbon\Carbon::parse($val->shipped_end_date)->format('H:i').')';
                    $d['shipped_date']      = isset($val->shipped_start_date)  ? \Carbon\Carbon::parse($val->shipped_start_date)->format('Y-m-d').' ('.\Carbon\Carbon::parse($val->shipped_start_date)->format('H:i').' - '.\Carbon\Carbon::parse($val->shipped_end_date)->format('H:i').')' : null;
                    $d['created_at']        = $val->created_at;
                    $d['updated_at']        = $val->updated_at;
                    $d['warehouse_id']      = $val->warehouse_id;
                    $d['warehouse_name']      = $val->warehouse_name;

                    $d['address']                 = $address;
                    $d['user_profile_phone']      = $user_profile_phone;
                    $d['user_full_name']          = $user_fullname;
                    $row++;
                    $data[] = $d;
                }

            }


            $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

            return json_encode($json_data);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }


    public function updateQtyOrderItem(Request $request, $id) {

        DB::beginTransaction();

        try {

            $order_item                     = OrderItem::find($id);

            $order_id                       = $order_item->order_id;
            $product_id                     = $order_item->product_id;
            $principle_id                   = $order_item->product->principle_id;

            $order                          = Order::find($order_id);

            $profile                        = UserProfile::where('user_id', $order->user_id)->first();

            $wh_id                          = $order->warehouse_id;


            /***********************************************
             **** update order item | set total before 0 ****
            ***********************************************/

            $order_item->total_item_before  = $order_item->total_item;
            $TOTAL_ITEM_BEFORE              = (int) $order_item->total_item_before;
            $order_item->total_item         = 0;
            $TOTAL_ITEM                     = (int) $order_item->total_item;
            $order_item->save();


            /***********************************************
             ************** warpay convertion **************
            ***********************************************/

            $PROD_BASE_PRICE_BY_O_ITEM      = (int) $order_item->product->prod_base_price;
            $WARPAY_CONVERTION              = floor( (int) $PROD_BASE_PRICE_BY_O_ITEM / $this->__convertionWarpay() );
            $TOTAL_WARPAY_REFUND            = ( ($TOTAL_ITEM_BEFORE - $TOTAL_ITEM) * $WARPAY_CONVERTION );


            /***********************************************
             ********** find order items by order **********
            ***********************************************/

            $order_items              = OrderItem::where('order_id', $order_id)->get();


            /***********************************************
             ***** re-calculate grand total di order*********
            ***********************************************/

            $GRAND_TOTAL                    = 0;
            foreach($order_items as $item) {
                $GRAND_TOTAL += ( $item->total_item * $item->price );
            }

            $order->total_price         = $GRAND_TOTAL;
            $order->final_total         = ( $GRAND_TOTAL + $order->total_ongkir );
            $order->save();



            /***********************************************
             **************  find stock product *************
            ***********************************************/

            $param = [
                'WHERE_STOCK_PRODUCT'       => [
                    'warehouse_id'  => $wh_id,
                    'product_id'    => $product_id,
                ]
            ];

            $stock_product                  = StockProduct::where($param['WHERE_STOCK_PRODUCT'])->first();
            $CURRENT_STOCK                  = $stock_product->stock;


            /*******************************************************
             ************ update stock product ********************
            *******************************************************/

            $param['UPDATE_STOCK_PRODUCT'] = [
                    'stock'          => ( $CURRENT_STOCK + ($TOTAL_ITEM_BEFORE - $TOTAL_ITEM) )
            ];

            $stock_product->update($param['UPDATE_STOCK_PRODUCT']);


            /*******************************************************
             ************ delete po principle ********************
            *******************************************************/
            /*
            $param['WHERE_PO_PRINCIPLE'] = [
                    'order_id'      => $order_id,
                    'principle_id'  => $principle_id
            ];

            $po_principle               = POPrinciple::where($param['WHERE_PO_PRINCIPLE'])->first();
            $po_principle->delete();
            */

            /*******************************************************
             ************ refund warpay pengguna  ******************
            *******************************************************/

            if($profile && $TOTAL_WARPAY_REFUND) {

                $param['UPDATE_USER_PROFILE'] = [
                    'warpay'          => ( $profile->warpay + $TOTAL_WARPAY_REFUND)
                ];

                $update_profile = $profile->update($param['UPDATE_USER_PROFILE']);

                // 3. History In Warpay 
                HistoryInOutWp::create(['type' => SELF::PLUS, 'user_id' => $profile->user_id, 'total' => $TOTAL_WARPAY_REFUND, 'warpay_prev' => $profile->warpay, 'by' => $request->by]);
                
                $this->initNotification('Refund Warpay', 'Penambahan warpay sebesar '.$TOTAL_WARPAY_REFUND.' ke akunmu', $profile->user->device_token);


                if(!$update_profile){
                    DB::rollback();
                    return response()->json([
                        'status' => false,
                        'message' => 'Warpay tidak bertambah.'
                    ], 500);
                }

            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diperbarui.',
            ], 200);


        } catch(Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }


    }

    public function update(Request $request, $id, $desc) {

        $objectDesc = [
            'a' => 'process-with-change-stock',
            'b' => 'cancel-without-change-stock',
            'c' => 'process-without-change-stock',
            'd' => 'send-with-add-number-resi',
            'e' => 'pesanan-siap-diambil',
            'f' => 'pesanan-selesai',
        ];

        DB::beginTransaction();

            switch ($desc) {

                case $objectDesc['a']:

                    try {

                        $order                  = Order::find($id);

                        $user                   = User::find($order->user_id);

                        $profile                = UserProfile::where('user_id', $order->user_id)->first();

                        $wh_id                  = $order->warehouse_id;

                        if(isset($request->total_stock_order_item) && isset($request->total_price_order_item)) {

                            /**********************************************/
                            $TOTAL_STOCK_AND_PRICE   = [
                                'STOCK' => $request->total_stock_order_item,
                                'PRICE' => $request->total_price_order_item
                            ];
                        /**********************************************/
                           $TOTAL_WARPAY_REFUND = 0;
    
                            foreach($TOTAL_STOCK_AND_PRICE as $INDEX => $ORDER_ITEM_IDS) {

                                if($INDEX === 'STOCK') {

                                    foreach($ORDER_ITEM_IDS as $id => $TOTAL_STOCK) {

                                        $order_item                     = OrderItem::find($id);
                                        $order_item->total_item_before  = $order_item->total_item;

                                        $TOTAL_ITEM_BEFORE              = $order_item->total_item_before;
                                        $order_item->total_item         = $TOTAL_STOCK;
                                        $TOTAL_ITEM                     = $order_item->total_item;

                                        /***********************************************
                                         ************** warpay convertion **************
                                        ***********************************************/

                                        // $PROD_BASE_PRICE_BY_O_ITEM      = (int) $order_item->product->prod_base_price;
                                        $PROD_BASE_PRICE_BY_O_ITEM      = (int) $order_item->price;
                                        $WARPAY_CONVERTION              = floor((int) $PROD_BASE_PRICE_BY_O_ITEM / $this->__convertionWarpay());
                                        $TOTAL_WARPAY_REFUND            += ( ($TOTAL_ITEM_BEFORE - $TOTAL_ITEM) * $WARPAY_CONVERTION );
                            

                                        /***********************************************
                                         ************** find stock product *************
                                        ***********************************************/

                                        $param = [
                                            'WHERE_STOCK_PRODUCT'       => [
                                                'warehouse_id'  => $wh_id,
                                                'product_id'    => $order_item->product_id,
                                            ]
                                        ];

                                        $stock_product                  = StockProduct::where($param['WHERE_STOCK_PRODUCT'])->first();
                                        $CURRENT_STOCK                  = $stock_product->stock;

                                        /*******************************************************
                                         ************ upt order item & stock product ***********
                                        *******************************************************/

                                        $param['UPDATE_STOCK_PRODUCT'] = [
                                                'stock'          => ( $CURRENT_STOCK + ($TOTAL_ITEM_BEFORE - $TOTAL_ITEM) )
                                        ];

                                        $order_item->save();
                                        $stock_product->update($param['UPDATE_STOCK_PRODUCT']);

                                    }

                                }

                                if($INDEX === 'PRICE') {

                                    foreach($ORDER_ITEM_IDS as $id => $TOTAL_PRICE) {

                                        $order_item                     = OrderItem::find($id);
                                        $order_item->total_price        = $TOTAL_PRICE;

                                        $order_item->save();

                                    }

                                }

                            }



                            /*******************************************************
                             ************ refund warpay pengguna  ******************
                            *******************************************************/


                            if($profile && $TOTAL_WARPAY_REFUND) {

                                $param['UPDATE_USER_PROFILE'] = [
                                    'warpay'          => ( $profile->warpay + (int)$TOTAL_WARPAY_REFUND )
                                ];

                                $update_profile = $profile->update($param['UPDATE_USER_PROFILE']);

                                // 3. History In Warpay 
                                HistoryInOutWp::create(['type' => SELF::PLUS, 'user_id' => $profile->user_id, 'total' => (int)$TOTAL_WARPAY_REFUND, 'warpay_prev' => $profile->warpay, 'by' => $request->by]);
                                $this->initNotification('Refund Warpay', 'Penambahan warpay sebesar '.(int)$TOTAL_WARPAY_REFUND.' ke akunmu', $user->device_token);


                                if(!$update_profile){
                                    DB::rollback();
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'Warpay tidak bertambah.'
                                    ], 500);
                                }

                            }


                        }
                        /*******************************************************
                         ************ update order & create principle **********
                        *******************************************************/

                        $order->update($request->except('token', 'email', 'by', 'total_item'));

                        $order_items = OrderItem::where('order_id', $order->id)->get();


                        foreach($order_items as $index => $a) {

                            if($a->total_item > 0) {
                                    
                                $data_po_principle = [
                                    'order_id'     => $order->id,
                                    'principle_id' => $a->product->principle_id
                                ];

                                $get_po_principle = POPrinciple::where($data_po_principle)->first();

                                if(!$get_po_principle){

                                    $data_po_principle['no_po'] = rand(10000000, 99999999);
                                    $po_principle = POPrinciple::create($data_po_principle);

                                    $data_po_principle['no_po'] = $this->GeneratePOPrinciple($po_principle);

                                    $po_principle = POPrinciple::where('id', $po_principle->id)->update([
                                        'no_po' => $data_po_principle['no_po']
                                    ]);

                                }
                                
                            }

                        }

                        /* ------------------------------ Notification ------------------------------ */

                        $status_name = MasterOrderStatus::find($request->status)->name;
                        $this->initNotification($status_name, $order->no_po, $user->device_token);

                        DB::commit();

                        return response()->json([
                            'status' => true,
                            'message' => 'Data berhasil diperbarui.',
                            'data'    => $order
                        ], 200);

                    } catch (Exception $e) {

                        DB::rollback();
                        
                        return __jsonResp(false, $e->getMessage(), 500, $e);

                    }

                break;

                case $objectDesc['b']:

                    try {

                        $order                  = Order::find($id);

                        $user                   = User::find($order->user_id);

                        //init variable
                        $total_warpay = 0;
                        $total_ongkir = 0;
                        $total_point  = 0;


                        //qty to check type refund
                        $total_diff_qty = 0;
                        //init next status
                        $status = 8;
                        //check prod per order

                        $check_ord_details = OrderItem::where('order_id',$id)
                        ->leftJoin('order','order.id','=','order_item.order_id')
                        ->leftJoin('product','product.id','=','order_item.product_id')
                        ->leftJoin('promosi AS d', 'd.id', '=', 'order_item.promosi_id')
                        ->leftJoin('promosi_item AS e', 'e.promosi_id', '=', 'order_item.promosi_id')
                        ->select(
                            'order_item.*',
                            'order.warehouse_id as warehouse_id',
                            'product.prod_base_price as prod_base_price',
                            'order.total_ongkir',
                            'd.promosi_type',
                            'd.total_value AS promosi_total_value',
                            'e.fix_value AS promosi_fix_value'
                        )
                        // ->groupBy('order_item.product_id')
                        ->first();


                        if($check_ord_details->promosi_type) {
                        
                            $order_details = OrderItem::where('order_id',$id)
                            ->leftJoin('order','order.id','=','order_item.order_id')
                            ->leftJoin('product','product.id','=','order_item.product_id')
                            ->leftJoin('promosi AS d', 'd.id', '=', 'order_item.promosi_id')
                            ->leftJoin('promosi_item AS e', 'e.promosi_id', '=', 'order_item.promosi_id')
                            ->join('master_stock AS f', function ($join) {
                                $join->on('f.id', '=', 'e.stock_id')
                                ->on('f.product_id', '=', 'order_item.product_id');
                            })
                            ->select(
                                'order_item.*',
                                'order.warehouse_id as warehouse_id',
                                'product.prod_base_price as prod_base_price',
                                'order.total_ongkir',
                                'd.promosi_type',
                                'd.total_value AS promosi_total_value',
                                'e.fix_value AS promosi_fix_value'
                            )
                            ->get();
    
                        
                        } else {
               
                            $order_details = OrderItem::where('order_id',$id)
                            ->leftJoin('order','order.id','=','order_item.order_id')
                            ->leftJoin('product','product.id','=','order_item.product_id')
                            ->leftJoin('promosi AS d', 'd.id', '=', 'order_item.promosi_id')
                            ->leftJoin('promosi_item AS e', 'e.promosi_id', '=', 'order_item.promosi_id')
                            ->select(
                                'order_item.*',
                                'order.warehouse_id as warehouse_id',
                                'product.prod_base_price as prod_base_price',
                                'order.total_ongkir',
                                'd.promosi_type',
                                'd.total_value AS promosi_total_value',
                                'e.fix_value AS promosi_fix_value'
                            )
                            ->get();
    
                        }

                        if(is_null($order_details)){
                            return response()->json([
                                'status' => false,
                                'message' => 'Order tidak ditemukan',
                                'error'   => 'Order tidak ditemukan'
                            ], 500);
                        }
                        //check qty per order
                        foreach($order_details as $order_detail){

                            if ($order->payment_type == 'point') {

                                $stock = StockProductPoint::where([
                                    'warehouse_id' => $order_detail->warehouse_id,
                                    'product_point_id'   => $order_detail->product_id
                                ])->first();

                                if(is_null($stock)){
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'Stock tidak ditemukan',
                                        'error'   => 'Stock tidak ditemukan'
                                    ], 500);
                                }

                                //get qty different konfirmasi
                                $diff_qty = $order_detail->total_item;

                                //add stock to warehouse with product_id & warehouse_id
                                $stock = StockProductPoint::where('id', $stock->id)
                                    ->update(['stock'=>(int)$stock->stock + $diff_qty]);

                                if(!$stock){
                                    DB::rollback();
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'stock tidak ditemukan'
                                    ], 500);
                                }

                            } else {

                                //get stock
                                $stock = StockProduct::where([
                                    'warehouse_id' => $order_detail->warehouse_id,
                                    'product_id'   => $order_detail->product_id
                                ])->first();

                                if(is_null($stock)){
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'Stock tidak ditemukan',
                                        'error'   => 'Stock tidak ditemukan'
                                    ], 500);
                                }

                                //get qty different konfirmasi
                                $diff_qty = $order_detail->total_item;

                                //add stock to warehouse with product_id & warehouse_id
                                $stock = StockProduct::where('id',$stock->id)
                                    ->update(['stock'=>(int)$stock->stock + $diff_qty]);

                                if(!$stock){
                                    DB::rollback();
                                    return response()->json([
                                        'status' => false,
                                        'message' => 'stock tidak ditemukan'
                                    ], 500);
                                }

                            }
                            
                            // cara lama 
                            //convertion to warpay
                            // $warpay_convertion = ceil( (int) $order_detail->prod_base_price / $this->__convertionWarpay()); // cara lama
                            
                            // $warpay_convertion = 0;

                            // if(isset($order_detail->promosi_type)) {

                            //     if($order_detail->promosi_type == 1 ) {
                                    
                            //         $warpay_convertion = (int)$order_detail->promosi_total_value / $this->__convertionWarpay(); 

                            //     } else if ($order_detail->promosi_type == 2 ) {
                                
                            //         $warpay_convertion = (int)$order_detail->promosi_fix_value / $this->__convertionWarpay(); 
                                
                            //     }
                            // } else {

                            //         $warpay_convertion = (int)$order_detail->prod_base_price / $this->__convertionWarpay();
                            // }




                            //get total price refund warpay

                            // cara baru
                                $warpay_convertion = floor( (int)$order_detail->price / $this->__convertionWarpay());

                                $total_warpay += (int)$diff_qty * $warpay_convertion;
                                $total_ongkir = (int) $order_detail->total_ongkir /  $this->__convertionWarpay();

                                $order_detail = OrderItem::find($order_detail->id);

                                $order_detail->total_item = 0;
                                $order_detail->save();
                        }

                        //get current user warpay
                        $profile = UserProfile::where('user_id',$order->user_id)
                        ->first();

                        if($order->payment_type !== 'point') {

                            //add warpay current user amount + total price refund warpay
                            $add_warpay = $profile->warpay + floor($total_warpay + $total_ongkir);
                            
                            // $update_profile = UserProfile::where('user_id',$order->user_id)
                            //     ->update(['warpay'=>$add_warpay]);

                            $profile->warpay = $add_warpay;
                            $profile->save();

                            // 3. History In Warpay 
                            HistoryInOutWp::create(['type' => SELF::PLUS, 'user_id' => $profile->user_id, 'total' => ceil($total_warpay) + ceil($total_ongkir), 'warpay_prev' => $profile->warpay, 'by' => $request->by]);

                            $this->initNotification('Refund Warpay', 'Penambahan warpay sebesar '.ceil($total_warpay).' ke akunmu', $user->device_token);

                            if(!$profile){
                                DB::rollback();
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Warpay tidak bertambah.'
                                ], 500);
                            }

                        } else {

                            //add point current user amount + total price refund point
                            $add_point = $profile->point + $order->final_total;
                        
                            $profile->point = $add_point;
                            $profile->save();

                            $this->initNotification('Refund Point', 'Penambahan point sebesar '.$order->final_total.' ke akunmu', $user->device_token);

                            if(!$profile){
                                DB::rollback();
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Point tidak bertambah.'
                                ], 500);
                            }


                        }


                        $order->cancel_msg = $request->cancel_msg;
                        $order->status = $status;
                        $order->save();

                        /* ------------------------------ Notification ------------------------------ */

                        $status_name = MasterOrderStatus::find($status)->name;
                        $this->initNotification($status_name, $order->no_po ? $order->no_po : 'Produk Point', $user->device_token);

                        DB::commit();

                        return response()->json([
                            'status' => true,
                            'message' => 'Pesanan berhasil dibatalkan.',
                            'data'    => $order
                        ], 200);

                    } catch (Exception $e) {

                        DB::rollback();

                        return __jsonResp(false, $e->getMessage(), 500, $e);

                    }

                break;

                case $objectDesc['c']:

                    try {

                        // find order
                        $order = Order::find($id);

                        $user                   = User::find($order->user_id);

                        // find order items
                        $order_items = OrderItem::find($id);

                        // update order
                        $order->update($request->except('token', 'email', 'by'));

                        $order_item = OrderItem::where('order_id', $order->id)->get();

                        foreach($order_item as $index => $a){
                            
                            if($a->total_item > 0) {

                                if($a->product && $a->product->principle_id) {
                                        
                                    $data_po_principle = [
                                        'order_id'     => $order->id,
                                        'principle_id' => $a->product->principle_id
                                    ];

                                    $get_po_principle = POPrinciple::where($data_po_principle)->first();

                                    if(!$get_po_principle){

                                        $data_po_principle['no_po'] = rand(10000000, 99999999);
                                        $po_principle = POPrinciple::create($data_po_principle);

                                        $data_po_principle['no_po'] = $this->GeneratePOPrinciple($po_principle);

                                        $po_principle = POPrinciple::where('id', $po_principle->id)->update([
                                            'no_po' => $data_po_principle['no_po']
                                        ]);

                                    }
                                }
                            }

                        }

                        /* ------------------------------ Notification ------------------------------ */

                        $status_name = MasterOrderStatus::find($request->status)->name;
                        $this->initNotification($status_name, $order->no_po, $user->device_token);

                        DB::commit();

                        return response()->json([
                            'status' => true,
                            'message' => 'Data berhasil diperbarui.',
                            'data'    => $order
                        ], 200);

                    } catch (Exception $e) {

                        DB::rollback();
                        
                        return __jsonResp(false, $e->getMessage(), 500, $e);

                    }

                break;

                case $objectDesc['d']:

                    try{
                        

                        $rules = [
                            'number_resi'          => 'required|string|unique:resi,deleted_at',
                        ];
            
                        $validator = Validator::make($request->all(), $rules);

                        if ($validator->fails()) {
                            return response()->json([
                                'message' => ucfirst($validator->errors()->first())
                            ], 400);
                        }
            
                        // find order
                        $order = Order::find($id);

                        $user                   = User::find($order->user_id);

                        $order_id = $order->id;

                        // update order
                        $order->update($request->only('status'));

                        Resi::create(['order_id' => $order_id, 'number_resi' => $request->number_resi]);

                        /* ------------------------------ Notification ------------------------------ */

                        $status_name = MasterOrderStatus::find($request->status)->name;
                        $this->initNotification($status_name, $order->no_po, $user->device_token);
                        
                        DB::commit();

                        return response()->json([
                            'status' => true,
                            'message' => 'Data berhasil diperbarui.',
                            'data'    => $order
                        ], 200);

                    } catch (Exception $e) {

                        DB::rollback();
                        
                        return __jsonResp(false, $e->getMessage(), 500, $e);

                    }

                break;

                case $objectDesc['e']:

                    try {


                        $date_start = $request->shipped_date.' '.$request->start_time;
                        $date_end = $request->shipped_date.' '.$request->end_time;

                        $request['shipped_start_date'] = \Carbon\Carbon::parse($date_start)->format('Y-m-d H:i');
                        $request['shipped_end_date'] = \Carbon\Carbon::parse($date_end)->format('Y-m-d H:i');

                        // find order
                        $order = Order::find($id);

                        $user                   = User::find($order->user_id);
                       
                        $order_id = $order->id;

                        // update order
                        $order->update($request->only('status', 'shipped_start_date', 'shipped_end_date'));

                        /* ------------------------------ Notification ------------------------------ */

                        $status_name = MasterOrderStatus::find($request->status)->name;
                        $this->initNotification($status_name, $order->no_po, $user->device_token);

                        DB::commit();

                        return response()->json([
                            'status' => true,
                            'message' => 'Data berhasil diperbarui.',
                            'data'    => $order
                        ], 200);


                    } catch (Exception $e) {

                        DB::rollback();

                        return __jsonResp(false, $e->getMessage(), 500, $e);

                    }

                break;

                case $objectDesc['f']:

                    try {

                        /***********************************************
                         **** change status order -> pesanan selesai ****
                        ***********************************************/

                        $order = Order::find($id);
                        $user = User::find($order->user_id);

                        $param['UPDATE_ORDER'] = [
                                'status'    => 6
                        ];

                        $order->update($param['UPDATE_ORDER']);

                        /* ------------------------------ Notification ------------------------------ */

                        $status_name = MasterOrderStatus::find(6)->name;
                        $this->initNotification($status_name, $order->no_po, $user->device_token);

                        DB::commit();

                        return response()->json([
                            'status' => true,
                            'message' => 'Data berhasil diperbarui.',
                            'data'    => $order
                        ], 200);


                    } catch (Exception $e) {

                        DB::rollback();

                        return __jsonResp(false, $e->getMessage(), 500, $e);

                    }

                break;
            }

    }

    public function OrderItemByOrderId(Request $request, $id) {
        try{

            $order_item = [];
            $products = [];
            $order =  DB::table('order')
                ->leftJoin('master_order_status AS a', 'a.id', '=', 'order.status')
                ->leftJoin('user_profile AS b', 'b.user_id', '=', 'order.user_id')
                ->leftJoin('users AS c', 'c.id', '=', 'order.user_id')
                ->select('order.*', 'b.phone AS user_profile_phone', 'b.address AS user_profile_address', 'c.fullname AS user_fullname',
                        'a.status_name AS status_po')
                ->where('order.deleted_at', null)
                ->where('order.id', $id)
                ->first();


                $order_item = DB::table('order_item AS a')
                // ->leftJoin('product AS b', function ($join) {
                //     $join->on('a.product_id', '=', 'b.id');
                // })
                // ->leftJoin('product_image AS c', function ($join) {
                //     $join->on('b.id', '=', 'c.id_product')
                //         ->on(
                //             'c.id', '=', DB::raw("(SELECT min(id) FROM product_image WHERE id_product = b.id)")
                //         );
                // })
                ->leftJoin('promosi AS d', 'd.id', '=', 'a.promosi_id')
                ->leftJoin('promosi_item AS e', 'e.promosi_id', '=', 'a.promosi_id')
                ->leftJoin('master_stock AS f', function ($join) {
                        $join->on('f.id', '=', 'e.stock_id')
                        ->on('f.product_id', '=', 'a.product_id');
                })
                ->where('a.order_id', $id)
                ->where('a.deleted_at', NULL)
                ->where('e.deleted_at', NULL)
                ->select(
                    'a.*',
                    'e.id AS promosi_item_id',
                    // 'b.prod_number', 
                    // 'b.prod_name', 
                    // 'b.prod_base_price', 
                    // 'b.prod_gram', 
                    // 'c.path', 
                    'f.id AS id_master_stock',
                    'f.product_id AS product_id_master_stock',
                    'd.promosi_name',
                    'd.promosi_type',
                    'd.total_value AS promosi_total_value',
                    'e.fix_value AS promosi_fix_value')
                // ->groupBy('e.stock_id')
                ->get();

                $new_order_items = [];

                foreach($order_item as $k => $a) {
                    if(!(isset($a->promosi_id) && is_null($a->id_master_stock))) {
                        array_push($new_order_items, $a);
                    }
                }


                foreach($new_order_items as $k => $a) {
                    
                    if($order->payment_type === 'point') {

                        $point  = ProductPoint::find($a->product_id);
                        $point_image = isset($point) ? ProductPointImage::where('product_point_id', $point->id)->first() : null;

                        $a->prod_name = isset($point) ? $point->prod_name : null;
                        $a->prod_number = isset($point) ? $point->prod_number : null;
                        $a->prod_base_price = isset($point) ? $point->prod_base_point : null;
                        $a->prod_gram = isset($point) ? $point->prod_gram : null;
                        $a->path = isset($point_image) ? $point_image->path : "assets/product_image/_blank.jpg";
                        
                    } else if($order->payment_type === 'transfer' || $order->payment_type === 'warpay') {
                        
                        $prod = Product::find($a->product_id);
                        $prod_image = isset($prod) ? ProductImage::where('id_product', $prod->id)->first() : null;
    
                        $a->prod_name = isset($prod) ? $prod->prod_name : null;
                        $a->prod_number = isset($prod) ? $prod->prod_number : null;
                        $a->prod_base_price = isset($prod) ? $prod->prod_base_price : null;
                        $a->prod_gram = isset($prod) ? $prod->prod_gram : null;
                        $a->path = isset($prod_image) ? $prod_image->path : "assets/product_image/_blank.jpg";
                    }
                        
                    unset($a->product_id);

                }


                // Pesanan dibatalkan
                if($order->status === 8) {
                    foreach($new_order_items as $a) {
                        $a->total_item = 1;
                    }
                }


                $arr = ['PRODUCT_WARPAY' => 0];
                if($order->payment_type === 'warpay') {

                    $arr['ONGKIR_WARPAY'] = (floor((int)$order->total_ongkir / $this->__convertionWarpay()));

                    foreach($new_order_items as $a) {

                        $warpay_convertion = floor( (int)$a->price / $this->__convertionWarpay());
                        $total_warpay = (int)$a->total_item * $warpay_convertion;
        
                        $arr['PRODUCT_WARPAY'] += (int)$total_warpay;
        
                    }

                    $arr['FINAL_TOTAL_WARPAY'] = $arr['PRODUCT_WARPAY'] + $arr['ONGKIR_WARPAY'];

                }

            foreach ($arr as $key => $value) {
                $order->$key = $value;
            }                


            $data = [
                'order_items' => $new_order_items,
                'order' => $order
            ];

            return response()->json([
                'status' => true,
                'message' => 'Berhasil ambil data',
                'data' => $data,
            ], 200);

        }catch(\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }

    }


    public function generatePaymentToken($id)
	{
        try{
            $this->initPaymentGateway();

            $order = Order::find($id);

            //dd($order);
            if($order) {

                $customer = UserProfile::where('user_id', $order->user_id)->with('user')->first();

                //dd($customer);

                $customerDetails = [
                    'first_name' => $customer->user->fullname,
                    'email' => $customer->user->email,
                    'phone' => $customer->phone,
                ];

                $params = [
                    'enable_payments' => Payment::PAYMENT_CHANNELS,
                    'transaction_details' => [
                        'order_id' => $order->no_po,
                        'gross_amount' => $order->final_total,
                    ],
                    'customer_details' => $customerDetails,
                    'expiry' => [
                        'start_time' => date('Y-m-d H:i:s T'),
                        'unit' => Payment::EXPIRY_UNIT['minute'],
                        'duration' => Payment::EXPIRY_DURATION,
                    ],
                ];

                // dd($params);

                $orderDate = date('Y-m-d H:i:s');

                //$paymentDue = (new \DateTime($orderDate))->modify('+7 day')->format('Y-m-d H:i:s');

                $paymentDue = (new \DateTime($orderDate))->modify('+5 minute')->format('Y-m-d H:i:s');

                $snap = Snap::createTransaction($params);
                // dd($snap);

                if ($snap->token) {
                    $order->payment_due = $paymentDue;
                    $order->payment_token = $snap->token;
                    $order->payment_url = $snap->redirect_url;
                    $order->save();
                }

                return response()->json([
                    'status' => true,
                    'data' => ['snap' => $snap],
                ], 200);

            }

            return response()->json([
                'status' => true,
                'message' => 'Order tidak ditemukan.',
            ], 200);

        } catch(Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }

	}

    public function total($warehouse_id)
    {
        try{

            $total_order = 0;

            if($warehouse_id == 0){

                $total_order = Order::where(['status' => '2'])->count();

            }else{

                $total_order = Order::where(['warehouse_id' => $warehouse_id, 'status' => '2'])->count();

            }

            return response()->json($total_order);

        }catch(\Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    private function GeneratePO($warehouse_id, $user_id, $order_id)
    {
        $code_1 = 'PO';
        $code_2 = 'SGS';
        $code_3 = date('m');
        $code_4 = date('y');
        $code_5 = str_pad($order_id, 6, "0", STR_PAD_LEFT);

    	$po = $code_1 . '-' . $code_2 . '/' . $code_3 . '/' . $code_4 . '/' . $code_5;

    	return $po;
    }

    private function GeneratePOPrinciple($po_principle)
    {
        $code_1 = 'PO';
        $code_2 = Principle::find($po_principle->principle_id)->code;
        $code_3 = date('m');
        $code_4 = date('y');

        $po = $code_1 . '-' . $code_2 . '/' . $code_3 . '/' . $code_4;

        $first = explode('/', $po)[0] . '/';

        $no_po = POPrinciple::where('no_po' , 'like', '%' . $first . '%')
                            ->orderBy('id', 'desc')
                            ->first();

        if($no_po){

            $queue  = explode('/', $no_po)[3];
            $code_5 = (int)$queue + 1;
            $code_5 = str_pad($code_5, 6, "0", STR_PAD_LEFT);

        }else{

            $code_5 = str_pad('1', 6, "0", STR_PAD_LEFT);

        }

        $po .= '/' . $code_5;

        return $po;
    }
    public function getListStatusOrder() {
        try {
            $status_order = MasterOrderStatus::get();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $status_order
            ], 200);

        } catch(Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function refund_warpay(Request $request)
    {
        try{

            # code...
            //validation
            $input = $request->all();
            $validator = Validator::make($input, [
                'user_response' => 'required',
                'payment_type' => 'required',
                'order_id' => 'required',
                'email' => 'required'
                ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi request tidak valid',
                    'error'   => $validator->errors()
                ], 500);
            }
            //init variable
            $total_warpay = 0;
            //0=half 1=full
            $type_refund = 0;
            //qty to check type refund
            $total_qty = 0;
            $total_diff_qty = 0;
            //init next status
            $status = 8;

            DB::beginTransaction();
            $order = Order::find($request->order_id);
            //check user response 1 = terima, 2 = batalkan order
            if($request->user_response){

                //check prod per order
                $order_details = OrderItem::where('order_id',$request->order_id)
                ->leftJoin('order','order.id','=','order_item.order_id')
                ->leftJoin('product','product.id','=','order_item.product_id')
                ->select(
                    'order_item.*',
                    'order.warehouse_id as warehouse_id',
                    'product.prod_base_price as prod_base_price',
                    'product.prod_base_price as prod_base_price',
                )
                ->withTrashed()
                ->get();

                if(is_null($order_details)){
                    return response()->json([
                        'status' => false,
                        'message' => 'Order tidak ditemukan',
                        'error'   => 'Order tidak ditemukan'
                    ], 500);
                }
                //check qty per order
                foreach($order_details as $order_detail){
                    //get stock
                    $stock = StockProduct::where([
                        'warehouse_id' => $order_detail->warehouse_id,
                        'product_id'   => $order_detail->product_id
                    ])->first();
                    if(is_null($stock)){
                        return response()->json([
                            'status' => false,
                            'message' => 'Stock tidak ditemukan',
                            'error'   => 'Stock tidak ditemukan'
                        ], 500);
                    }
                    //get qty different konfirmasi
                    $qty = 0;
                    $qty = is_null($order_detail->total_item_before) ? (int)$order_detail->total_item : (int)$order_detail->total_item_before;
                    $diff_qty = 0;
                    $diff_qty = is_null($order_detail->total_item_before) ? 0 : (int)$order_detail->total_item_before - (int)$order_detail->total_item;


                    $total_qty += $qty;
                    $total_diff_qty += $diff_qty;
                    //add stock to warehouse with product_id & warehouse_id
                    $stock = StockProduct::where('id',$stock->id)
                            ->update(['stock'=>(int)$stock->stock + $diff_qty]);
                    if(!$stock){
                        DB::rollback();
				        return response()->json([
				        	'status' => false,
    			        	'message' => 'stock tidak ditemukan'
    			        ], 500);
                    }
                    //convertion to warpay
                    // $warpay_convertion = ceil( (int)$order_detail->prod_base_price / $this->__convertionWarpay()); // cara lama
                    $warpay_convertion = (int)$order_detail->prod_base_price / $this->__convertionWarpay();
                    //get total price refund warpay
                    $total_warpay += (int)$diff_qty * $warpay_convertion;
                }
                //init type refund for future
                $type_refund = (int) $total_qty - $diff_qty == 0 ? 1 : 0;

                //get current user warpay
                $profile = UserProfile::where('user_id',$order->user_id)
                            ->first();

                if(is_null($profile)){
                    DB::rollback();
				    return response()->json([
				    	'status' => false,
    			    	'message' => 'userprofile Not Found'
    			    ], 500);
                }

                //add warpay current user amount + total price refund warpay
                $add_warpay = $profile->warpay + ceil($total_warpay);
                //save warpay to user account
                $update_profile = UserProfile::where('user_id',$order->user_id)
                        ->update(['warpay'=>$add_warpay]);

                
                // History WP in 
                HistoryInOutWp::create(['type' => SELF::PLUS, 'user_id' => $profile->user_id, 'total' => ceil($total_warpay), 'warpay_prev' => $update_profile->warpay]);

                if(!$update_profile){
                    DB::rollback();
                    return response()->json([
                        'status' => false,
                        'message' => 'Warpay tidak bertambah.'
                    ], 500);
                }
                //
                $status = 3;
            }

            //change status order
            $update_order = Order::where('id',$request->order_id)
                        ->update(['status'=>$status]);

            if(!$update_order){
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'update order gagal.'
                ], 500);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil disimpan.',
                "data" => $order
            ], 200);
        }catch(Exception $e){
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
