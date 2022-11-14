<?php

//  ^(?=.*\d)(?=.*[A-z])([^\s]){5,16}$
// update db | update env | api | web, tambahin url ini
// sgs.ns2solution.com = WEB_URL=http://sgs.ns2solution.com/
// sgs.ns2solution.com = WEB_URL=http://sgswarrior.lspsgs.co.id/

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client as GuzzleClient;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\AkumulasiPointPerMonth;
use App\Order;


$router->get('/', function () use ($router) {
    // dd(Hash::make('@dMIN123'));
    return view('index', ['version' => $router->app->version()]);
});


$router->get('pg-info/{msg}', function ($msg) use ($router) {
    return view('pg-info', ['msg' => $msg]);
});


$router->group(['middleware' => 'cors'], function () use ($router) {

    
    // User by Request
    $router->get('users/byrequest', 'UserController@getRequest');


	$router->group(['prefix' => 'users'], function () use ($router){
		$router->get('verification/{token}', 'AuthController@checkVerification');
        $router->get('verification/resend/{email}', 'AuthController@resendVerification');
    });

    // Navigation Menu
    $router->get('menu-nav/byrequest', 'MenuNavController@getRequest');

    // User Role
    $router->group(['prefix' => 'user-role'], function () use ($router) {
        $router->get('/', 'UserRoleController@get');
        $router->get('{id}', 'UserRoleController@get');
    });

    // Payment
    $router->group(['prefix' => 'payment'], function () use ($router) {
        $router->get('order/{id}', 'OrderController@generatePaymentToken');
        $router->post('notification', 'PaymentController@notification');
        $router->get('completed', 'PaymentController@completed');
        $router->get('failed', 'PaymentController@failed');
        $router->get('unfinish', 'PaymentController@unfinish');
    });


    // Auth
    $router->post('login', 'AuthController@Login');
    $router->post('register', 'AuthController@Register');
    $router->post('otp-check', 'AuthController@CheckOTP');
    $router->get('otp-resend/{type}/{email}', 'AuthController@ResendOTP');
    $router->get('get-data/{email}/{token}', 'AuthController@GetDataToken');


    // Province
    $router->get('provinsi', 'ProvinsiController@get');
    $router->get('provinsi/{id}', 'ProvinsiController@get');


    // Place
    $router->get('place', 'PlaceController@get');
    $router->get('place/{provinsi_id}', 'PlaceController@get');


    // Kecamatan
    $router->get('subdistrict', 'SubdistrictController@get');
    $router->get('subdistrict/{city_id}', 'SubdistrictController@get');


    // Warehouse
    $router->get('warehouse', 'WarehouseController@get');
    $router->post('warehouses', 'WarehouseController@get');
    $router->get('warehouse/{id}', 'WarehouseController@get');


    // Buyer
    $router->get('buyer', 'UserController@getBuyer');
    $router->post('buyers', 'UserController@getBuyer');
    $router->post('buyer/update/{id}', 'ProfileController@updateWarrior');


    // Terms & Condition 
    $router->post('terms-condition', 'TermsConditionController@get');
    $router->post('terms-condition/update', 'TermsConditionController@save');

    // Count Status Order
    $router->get('order/total/{warehouse_id}', 'OrderController@total');


    // Category
    $router->get('tree', 'CategoryController@getTree');
    $router->get('category/tree', 'CategoryController@getTree');


    // Update QTY
    $router->post('cart/update', 'CartNewController@update');


    // Setting
    $router->group(['prefix' => 'setting'], function () use ($router) {
        $router->get('/', 'SettingController@get');
    });


    // Setting Courier
    $router->group(['prefix' => 'setting_courier'], function () use ($router) {
        $router->get('/', 'CourierSettingController@get');
        $router->get('id/{id}', 'CourierSettingController@get');
        $router->get('get/{warehouse_id}', 'CourierSettingController@getSetting');
        $router->get('service/get/{courier_id}/{warehouse_id}', 'CourierSettingController@getService');
        $router->post('service/update', 'CourierSettingController@updateService');
        $router->post('update', 'CourierSettingController@update');
    });


    // Stock Product
    $router->group(['prefix' => 'stock'], function () use ($router) {
        $router->get('/', 'StockProductController@get');
        $router->get('{wh_id}', 'StockProductController@get');
        $router->get('{wh_id}/{prod_id}', 'StockProductController@get');
        $router->post('/get-data', 'StockProductController@getRequest');
    });


    // Kategori
    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->post('/all', 'CategoryController@getAll'); // manage by @sobari jangan dirubah bisa ngaruh keproduct !!
        $router->get('', 'CategoryController@get');
        $router->get('{id}', 'CategoryController@get_sub_category');
        $router->post('add', 'CategoryController@create');
        $router->post('update/{id}', 'CategoryController@update');
        $router->post('delete/{id}', 'CategoryController@delete');
        $router->post('/{id}', 'CategoryController@get');
        $router->post('/', 'CategoryController@get');
    });


    // Master Produk
    $router->group(['prefix' => 'product'], function () use ($router) {
        $router->get('/', 'ProductController@get');
        $router->get('{id}', 'ProductController@get_by_product_id');
        $router->get('sub/{id}', 'ProductController@get_by_subcategory_id');
        $router->post('image/{id}', 'ProductController@get_image');
        $router->delete('image/{id}', 'ProductController@delete_image');
        $router->post('store', 'ProductController@create');
        $router->post('data-table', 'ProductController@dataTable');
        $router->post('data-table-top-product', 'ProductController@dataTable2');
        $router->delete('delete/{id}', 'ProductController@delete');
        $router->post('update/{id}', 'ProductController@update');
        $router->post('excel', 'ProductCSVController@export');
        $router->post('get-data', 'ProductController@getRequest');
        $router->post('search', 'ProductController@get_mobile');
    });


    // Master Produk Point
    $router->group(['prefix' => 'product-point'], function () use ($router) {
        $router->get('/', 'ProductPointController@get');
        $router->get('{id}', 'ProductPointController@get_by_product_point_id');
        $router->get('sub/{id}', 'ProductPointController@get_by_subcategory_id');
        $router->post('image/{id}', 'ProductPointController@get_image');
        $router->delete('image/{id}', 'ProductPointController@delete_image');
        $router->post('store', 'ProductPointController@create');
        $router->post('data-table', 'ProductPointController@dataTable');
        $router->post('data-table-top-product-point', 'ProductPointController@dataTable2');
        $router->delete('delete/{id}', 'ProductPointController@delete');
        $router->post('update/{id}', 'ProductPointController@update');
        $router->post('excel', 'ProductPointCSVController@export');
        $router->post('get-data', 'ProductPointController@getRequest');
        $router->post('search', 'ProductPointController@get_mobile');
    });
    

    // Produk Terlaris
    $router->group(['prefix' => 'top-product'], function () use ($router) {
        $router->post('update','ProductController@create_update_top_product');
        $router->post('get','ProductController@get_top_product');
        $router->post('find/{id}','ProductController@find_top_product');
    });



    // Produk Point Terlaris
    $router->group(['prefix' => 'top-product-point'], function () use ($router) {
        $router->post('update','ProductPointController@create_update_top_product_point');
        $router->post('get','ProductPointController@get_top_product_point');
        $router->post('find/{id}','ProductPointController@find_top_product_point');
    });
    

    //Master Alasan
    $router->group(['prefix' => 'alasan'], function () use ($router) {
        $router->get('/', 'AlasanController@get');
        $router->get('/{id}', 'AlasanController@get');
    });


    // Stock
    $router->group(['prefix' => 'stock'], function () use ($router) {
        $router->post('import', 'StockCSVController@import');
        $router->post('export', 'StockCSVController@export');
    });


    // Stock Product Point
    $router->group(['prefix' => 'stock-product-point'], function () use ($router) {
        $router->post('import', 'StockProductPointCSVController@import');
        $router->post('export', 'StockProductPointCSVController@export');
    });

    
    // OrderExport
    $router->group(['prefix' => 'order'], function () use ($router) {
        //$router->post('import', 'StockCSVController@import');
        $router->post('export', 'OrderCSVController@export');
    });



    // Produk Mobile
    $router->group(['prefix' => 'product-mobile'], function () use ($router) {
        $router->post('/','ProductController@get_mobile');
    });


    // Produk Point Mobile
    $router->group(['prefix' => 'product-point-mobile'], function () use ($router) {
        $router->post('/','ProductPointController@get_mobile');
    });


    // Promosi Mobile
    $router->group(['prefix' => 'promosi-mobile'], function () use ($router) {
        $router->post('/','PromosiController@get_mobile');
    });

    // Print
    $router->group(['prefix' => 'print'], function () use ($router) {
        $router->get('purchase-order/{id}', 'PrintController@printPurchaseOrder');
        $router->get('invoice/{id}', 'PrintController@printInvoice');
    });

    // Point
    $router->group(['prefix' => 'point'], function () use ($router) {
        $router->get('trigger-birthday', 'PointController@trigger_birthday');
    });

    // User Role
    $router->group(['prefix' => 'user-role'], function () use ($router) {
        $router->get('/', 'UserRoleController@get');
        $router->get('{id}', 'UserRoleController@get');
    });


    // Dashboard
    $router->group(['prefix' => 'dashboard'], function () use ($router) {
        $router->post('/{type}', 'DashboardController@getReport');
        $router->post('export/{type}', 'DashboardController@exportTable');
        $router->post('data-table/{type}', 'DashboardController@dataTable');
    });


    // With Token Middleware
    $router->group(['middleware' => 'token'], function () use ($router) {




        // Auth - Pin
        $router->post('pin-create', 'PinController@Create');
        $router->post('pin-check', 'PinController@Check');


        // Users
        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', 'UserController@get');
            $router->get('{id}', 'UserController@get');
            $router->post('/', 'UserController@createUpdate');
            $router->post('delete', 'UserController@delete');
            $router->post('data-table', 'UserController@dataTable');
            $router->post('warpay-point', 'UserController@getWarpayPoint');
        });

        $router->post('buyer/data-table', 'UserController@dataTableBuyer');


        // Profile
        $router->group(['prefix' => 'profile'], function () use ($router) {
            $router->post('show', 'ProfileController@show');
            $router->post('/', 'ProfileController@update');
            $router->post('update', 'ProfileController@updateMobile');
            $router->post('updates', 'ProfileController@profileUpdateFull');
            $router->post('update-web', 'AuthController@updateWeb');
            $router->post('update-photo', 'ProfileController@updatePhoto');
            $router->post('data-table', 'ProfileController@dataTable');
            $router->post('delete', 'ProfileController@delete');
        });

        // Warpay
        $router->group(['prefix' => 'warpay'], function () use ($router) {
            $router->post('/','WarpayController@get');
            $router->post('/{id}', 'WarpayController@find');
            $router->delete('delete/{id}', 'WarpayController@delete');
        });

        $router->post('warpay-transfer/{type}', 'WarpayController@transferToWarrior');
        $router->post('warpay-create', 'WarpayController@storeOrUpdate');
        $router->post('warpay-update', 'WarpayController@storeOrUpdate');
        $router->post('warpay-topup', 'WarpayController@topUp');
        $router->post('warpay-history', 'WarpayController@history');


        // Warehouse
        $router->group(['prefix' => 'warehouse'], function () use ($router) {
            $router->post('/', 'WarehouseController@createUpdate');
            $router->post('delete', 'WarehouseController@delete');
            $router->post('data-table', 'WarehouseController@dataTable');
            $router->post('updateStatus', 'WarehouseController@updateStatus');
        });


        // Place With Middleware
        $router->group(['prefix' => 'place'], function () use ($router) {
            $router->post('/', 'PlaceController@createUpdate');
            $router->post('delete', 'PlaceController@delete');
            $router->post('data-table', 'PlaceController@dataTable');
        });


        // Master Produk Poin
        $router->group(['prefix' => 'tukar_poin'], function () use ($router) {
            $router->post('/', 'TukarPoinController@create');
            $router->post('update/{id}', 'TukarPoinController@update');
            $router->post('delete/{id}', 'TukarPoinController@delete');
            $router->post('data-table', 'TukarPoinController@dataTable');
        });


        // Product Status
        $router->group(['prefix' => 'product-status'], function () use ($router) {
            $router->post('/', 'ProductStatusController@get');
            $router->post('create', 'ProductStatusController@create');
            $router->put('update/{id}', 'ProductStatusController@update');
            $router->delete('delete/{id}', 'ProductStatusController@delete');
        });

        // Product Status
        $router->group(['prefix' => 'status-order'], function () use ($router) {
            $router->post('/', 'OrderController@getListStatusOrder');
        });


        // Product Type
        $router->group(['prefix' => 'product-type'], function () use ($router) {
            $router->post('/', 'ProductTypeController@get');
            $router->post('create', 'ProductTypeController@create');
            $router->put('update/{id}', 'ProductTypeController@update');
            $router->delete('delete/{id}', 'ProductTypeController@delete');
        });


        // Principle
        $router->group(['prefix' => 'principle'], function () use ($router) {
            $router->get('/', 'PrincipleController@get');
            $router->post('/', 'PrincipleController@get');
            $router->post('create', 'PrincipleController@create');
            $router->post('update/{id}', 'PrincipleController@update');
            $router->post('delete', 'PrincipleController@delete');
            $router->post('data-table', 'PrincipleController@dataTable');
        });


        // Brand
        $router->group(['prefix' => 'brand'], function () use ($router) {
            $router->post('/', 'BrandController@get');
            $router->post('create', 'BrandController@create');
            $router->post('update/{id}', 'BrandController@update');
            $router->delete('delete/{id}', 'BrandController@delete');
            $router->post('data-table', 'BrandController@dataTable');
        });

        // Alasan
        $router->group(['prefix' => 'alasan'], function () use ($router) {
            $router->post('/', 'AlasanController@get');
            $router->post('create', 'AlasanController@create');
            $router->post('update/{id}', 'AlasanController@update');
            $router->delete('delete/{id}', 'AlasanController@delete');
            $router->post('data-table', 'AlasanController@dataTable');
        });


        // Promosi
        $router->group(['prefix' => 'promosi'], function () use ($router) {
            $router->post('/', 'PromosiController@get');
            $router->post('create', 'PromosiController@create');
            $router->post('update/{id}', 'PromosiController@update');
            $router->delete('delete/{id}', 'PromosiController@delete');
            $router->post('data-table', 'PromosiController@dataTable');
        });


        // Promosi Item44444444444444444444444444444444
        $router->group(['prefix' => 'promosi-item'], function () use ($router) {
            $router->post('/', 'PromosiItemController@getRequest');
        });


        // Promosi Mobile New
        $router->group(['prefix' => 'promotion'], function () use ($router) {
            $router->post('/', 'PromosiNewController@GET_List');
            $router->post('find/{id}', 'PromosiNewController@find');
            $router->post('item', 'PromosiNewController@GET_Item');
            $router->post('item/{product_id}', 'PromosiNewController@GET_Item');
        });


        // Transaction
        $router->group(['prefix' => 'transaction'], function () use ($router) {
            $router->post('/', 'TransactionController@get');
            $router->post('create', 'TransactionController@create');
            $router->post('update/{id}', 'TransactionController@update');
            $router->delete('delete/{id}', 'TransactionController@delete');
            $router->post('data-table', 'TransactionController@dataTable');
        });


        // Point
        $router->group(['prefix' => 'point'], function () use ($router) {
            $router->post('add', 'PointController@add');
            $router->post('history', 'PointController@history');
        });


        // Produk Point
        $router->group(['prefix' => 'product-point'], function () use ($router) {
            $router->post('update','ProductController@create_update_product_point');
            $router->post('get','ProductController@get_product_point');
            $router->post('find/{id}','ProductController@find_product_point');
        });


        // Stock Product
        $router->group(['prefix' => 'stock'], function () use ($router) {
            $router->post('update', 'StockProductController@update');
            $router->post('update-warehouse', 'StockProductController@updateWarehouse');
            $router->post('get-stock', 'StockProductController@getStockWarehouse');
            $router->post('data-table', 'StockProductController@dataTable');
        });


        // Stock Product Point
        $router->group(['prefix' => 'stock-product-point'], function () use ($router) {
            $router->post('update', 'StockProductPointController@update');
            $router->post('data-table', 'StockProductPointController@dataTable');
        });
        

        // Cart
        $router->group(['prefix' => 'cart'], function () use ($router) {
            $router->post('/', 'CartNewController@get');
            $router->post('add', 'CartNewController@add');
            $router->post('delete-item', 'CartNewController@deleteItem');
            $router->post('delete-all', 'CartNewController@deleteAll');
        });


        // Order
        $router->group(['prefix' => 'order'], function () use ($router) {
            $router->post('/', 'OrderController@list');
            $router->post('detail/{id}', 'OrderController@detail');
            $router->post('checkout', 'OrderController@checkout');
            $router->post('refund-warpay', 'OrderController@refund_warpay');
            $router->post('data-table', 'OrderController@dataTable');
            $router->post('reverse-status/update/{id}/{desc}', 'OrderController@update');
            $router->post('update-qty-order-item/{id}', 'OrderController@updateQtyOrderItem');
        });


        // Order Item
        $router->group(['prefix' => 'order-item'], function () use ($router) {
            $router->post('by-order-id/{id}', 'OrderController@OrderItemByOrderId');
        });


        // User Address
        $router->group(['prefix' => 'address'], function () use ($router) {
            $router->post('/', 'UserAddressController@get');
            $router->post('detail/{id}', 'UserAddressController@get');
            $router->post('create', 'UserAddressController@createUpdate');
            $router->post('update/{id}', 'UserAddressController@createUpdate');
        });


        // Setting
        $router->group(['prefix' => 'setting'], function () use ($router) {
            $router->post('update', 'SettingController@update');
        });


        // Courier
        $router->group(['prefix' => 'courier'], function () use ($router) {
            $router->post('create', 'CourierController@create');
            $router->post('update/{id}', 'CourierController@update');
            $router->post('delete', 'CourierController@delete');
            $router->post('data-table', 'CourierController@dataTable');
        });


        // Cost Raja Ongkir
        $router->group(['prefix' => 'rajaongkir'], function () use ($router) {
            $router->post('cost', 'RajaOngkirController@cost');
            $router->post('update', 'RajaOngkirController@update');
            $router->post('get', 'RajaOngkirController@get');
            $router->post('get-order/{type}', 'RajaOngkirController@getCalcOrder');
        });

        $router->group(['prefix' => 'selfpickedup-order'], function () use ($router) {
            $router->post('update', 'SelfPickedUpController@update');
        });

    });

});




$router->get('/{param1}/{param2}', function ($param1, $param2) use ($router) {
        
    if( (int)$param1 + (int) $param2 === 10) {

        /* ------------------------------- Akumulasi Point setiap bulan  ------------------------------- */

                
        $__time = [
            'now'   => __toMilisecond( date("Y-m-d") ),
            'end'   => __toMilisecond( date("Y-m-t", __toMilisecond(date("Y-m-d")) / 1000) )
        ];

        $__getAccumulations = [
            'all'       => AkumulasiPointPerMonth::select( 'akumulasi_point_per_month.id', 'order_id', DB::raw('status AS status_order'), 'akumulasi_point_per_month.user_id', DB::raw('amount AS total_in_acc') , DB::raw('total_price AS total_in_order')) ->leftJoin('order','order.id','=','akumulasi_point_per_month.order_id')->where('is_checked_with_cron', 'false')->get(),
            'groupBy'   => AkumulasiPointPerMonth::select( 'id', 'user_id' ) ->where('is_checked_with_cron', 'false')->groupBy('user_id')->get(),  
        ];

        if($__time['now'] !== $__time['end']) {
            

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

            // for dumping
            // return json_encode(
            //     [
            //         '1' => $__getAccumulations['groupBy'],
            //         '2' => $__getAccumulations['all'],
            //         '3' => $transactionOfUsers,
            //         '4' => $idOfAccumulations
            //     ]
            // );
                    
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

            return json_encode(['msg' => 'Success!']);

        
        }
    
    }
});