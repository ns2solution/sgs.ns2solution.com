<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Exception;
use Carbon\Carbon;

use App\Setting;
use App\Order;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\OrderItem;

class DashboardController extends Controller
{

    const   DAY     = 'day', 
            MONTH   = 'month',
            YEAR    = 'year',
            WARRIOR = 'warrior',
            WARPAY = 'warpay',
            MUTASI_WP = 'mutasi-wp',
            SALDO_WP = 'saldo-wp',
            BRAND = 'brand',
            TRANS_PER_WH = 'transaction-per-warehouse',
            PLUS = '+',
            MIN = '-';

    private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
    }

    public function dataTable($type, Request $request)
    {
        if($type == self::DAY) {


            /* -------------------------------------------------------------------------- */
            /*                                    DAY                                     */
            /* -------------------------------------------------------------------------- */
            

            try {

                $columns = [null, 'order.created_at','A.id', 'fullname', 'B.sort', 'nominal_transaksi', 'total_transaksi', 'total_ongkir', 'D.name', 'sum_final_total'];

                $query = DB::table('order')
                ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                ->leftjoin('shipment_types  AS C', 'C.order_id', '=', 'order.id')
                ->leftjoin('couriers        AS D', 'D.id',       '=', 'C.courier_id')
                ->select(
                    'A.id',
                    'order.id AS order_id',
                    'A.fullname',
                    DB::raw('CONCAT(D.name, " (", C.courier_service, ")") AS courier_name'),
                    DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                    'order.status AS status_order',
                    DB::raw('FORMAT(order.total_price, 0) AS nominal_transaksi'),
                    DB::raw('FORMAT(order.final_total, 0) AS total_transaksi'),
                    DB::raw('FORMAT(order.total_ongkir, 0) AS total_ongkir'),
                    DB::raw('DATE_FORMAT(order.created_at, "%d-%m-%Y") AS created_at'),
                    DB::raw('DAY(order.created_at) AS day'),
                    DB::raw('MONTH(order.created_at) AS month'),
                    DB::raw('YEAR(order.created_at) AS year'),
                    DB::raw('FORMAT(SUM(order.final_total), 0) AS sum_final_total')
                );
                // ->where(function ($where) {
                //     $where->whereNotNull('order.user_id')
                //             ->whereNotNull('A.id')
                //             ->whereNotIn('order.status', [1, 2, 8]);          
                //         })
                // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'));

                /* --------------------------------- Request -------------------------------- */

                $START_DATE              = $request->start_date;
                $END_DATE                = $request->end_date;

                $limit              = $request->input('length');
                $start              = $request->input('start');
                $order              = $columns[$request->input('order.0.column')];
                $dir                = $request->input('order.0.dir');

                $data               = array();
                $totalData          = 0;


                $CASE_FILTER = [
                    0 => (empty($START_DATE)) || (empty($END_DATE)),
                    1 => (!empty($START_DATE)) && (!empty($END_DATE)),   
                ];


                /* ------------------------------ Count Data ----------------------------- */

                if($CASE_FILTER[0]) {

                    $_ = $query->where(function ($where) {
                        $where->whereNotNull('order.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('order.status', [1, 2, 8]);
                    })
                    // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))->get();
                    ->groupBy('order.id')
                    ->get();

                    $totalData = count( $query->get() );
                
                } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $_ = $query->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] );          
                        })
                        // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                        ->groupBy('order.id')
                        ->get();

                    $totalData  = count( $_ );
               
                }

                $totalFiltered = $totalData;
                $_populate	       = '';


                /* ------------------------------- Search Data ------------------------------ */


                if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {
                        
                        $_table = $query->where(function ($where) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8]);
                        })
                        // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                        ->groupBy('order.id')
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();
                   
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';

                        $_table = $query->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] );     
                        })
                        // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                        ->groupBy('order.id')
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    }
                        
                    $_populate = $_table;

                } else {

                /* ------------------------------- Where Like ------------------------------- */


                    $search = $request->input('search.value');

                    if($CASE_FILTER[0]) {

                        $_table = $query
                        ->where(function ($where) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('order.status', 'LIKE', "%{$search}%")
                            ->orWhere('D.name', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(D.name, " (", C.courier_service, ")")'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(order.total_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(order.final_total, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(order.total_ongkir, 0)'), 'LIKE', "%{$search}%")
                            // ->orWhere(DB::raw('FORMAT(SUM(order.final_total), 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('order.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('order.final_total', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_ongkir', 'LIKE', "%{$search}%")
                            ->orWhere('order.created_at', 'LIKE', "%{$search}%")
                            // ->orWhere(DB::raw('FORMAT(SUM(order.final_total),0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                        })
                        // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'));
                        ->groupBy('order.id');

                        
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';

                        $_table = $query->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] );
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('order.status', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(D.name, " (", C.courier_service, ")")'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(order.total_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(order.final_total, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(order.total_ongkir, 0)'), 'LIKE', "%{$search}%")
                            // ->orWhere(DB::raw('FORMAT(SUM(order.final_total), 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('order.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('order.final_total', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_ongkir', 'LIKE', "%{$search}%")
                            ->orWhere('order.created_at', 'LIKE', "%{$search}%")
                            // ->orWhere(DB::raw('SUM(order.final_total)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                        })
                        // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'));
                        ->groupBy('order.id');

                    }

                    $totalFiltered = count($_table->get());
                    $_populate = $_table->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
                        
                }



                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {

                        $d['no']                                = $no++;
                        $d['id_warrior']                        = $a->id;
                        $d['id_order']                          = $a->order_id;
                        $d['fullname']                          = $a->fullname;
                        $d['wh_name']                           = $a->wh_name;
                        $d['status_order']                      = $a->status_order;
                        $d['nominal_transaksi']                 = $a->nominal_transaksi;
                        $d['total_transaksi']                   = $a->total_transaksi;
                        $d['total_ongkir']                      = $a->total_ongkir;
                        $d['created_at']                        = $a->created_at;
                        $d['created_at_ori']                    = $a->created_at;
                        $d['grand_total_per_user_per_wh']       = $a->sum_final_total;
                        $d['courier_name']                      = $a->courier_name ? $a->courier_name : '-';
                        

                        $row++;
                        $data[] = $d;

                    }

                    usort($data, function($a, $b) {
                        return strtotime($a['created_at_ori']) < strtotime($b['created_at_ori']);
                    });

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
            }

        } else if($type == self::MONTH) {


            /* -------------------------------------------------------------------------- */
            /*                                    MONTH                                   */
            /* -------------------------------------------------------------------------- */


            try {

                $columns = [null, 'order.created_at','A.id', 'fullname', 'B.sort', 'nominal_transaksi', 'total_transaksi', 'total_ongkir',  'sum_final_total'];

                $query = DB::table('order')
                ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                ->select(
                    'A.id',
                    'order.id AS order_id',
                    'A.fullname',
                    DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                    'order.status AS status_order',
                    'order.total_price AS nominal_transaksi',
                    'order.final_total AS total_transaksi',
                    'order.total_ongkir AS total_ongkir',
                    'order.created_at',
                    DB::raw('DAY(order.created_at) AS day'),
                    DB::raw('MONTH(order.created_at) AS month'),
                    DB::raw('YEAR(order.created_at) AS year'),
                    DB::raw('SUM(order.final_total) AS sum_final_total')
                )
                ->where(function ($where) {
                    $where->whereNotNull('order.user_id')
                            ->whereNotNull('A.id')
                            ->whereNotIn('order.status', [1, 2, 8]);          
                        })
                ->groupBy('B.short', DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'));

                /* --------------------------------- Request -------------------------------- */

                $START_DATE              = $request->start_date;
                $END_DATE                = $request->end_date;

                $limit              = $request->input('length');
                $start              = $request->input('start');
                $order              = $columns[$request->input('order.0.column')];
                $dir                = $request->input('order.0.dir');

                $data               = array();
                $totalData          = 0;


                 /* ------------------------------- Case Filter ------------------------------ */
                // 0. jika $start_date || $end_date kosong
                // 1. jika $start_date && $end_date ada


                $CASE_FILTER = [
                    0 => (empty($START_DATE)) || (empty($END_DATE)),
                    1 => (!empty($START_DATE)) && (!empty($END_DATE)),   
                ];


                /* ------------------------------ Count Data ----------------------------- */

                if($CASE_FILTER[0]) {
               
                    _log('case filter 0');

                    $totalData = count( $query->get() );
                
                } else if($CASE_FILTER[1]) {
                    
                    _log('case filter 1');
                   
                    $_          = $query->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] )->get();
                    $totalData  = count( $_ );
               
                }

                $totalFiltered = $totalData;
                $_populate	       = '';


                if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {
                        
                        $_table = $query
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();
                   
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = Carbon::parse($START_DATE.'-01')->format('Y-m-d H:i:s');
                        $END_DATE = Carbon::parse($END_DATE.'-01')->format('Y-m-d H:i:s');

                        $_table = DB::table('order')
                        ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                        ->select(
                            'A.id',
                            'order.id AS order_id',
                            'A.fullname',
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            'order.status AS status_order',
                            'order.total_price AS nominal_transaksi',
                            'order.final_total AS total_transaksi',
                            'order.total_ongkir AS total_ongkir',
                            'order.created_at',
                            DB::raw('DAY(order.created_at) AS day'),
                            DB::raw('MONTH(order.created_at) AS month'),
                            DB::raw('YEAR(order.created_at) AS year'),
                            DB::raw('SUM(order.final_total) AS sum_final_total')
                        )
                        ->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] );
                        })
                        ->groupBy('B.short', DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    }


                } else {

                /* ------------------------------- Where Like ------------------------------- */


                    $search = $request->input('search.value');

                    if($CASE_FILTER[0]) {

                        $_table = DB::table('order')
                        ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                        ->select(
                            'A.id',
                            'order.id AS order_id',
                            'A.fullname',
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            'order.status AS status_order',
                            'order.total_price AS nominal_transaksi',
                            'order.final_total AS total_transaksi',
                            'order.total_ongkir AS total_ongkir',
                            'order.created_at',
                            DB::raw('DAY(order.created_at) AS day'),
                            DB::raw('MONTH(order.created_at) AS month'),
                            DB::raw('YEAR(order.created_at) AS year'),
                            DB::raw('SUM(order.final_total) AS sum_final_total')
                        )
                        ->where(function ($where) use ($search)  {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('order.status', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('order.final_total', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_ongkir', 'LIKE', "%{$search}%")
                            ->orWhere('order.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                        })
                        ->groupBy('B.short', DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    } else if($CASE_FILTER[1]) {


                        $START_DATE = Carbon::parse($START_DATE.'-01')->format('Y-m-d H:i:s');
                        $END_DATE = Carbon::parse($END_DATE.'-01')->format('Y-m-d H:i:s');
                        
                        $_table = DB::table('order')
                        ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                        ->select(
                            'A.id',
                            'order.id AS order_id',
                            'A.fullname',
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            'order.status AS status_order',
                            'order.total_price AS nominal_transaksi',
                            'order.final_total AS total_transaksi',
                            'order.total_ongkir AS total_ongkir',
                            'order.created_at',
                            DB::raw('DAY(order.created_at) AS day'),
                            DB::raw('MONTH(order.created_at) AS month'),
                            DB::raw('YEAR(order.created_at) AS year'),
                            DB::raw('SUM(order.final_total) AS sum_final_total')
                        )
                        ->where(function ($where) use ($search)  {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('order.status', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('order.final_total', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_ongkir', 'LIKE', "%{$search}%")
                            ->orWhere('order.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                        })
                        ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] )
                        ->groupBy('B.short', DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    }        

                }

                $_populate = $_table;


                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {

                        $d['no']                                = $no++;
                        $d['id_warrior']                        = $a->id;
                        $d['id_order']                          = $a->order_id;
                        $d['fullname']                          = $a->fullname;
                        $d['wh_name']                           = $a->wh_name;
                        $d['status_order']                      = $a->status_order;
                        $d['nominal_transaksi']                 = $a->nominal_transaksi;
                        $d['total_transaksi']                   = $a->total_transaksi;
                        $d['total_ongkir']                      = $a->total_transaksi;
                        $d['created_at']                        = $a->month.'-'.$a->year;
                        $d['created_at_ori']                    = $a->created_at;
                        $d['grand_total_per_user_per_wh']       = $a->sum_final_total;

                        $row++;
                        $data[] = $d;

                    }

                    usort($data, function($a, $b) {
                        return strtotime($a['created_at_ori']) < strtotime($b['created_at_ori']);
                    });

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
            }

        } else if($type == self::YEAR) {


            /* -------------------------------------------------------------------------- */
            /*                                    YEAR                                    */
            /* -------------------------------------------------------------------------- */


            try {

                $columns = [null, 'order.created_at','A.id', 'fullname', 'B.sort', 'nominal_transaksi', 'total_transaksi', 'total_ongkir',  'sum_final_total'];

                $query = DB::table('order')
                ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                ->select(
                    'A.id',
                    'order.id AS order_id',
                    'A.fullname',
                    DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                    'order.status AS status_order',
                    'order.total_price AS nominal_transaksi',
                    'order.final_total AS total_transaksi',
                    'order.total_ongkir AS total_ongkir',
                    'order.created_at',
                    DB::raw('DAY(order.created_at) AS day'),
                    DB::raw('MONTH(order.created_at) AS month'),
                    DB::raw('YEAR(order.created_at) AS year'),
                    DB::raw('SUM(order.final_total) AS sum_final_total')
                )
                ->where(function ($where) {
                    $where->whereNotNull('order.user_id')
                            ->whereNotNull('A.id')
                            ->whereNotIn('order.status', [1, 2, 8]);          
                        })
                ->groupBy('B.short', DB::raw('YEAR(order.created_at)'));

                /* --------------------------------- Request -------------------------------- */

                $START_DATE              = $request->start_date;
                $END_DATE                = $request->end_date;

                $limit              = $request->input('length');
                $start              = $request->input('start');
                $order              = $columns[$request->input('order.0.column')];
                $dir                = $request->input('order.0.dir');

                $data               = array();
                $totalData          = 0;


                 /* ------------------------------- Case Filter ------------------------------ */
                // 0. jika $start_date || $end_date kosong
                // 1. jika $start_date && $end_date ada


                $CASE_FILTER = [
                    0 => (empty($START_DATE)) || (empty($END_DATE)),
                    1 => (!empty($START_DATE)) && (!empty($END_DATE)),   
                ];


                /* ------------------------------ Count Data ----------------------------- */

                if($CASE_FILTER[0]) {
               
                    _log('case filter 0');

                    $totalData = count( $query->get() );
                
                } else if($CASE_FILTER[1]) {
                    
                    _log('case filter 1');
                   
                    $_          = $query->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] )->get();
                    $totalData  = count( $_ );
               
                }

                $totalFiltered = $totalData;
                $_populate	       = '';


                if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {

                        $_table = $query
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    } else if($CASE_FILTER[1]) {

                        $START_DATE = Carbon::parse($START_DATE.'-01-01')->format('Y-m-d H:i:s');
                        $END_DATE = Carbon::parse($END_DATE.'-01-01')->format('Y-m-d H:i:s');
                        

                        $_table = DB::table('order')
                        ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                        ->select(
                            'A.id',
                            'order.id AS order_id',
                            'A.fullname',
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            'order.status AS status_order',
                            'order.total_price AS nominal_transaksi',
                            'order.final_total AS total_transaksi',
                            'order.total_ongkir AS total_ongkir',
                            'order.created_at',
                            DB::raw('DAY(order.created_at) AS day'),
                            DB::raw('MONTH(order.created_at) AS month'),
                            DB::raw('YEAR(order.created_at) AS year'),
                            DB::raw('SUM(order.final_total) AS sum_final_total')
                        )
                        ->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] );
                        })
                        ->groupBy('B.short', DB::raw('YEAR(order.created_at)'))
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    }
                        
                } else {

                /* ------------------------------- Where Like ------------------------------- */


                    $search = $request->input('search.value');

                    if($CASE_FILTER[0]) {
                        
                        $_table = DB::table('order')
                        ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                        ->select(
                            'A.id',
                            'order.id AS order_id',
                            'A.fullname',
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            'order.status AS status_order',
                            'order.total_price AS nominal_transaksi',
                            'order.final_total AS total_transaksi',
                            'order.total_ongkir AS total_ongkir',
                            'order.created_at',
                            DB::raw('DAY(order.created_at) AS day'),
                            DB::raw('MONTH(order.created_at) AS month'),
                            DB::raw('YEAR(order.created_at) AS year'),
                            DB::raw('SUM(order.final_total) AS sum_final_total')
                        )
                        ->where(function ($where) use ($search)  {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('order.status', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('order.final_total', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_ongkir', 'LIKE', "%{$search}%")
                            ->orWhere('order.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                        })
                        ->groupBy('B.short',  DB::raw('YEAR(order.created_at)'))
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();          

                    } else if($CASE_FILTER[1]) {

                        $START_DATE = Carbon::parse($START_DATE.'-01-01')->format('Y-m-d H:i:s');
                        $END_DATE = Carbon::parse($END_DATE.'-01-01')->format('Y-m-d H:i:s');
                        

                        $_table = DB::table('order')
                        ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                        ->select(
                            'A.id',
                            'order.id AS order_id',
                            'A.fullname',
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            'order.status AS status_order',
                            'order.total_price AS nominal_transaksi',
                            'order.final_total AS total_transaksi',
                            'order.total_ongkir AS total_ongkir',
                            'order.created_at',
                            DB::raw('DAY(order.created_at) AS day'),
                            DB::raw('MONTH(order.created_at) AS month'),
                            DB::raw('YEAR(order.created_at) AS year'),
                            DB::raw('SUM(order.final_total) AS sum_final_total')
                        )
                        ->where(function ($where) use ($search)  {
                            $where->whereNotNull('order.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('order.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('order.status', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('order.final_total', 'LIKE', "%{$search}%")
                            ->orWhere('order.total_ongkir', 'LIKE', "%{$search}%")
                            ->orWhere('order.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                        })
                        ->whereBetween( DB::raw('order.created_at') , [ $START_DATE, $END_DATE ] )
                        ->groupBy('B.short',  DB::raw('YEAR(order.created_at)'))
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();          
                        
                    }

                }

                $_populate = $_table;


                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {

                        $d['no']                                = $no++;
                        $d['id_warrior']                        = $a->id;
                        $d['id_order']                          = $a->order_id;
                        $d['fullname']                          = $a->fullname;
                        $d['wh_name']                           = $a->wh_name;
                        $d['status_order']                      = $a->status_order;
                        $d['nominal_transaksi']                 = $a->nominal_transaksi;
                        $d['total_transaksi']                   = $a->total_transaksi;
                        $d['total_ongkir']                      = $a->total_transaksi;
                        $d['created_at']                        = $a->year;
                        $d['created_at_ori']                    = $a->created_at;
                        $d['grand_total_per_user_per_wh']       = $a->sum_final_total;

                        $row++;
                        $data[] = $d;

                    }

                    usort($data, function($a, $b) {
                        return strtotime($a['created_at_ori']) < strtotime($b['created_at_ori']);
                    });

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        } else if($type == self::WARRIOR) {

            /* -------------------------------------------------------------------------- */
            /*                                    WARRIOR                                 */
            /* -------------------------------------------------------------------------- */
            
            try {

                $columns    = [null, 'Z.created_at','A.id', 'C.sort', 'E.code','D.prod_name', 'Z.total_item', 'D.prod_modal_price', 'Z.price', 'Z.total_price',  'A.user_id', 'fullname'];


                $query = DB::table('order_item AS Z')
                    ->leftJoin('order AS A',   'A.id', '=', 'Z.order_id')
                    ->leftJoin('users AS B',   'B.id', '=', 'A.user_id')
                    ->leftJoin('warehouse AS C',   'C.id', '=', 'A.warehouse_id')
                    ->leftJoin('product AS D',   'D.id', '=', 'Z.product_id')
                    ->leftJoin('principles AS E', 'D.principle_id', '=', 'E.id')
                    ->select(
                        'Z.id',
                        'A.user_id AS id_warrior',
                        'A.id AS order_id',
                        DB::raw("
                            IFNULL(B.fullname, '-') AS fullname
                        "),
                        DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                        'A.status AS status_order',
                        'D.prod_number',
                        'D.prod_name',
                        DB::raw('FORMAT(D.prod_modal_price, 0) AS prod_modal_price'),
                        'Z.total_item AS quantity',
                        DB::raw('FORMAT(Z.price, 0) AS price'),
                        DB::raw('FORMAT(Z.total_price, 0) AS total_price'),
                        DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                        DB::raw('DAY(Z.created_at) AS day'),
                        DB::raw('MONTH(Z.created_at) AS month'),
                        DB::raw('YEAR(Z.created_at) AS year'),
                        'E.code AS principle_kode'
                    );

                  /* --------------------------------- Request -------------------------------- */

                  $START_DATE              = $request->start_date;
                  $END_DATE                = $request->end_date;
  
                  $limit                   = $request->input('length');
                  $start                   = $request->input('start');
                  $order                   = $columns[$request->input('order.0.column')];
                  $dir                     = $request->input('order.0.dir');
  
                  $data                    = array();
                  $totalData               = 0;
  

  
                  $CASE_FILTER = [
                      0 => (empty($START_DATE)) || (empty($END_DATE)),
                      1 => (!empty($START_DATE)) && (!empty($END_DATE)),   
                  ];
  
  
                  /* ------------------------------ Count Data ----------------------------- */
  
                  if($CASE_FILTER[0]) {
                 
                    $_ = $query->where(function ($where) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8]);
                    })->get();
                            
                    $totalData  = count( $_ );


                  } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $_ = $query->where(function ($where) use ($START_DATE, $END_DATE) {
                                $where->whereNotNull('A.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('A.status', [1, 2, 8])
                                        ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );        
                    })->get();
                    $totalData  = count( $_ );
                 
                  }
  
                  $totalFiltered = $totalData;
                  $_populate	       = '';
  


                /* ------------------------------- Search Data ------------------------------ */


                  if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {
                        
                        $_table = $query
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();
                   
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';


                        $_table = $query
                        ->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );        
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    }

                    $_populate =  $_table;

                  } else {

                      /* ------------------------------- Where Like ------------------------------- */


                      $search = $request->input('search.value');

                      if($CASE_FILTER[0]) {
                        
                        $_table = $query
                        ->where(function ($where) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('A.id', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_number', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_name', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(D.prod_modal_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_item', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.total_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('Z.price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(C.short, " - ", C.name)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(D.prod_number, " (", D.prod_name)', ")"), 'LIKE', "%{$search}%");
                        });
                   
                    } else if($CASE_FILTER[1]) {
                        
                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';

                        $_table = $query
                        ->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );        
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('A.id', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_number', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_name', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(D.prod_modal_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_item', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.total_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('Z.price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(C.short, " - ", C.name)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(D.prod_number, " (", D.prod_name)', ")"), 'LIKE', "%{$search}%");
                        });

                    }


                    $totalFiltered = count($_table->get());
                    $_populate = $_table->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                }

                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {
                        
                        $d['no']                                = $no++;
                        $d['id_order_item']                     = $a->id;
                        $d['id_order']                          = $a->order_id;
                        $d['id_warrior']                        = $a->id_warrior;
                        $d['fullname']                          = $a->fullname;
                        $d['wh_name']                           = $a->wh_name;
                        $d['status_order']                      = $a->status_order;
                        $d['prod_number']                       = $a->prod_number;
                        $d['prod_name']                         = $a->prod_name;
                        $d['prod_modal_price']                  = $a->prod_modal_price;
                        $d['principle_kode']                    = $a->principle_kode;
                        $d['quantity']                          = $a->quantity;
                        $d['price']                             = $a->price;
                        $d['total_price']                       = $a->total_price;
                        $d['created_at']                        = $a->created_at;

                        $row++;
                        $data[] = $d;

                    }

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        } else if($type == self::MUTASI_WP) {

            /* -------------------------------------------------------------------------- */
            /*                             MUTASI WARPAY                                  */
            /* -------------------------------------------------------------------------- */
            
            try {


                $columns    = [null, 'Z.created_at','Z.user_id', 'fullname', 'Z.warpay_prev', 'Z.total', 'Z.total' ,'E.short','Z.id', 'F.id'];

                $query = DB::table('history_in_out_wp AS Z')
                        ->select(
                            'Z.id',
                            'Z.user_id',
                            'B.fullname',
                            'Z.total',
                            'Z.type',
                            'F.fullname AS assign_by',
                            DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                            DB::raw('FORMAT(Z.warpay_prev, 0) AS warpay_prev'),
                            DB::raw('FORMAT(C.warpay, 0) AS warpay'),
                            DB::raw('CONCAT(E.short, " - ", E.name) AS wh_name'),
                        )
                        ->leftJoin('users            AS B', 'Z.user_id', '=', 'B.id')
                        ->leftJoin('user_profile     AS C', 'B.id', '=', 'C.user_id')
                        ->leftJoin('rajaongkir_city  AS D', 'C.place_id', '=', 'D.city_id')
                        ->leftJoin('warehouse        AS E', 'D.warehouse_id', '=', 'E.id')
                        ->leftJoin('users            AS F', 'Z.by', '=', 'F.id');


                  /* --------------------------------- Request -------------------------------- */

                  $START_DATE              = $request->start_date;
                  $END_DATE                = $request->end_date;
                  $ID_WARRIOR              = $request->id_warrior;
  

                  $limit                   = $request->input('length');
                  $start                   = $request->input('start');


                  $order                   = $columns[$request->input('order.0.column')];
                  $dir                     = $request->input('order.0.dir');
  
                  $data                    = array();
                  $totalData               = 0;
  

  
                  $CASE_FILTER = [
                      0 => ((empty($START_DATE)) || (empty($END_DATE))) && (empty($ID_WARRIOR)),
                      1 => (!empty($START_DATE)) && (!empty($END_DATE)) && (empty($ID_WARRIOR)),   
                      2 => ((empty($START_DATE)) || (empty($END_DATE))) && (!empty($ID_WARRIOR)),
                      3 => (!empty($START_DATE)) && (!empty($END_DATE)) && (!empty($ID_WARRIOR)),  
                    ];
  
  
                  /* ------------------------------ Count Data ----------------------------- */
  
                  if($CASE_FILTER[0]) {
                 
                    $_  = count( $query->get() );

                    $totalData  = ( $_ );


                  } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $_  =  count( $query->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )->get() );
                   
                    $totalData  = ( $_ );
                 
                  } else if($CASE_FILTER[2]) {

                    $_  =  count( $query->where('Z.user_id', '=', $ID_WARRIOR)->get() );
                   
                    $totalData  = ( $_ );

                  } else if($CASE_FILTER[3]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';
   
                    $_  =  count( $query
                                    ->where('Z.user_id', '=', $ID_WARRIOR)
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )->get() 
                            );

                    $totalData  = ( $_ );
                
                  }
  
                  $totalFiltered = $totalData;
                  $_populate	       = '';
  



                /* ------------------------------- Search Data ------------------------------ */


                  if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {
                        
                        $_table =   $query
                                    ->offset($start)
                                    ->limit($limit)
                                    ->orderBy($order, $dir)
                                    ->get();
            
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';
  
                        $_table = $query
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )       
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

                    } else if($CASE_FILTER[2]) {
  
                        $_table = $query
                                ->where('Z.user_id', '=', $ID_WARRIOR)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();


                    } else if($CASE_FILTER[3]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';
  
                        $_table = $query
                                ->where('Z.user_id', '=', $ID_WARRIOR)
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )       
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();
                    } 

                    $_populate = $_table;

                  } else {

                      /* ------------------------------- Where Like ------------------------------- */


                      $search = $request->input('search.value');

                      if($CASE_FILTER[0]) {
                        
                        $_table = $query
                                ->where(function ($where) use ($search)  {
                                    $where->where('Z.id', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.user_id', 'LIKE', "%{$search}%")
                                    ->orWhere('fullname', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.warpay_prev', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                                    ->orWhere('total', 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('CONCAT(E.short, " - ", E.name)'), 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('FORMAT(Z.warpay_prev, 0)'), 'LIKE', "%{$search}%");
                                    
                                });

                   
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';
                        
                        $_table = $query
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )     
                                ->where(function ($where) use ($search)  {
                                    $where->where('Z.id', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.user_id', 'LIKE', "%{$search}%")
                                    ->orWhere('fullname', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.warpay_prev', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                                    ->orWhere('total', 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('CONCAT(E.short, " - ", E.name)'), 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('FORMAT(Z.warpay_prev, 0)'), 'LIKE', "%{$search}%");
                                });

                    } else if($CASE_FILTER[2]) {
                        
                        $_table = $query
                                ->where('Z.user_id', '=', $ID_WARRIOR)
                                ->where(function ($where) use ($search)  {
                                    $where->where('Z.id', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.user_id', 'LIKE', "%{$search}%")
                                    ->orWhere('fullname', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.warpay_prev', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                                    ->orWhere('total', 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('CONCAT(E.short, " - ", E.name)'), 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('FORMAT(Z.warpay_prev, 0)'), 'LIKE', "%{$search}%");
                                });
                   

                    } else if($CASE_FILTER[3]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';
                
                        $_table = $query
                                ->where('Z.user_id', '=', $ID_WARRIOR)
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )     
                                ->where(function ($where) use ($search)  {
                                    $where->where('Z.id', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.user_id', 'LIKE', "%{$search}%")
                                    ->orWhere('fullname', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.warpay_prev', 'LIKE', "%{$search}%")
                                    ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                                    ->orWhere('total', 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('CONCAT(E.short, " - ", E.name)'), 'LIKE', "%{$search}%")
                                    ->orWhere(DB::raw('FORMAT(Z.warpay_prev, 0)'), 'LIKE', "%{$search}%");
                                });

                    }

                    $totalFiltered = count($_table->get());
                    $_populate = $_table->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
                }




                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {
                        
                        $d['no']                                = $no++;
                        $d['id_transaksi']                      = $a->id;
                        $d['id_warrior']                        = $a->user_id;
                        $d['fullname']                          = $a->fullname;
                        $d['assign_by']                         = $a->assign_by ? $a->assign_by : '-';
                        $d['wh_name']                           = $a->wh_name;
                        $d['warpay_in']                         = ($a->type == SELF::PLUS && $a->total ) ? $a->total : '-';
                        $d['warpay_out']                        = ($a->type == SELF::MIN && $a->total ) ? $a->total : '-';;
                        $d['warpay_user']                       = $a->warpay_prev;
                        $d['warpay_total']                       = $a->warpay;
                        $d['created_at']                        = $a->created_at;


                        $row++;
                        $data[] = $d;

                    }

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
                
            }
        } else if($type == self::BRAND) {

            /* -------------------------------------------------------------------------- */
            /*                                    BRAND                                   */
            /* -------------------------------------------------------------------------- */
            
            try {

                $columns    = [null, 'Z.created_at', 'E.brand_name', 'F.code', 'D.prod_name', 'Z.total_item'];

                $query = DB::table('order_item AS Z')
                    ->join('order AS A',   'A.id', '=', 'Z.order_id')
                    ->join('users AS B',   'B.id', '=', 'A.user_id')
                    ->join('warehouse AS C',   'C.id', '=', 'A.warehouse_id')
                    ->join('product AS D',   'D.id', '=', 'Z.product_id')
                    ->join('brands AS E',   'E.id_brand', '=', 'D.brand_id')
                    ->leftJoin('principles AS F', 'D.principle_id', '=', 'F.id')
                    ->select(
                        'Z.id',
                        'A.user_id AS id_warrior',
                        'A.id AS order_id',
                        DB::raw("
                            IFNULL(B.fullname, '-') AS fullname
                        "),
                        DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                        'A.status AS status_order',
                        DB::raw("
                            IFNULL(D.prod_number, '-') AS prod_number
                        "),
                        DB::raw("
                            IFNULL(D.prod_name, '-') AS prod_name
                        "),
                        'Z.total_item AS quantity',
                        DB::raw("
                            IFNULL(E.brand_name, '-') AS brand_name
                        "),
                        DB::raw("
                            IFNULL(E.brand_logo, 'assets/product_image/_blank.jpg') AS brand_logo
                        "),
                        'E.id_brand',
                        DB::raw('FORMAT(Z.price, 0) AS price'),
                        DB::raw('FORMAT(Z.total_price, 0) AS total_price'),
                        DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                        DB::raw('DAY(Z.created_at) AS day'),
                        DB::raw('MONTH(Z.created_at) AS month'),
                        DB::raw('YEAR(Z.created_at) AS year'),
                        'F.code AS principle_kode',
                    );

                  /* --------------------------------- Request -------------------------------- */

                  $START_DATE              = $request->start_date;
                  $END_DATE                = $request->end_date;
  
                  $limit                   = $request->input('length');
                  $start                   = $request->input('start');
                  $order                   = $columns[$request->input('order.0.column')];
                  $dir                     = $request->input('order.0.dir');
  
                  $data                    = array();
                  $totalData               = 0;
  

  
                  $CASE_FILTER = [
                      0 => (empty($START_DATE)) || (empty($END_DATE)),
                      1 => (!empty($START_DATE)) && (!empty($END_DATE)),   
                  ];
  
  
                  /* ------------------------------ Count Data ----------------------------- */
  
                  if($CASE_FILTER[0]) {
                 
                    $_ = $query->where(function ($where) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8]);
                    })->get();
                            
                    $totalData  = count( $_ );


                  } else if($CASE_FILTER[1]) {

                    $_ = $query->where(function ($where) use ($START_DATE, $END_DATE) {
                                $where->whereNotNull('A.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('A.status', [1, 2, 8])
                                        ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );        
                    })->get();
                    $totalData  = count( $_ );
                 
                  }
  
                  $totalFiltered = $totalData;
                  $_populate	       = '';
  


                /* ------------------------------- Search Data ------------------------------ */


                  if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {
                        
                        $_table = $query
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();
                   
                    } else if($CASE_FILTER[1]) {



                        $_table = $query
                        ->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );        
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

                    }

                    $_populate = $_table;

                  } else {

                      /* ------------------------------- Where Like ------------------------------- */


                      $search = $request->input('search.value');

                      if($CASE_FILTER[0]) {
                        
                        $_table = $query
                        ->where(function ($where) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8]);
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('A.id', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_number', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_name', 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_item', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.total_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('Z.price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(C.short, " - ", C.name)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(D.prod_number, " (", D.prod_name)', ")"), 'LIKE', "%{$search}%")
                            ->orWhere('E.brand_name', 'LIKE', "%{$search}%");
                        });
                   
                    } else if($CASE_FILTER[1]) {
                        
                        $_table = $query
                        ->where(function ($where) use ($START_DATE, $END_DATE) {
                            $where->whereNotNull('A.user_id')
                                    ->whereNotNull('A.id')
                                    ->whereNotIn('A.status', [1, 2, 8])
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );        
                        })
                        ->where(function ($where) use ($search)  {
                            $where->where('fullname', 'LIKE', "%{$search}%")
                            ->orWhere('A.id', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_number', 'LIKE', "%{$search}%")
                            ->orWhere('D.prod_name', 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_item', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('FORMAT(Z.total_price, 0)'), 'LIKE', "%{$search}%")
                            ->orWhere('Z.price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.total_price', 'LIKE', "%{$search}%")
                            ->orWhere('Z.created_at', 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(C.short, " - ", C.name)'), 'LIKE', "%{$search}%")
                            ->orWhere(DB::raw('CONCAT(D.prod_number, " (", D.prod_name)', ")"), 'LIKE', "%{$search}%")
                            ->orWhere('E.brand_name', 'LIKE', "%{$search}%");
                        });

                    }

                    $totalFiltered = count($_table->get());
                    $_populate = $_table->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                }

                
                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {
                        
                        $d['no']                                = $no++;
                        $d['id_order_item']                     = $a->id;
                        $d['id_order']                          = $a->order_id;
                        $d['id_warrior']                        = $a->id_warrior;
                        $d['fullname']                          = $a->fullname;
                        $d['wh_name']                           = $a->wh_name;
                        $d['status_order']                      = $a->status_order;
                        $d['prod_number']                       = $a->prod_number;
                        $d['prod_name']                         = $a->prod_name;
                        $d['quantity']                          = $a->quantity;
                        $d['price']                             = $a->price;
                        $d['total_price']                       = $a->total_price;
                        $d['created_at']                        = $a->created_at;
                        $d['brand_logo']                        = $a->brand_logo;
                        $d['brand_name']                        = $a->brand_name;
                        $d['id_brand']                          = $a->id_brand;
                        $d['principle_kode']                    = $a->principle_kode; 
                        $row++;
                        $data[] = $d;

                    }

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
            }

        } else if($type == self::SALDO_WP) {
          
            /* -------------------------------------------------------------------------- */
            /*                            SALDO WARPAY BUYER                              */
            /* -------------------------------------------------------------------------- */

            try {

                $columns = [null, 'A.code','Z.fullname', 'C.short', 'A.warpay'];

                $query = DB::table('users AS Z')
                        ->leftJoin('user_profile     AS A', 'Z.id', '=', 'A.user_id')
                        ->leftJoin('rajaongkir_city  AS B', 'A.place_id', '=', 'B.city_id')
                        ->leftJoin('warehouse        AS C', 'B.warehouse_id', '=', 'C.id')
                        ->select(
                            'Z.id',
                            'A.code',
                            'Z.fullname',
                            DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                            DB::raw('FORMAT(IFNULL( A.warpay, 0), 0) as warpay')
                        )
                        ->where('Z.role', '=', 4);
                    


                  /* --------------------------------- Request -------------------------------- */

                  $WAREHOUSE_ID         = $this->convertZEROtoPST($request->warehouse_id);


                  $limit                   = $request->input('length');
                  $start                   = $request->input('start');


                  $order                   = $columns[$request->input('order.0.column')];
                  $dir                     = $request->input('order.0.dir');
  
                  $data                    = array();
                  $totalData               = 0;

  
                  /* ------------------------------ Count Data ----------------------------- */
  
                  if(empty($WAREHOUSE_ID)) {
                 
                    $_  = count( $query->get() );

                    $totalData  = ( $_ );


                  } else {
                      
                    $_  =  count( $query->where('C.id', '=', $this->convertPSTtoZERO($WAREHOUSE_ID))->get() );
                   
                    $totalData  = ( $_ );
                  
                  }
  
                  $totalFiltered = $totalData;
                  $_populate	       = '';
  


                /* ------------------------------- Search Data ------------------------------ */

                    
                if (empty($request->input('search.value'))) {
    
                    if(empty($WAREHOUSE_ID)) {
                 
                        $_table  = $query
                                    ->offset($start)
                                    ->limit($limit)
                                    ->orderBy($order, $dir)
                                    ->get();
    
                      } else {
                       
                        $_table  = $query
                                    ->where('C.id', '=', $this->convertPSTtoZERO($WAREHOUSE_ID))
                                    ->offset($start)
                                    ->limit($limit)
                                    ->orderBy($order, $dir)
                                    ->get();
                      
                      }

                    $_populate  = $_table;

                  
                } else {
    
                    $search = $request->input('search.value');
  
                    if(empty($WAREHOUSE_ID)) {


                        $_table = $query
                            ->where(function ($where) use ($search)  {
                                $where->where('Z.id', 'LIKE', "%{$search}%")
                                ->orWhere('Z.fullname', 'LIKE', "%{$search}%")
                                ->orWhere('C.short', 'LIKE', "%{$search}%")
                                ->orWhere('C.name', 'LIKE', "%{$search}%")
                                ->orWhere('A.warpay', 'LIKE', "%{$search}%")
                                ->orWhere(DB::raw('FORMAT(A.warpay, 0)'), 'LIKE', "%{$search}%")
                                ->orWhere(DB::raw('CONCAT(C.short, " - ", C.name)'), 'LIKE', "%{$search}%");
                            });
                    
                        } else {

                        $_table = $query
                            ->where('C.id', '=', $this->convertPSTtoZERO($WAREHOUSE_ID))
                            ->where(function ($where) use ($search)  {
                                $where->where('Z.id', 'LIKE', "%{$search}%")
                                ->orWhere('Z.fullname', 'LIKE', "%{$search}%")
                                ->orWhere('C.short', 'LIKE', "%{$search}%")
                                ->orWhere('C.name', 'LIKE', "%{$search}%")
                                ->orWhere('A.warpay', 'LIKE', "%{$search}%")
                                ->orWhere(DB::raw('FORMAT(A.warpay, 0)'), 'LIKE', "%{$search}%")
                                ->orWhere(DB::raw('CONCAT(C.short, " - ", C.name)'), 'LIKE', "%{$search}%");
                            });

                    }


                    $totalFiltered = count($_table->get());
                    $_populate = $_table->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                }
     

                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {
                        
                        $d['no']                                = $no++;
                        $d['id_warrior']                      = $a->code;
                        $d['fullname']                          = $a->fullname;
                        $d['wh_name']                           = $a->wh_name;
                        $d['warpay_user']                       = $a->warpay;

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

        } else if($type == self::TRANS_PER_WH) {

            /* -------------------------------------------------------------------------- */
            /*                                TRANS_PER_WH                                */
            /* -------------------------------------------------------------------------- */

            try {

                $columns    = [null, 'Z.created_at', 'B.short', 'ttl_warrior_transaksi', 'quantity', 'nominal_transaksi'];

                $query = 
                    DB::table('order AS Z')
                        ->leftJoin('users AS A',   'A.id', '=', 'Z.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'Z.warehouse_id')
                        // ->leftJoin('order_item AS C',   'C.order_id', '=', 'Z.id')
                        ->leftJoin('order_item AS C', function ($join) {
                            $join->on('C.order_id', '=', 'Z.id')
                                 ->on(
                                    'C.id',
                                    '=',
                                    DB::raw("(select min(`id`) from order_item where Z.id = order_item.order_id)")
                                 );
                        })
                        ->select(
                            'Z.id',
                            // DB::raw('SUM(C.total_item) AS quantity'),
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            DB::raw('FORMAT(SUM(Z.total_price), 0) AS nominal_transaksi'),
                            DB::raw('FORMAT(SUM(Z.total_price) + SUM(Z.total_ongkir), 0) AS total_transaksi'),
                            DB::raw('FORMAT(SUM(Z.total_ongkir), 0) AS total_ongkir'),
                            DB::raw('COUNT(Z.user_id) AS ttl_warrior_transaksi'),
                            // 'Z.created_at',
                            DB::raw('DAY(Z.created_at) AS day'),
                            DB::raw('MONTH(Z.created_at) AS month'),
                            DB::raw('YEAR(Z.created_at) AS year'),
                            DB::raw('DATE_FORMAT(Z.created_at, "%m-%Y") AS created_at'),
                        )

                    ->where(function ($where) {
                        $where->whereNotNull('Z.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('Z.status', [1, 2, 8]);          
                    });
                    // ->groupBy(DB::raw('CONCAT(B.short, " - ", B.name)'), DB::raw('MONTH(Z.created_at)'), DB::raw('YEAR(Z.created_at)'))
                    // ->get();
                  /* --------------------------------- Request -------------------------------- */

                  $START_DATE              = $request->start_date;
                  $END_DATE                = $request->end_date;
                  $ID_WAREHOUSE = $this->convertZEROtoPST($request->warehouse_id);

  
                  $limit                   = $request->input('length');
                  $start                   = $request->input('start');
                  $order                   = $columns[$request->input('order.0.column')];
                  $dir                     = $request->input('order.0.dir');
  
                  $data                    = array();
                  $totalData               = 0;
  

                //   return [$START_DATE, $END_DATE, $ID_WAREHOUSE];

  
                  $CASE_FILTER = [
                    0 => ((empty($START_DATE)) || (empty($END_DATE))) && (empty($ID_WAREHOUSE)),
                    1 => (!empty($START_DATE)) && (!empty($END_DATE)) && (empty($ID_WAREHOUSE)),   
                    2 => ((empty($START_DATE)) || (empty($END_DATE))) && (!empty($ID_WAREHOUSE)),
                    3 => (!empty($START_DATE)) && (!empty($END_DATE)) && (!empty($ID_WAREHOUSE)),  
                  ];

                  
  
                  /* ------------------------------ Count Data ----------------------------- */
  
                  if($CASE_FILTER[0]) {
                              
                    $_ = $query
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )->get();
                
        $totalData  = count( $_ );


                  } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $_ = $query->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )->get();
  
                    $totalData  = count( $_ );

                } else if($CASE_FILTER[2]) {

                    $_ = $query->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )->get();
  
                    $totalData  = count( $_ );

                } else if($CASE_FILTER[3]) {
                    
                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

   
                    $_  =  $query->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )            
                                ->groupBy(
                                    DB::raw('CONCAT(B.short, " - ", B.name)'),
                                    // DB::raw('DAY(Z.created_at)'), 
                                    DB::raw('MONTH(Z.created_at)'), 
                                    DB::raw('YEAR(Z.created_at)')
                                )->get(); 
                                    

                    $totalData  = count( $_ );
                
                  }

                  $totalFiltered = $totalData;
                  $_populate	       = '';
  


                /* ------------------------------- Search Data ------------------------------ */


                  if (empty($request->input('search.value'))) {

                    if($CASE_FILTER[0]) {
                        
                        $_table = $query->groupBy(
                            DB::raw('CONCAT(B.short, " - ", B.name)'),
                            // DB::raw('DAY(Z.created_at)'), 
                            DB::raw('MONTH(Z.created_at)'), 
                            DB::raw('YEAR(Z.created_at)')
                        )->offset($start)
                                        ->limit($limit)
                                        ->orderBy($order, $dir)
                                        ->get();
                   
                    } else if($CASE_FILTER[1]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';


                        $_table = $query->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )
                        ->groupBy(
                            DB::raw('CONCAT(B.short, " - ", B.name)'),
                            // DB::raw('DAY(Z.created_at)'), 
                            DB::raw('MONTH(Z.created_at)'), 
                            DB::raw('YEAR(Z.created_at)')
                        )
                                        ->offset($start)
                                        ->limit($limit)
                                        ->orderBy($order, $dir)
                                        ->get();

                    } else if($CASE_FILTER[2]) {


                        $_table = $query->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                        ->groupBy(
                            DB::raw('CONCAT(B.short, " - ", B.name)'),
                            // DB::raw('DAY(Z.created_at)'), 
                            DB::raw('MONTH(Z.created_at)'), 
                            DB::raw('YEAR(Z.created_at)')
                        )
                                        ->offset($start)
                                        ->limit($limit)
                                        ->orderBy($order, $dir)
                                        ->get();

                    } else if($CASE_FILTER[3]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';

                        $_table = $query->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                                        ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )            
                                        ->groupBy(
                                            DB::raw('CONCAT(B.short, " - ", B.name)'),
                                            // DB::raw('DAY(Z.created_at)'), 
                                            DB::raw('MONTH(Z.created_at)'), 
                                            DB::raw('YEAR(Z.created_at)')
                                        )
                                        ->offset($start)
                                        ->limit($limit)
                                        ->orderBy($order, $dir)
                                        ->get();

                    }


                    $_populate = $_table;

                  } else {

                      /* ------------------------------- Where Like ------------------------------- */


                      $search = $request->input('search.value');

                      if($CASE_FILTER[0]) {
                          
                        
                        $_table = $query
                                    ->where(function ($where) use ($search)  {
                                        $where->where(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('FORMAT(SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('COUNT(A.id)'), 'LIKE', "%{$search}%")
                                        // ->orWhere('Z.created_at', 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('SUM(C.total_item)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%");
                                    })
                                    ->groupBy(
                                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                                        // DB::raw('DAY(Z.created_at)'), 
                                        DB::raw('MONTH(Z.created_at)'), 
                                        DB::raw('YEAR(Z.created_at)')
                                    );
                   
                    } else if($CASE_FILTER[1]) {
                        
                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';

                        $_table = $query
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )
                                    ->where(function ($where) use ($search)  {
                                        $where->where(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('FORMAT(SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('COUNT(A.id)'), 'LIKE', "%{$search}%")
                                        // ->orWhere('Z.created_at', 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('C.total_item)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%");;
                                    })
                                    ->groupBy(
                                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                                        // DB::raw('DAY(Z.created_at)'), 
                                        DB::raw('MONTH(Z.created_at)'), 
                                        DB::raw('YEAR(Z.created_at)')
                                    );

                    } else if($CASE_FILTER[2]) {
                        
                        $_table = $query
                                    ->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                                    ->where(function ($where) use ($search)  {
                                        $where->where(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('FORMAT(SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('COUNT(A.id)'), 'LIKE', "%{$search}%")
                                        // ->orWhere('Z.created_at', 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('C.total_item)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%");;
                                    })
                                    ->groupBy(
                                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                                        // DB::raw('DAY(Z.created_at)'), 
                                        DB::raw('MONTH(Z.created_at)'), 
                                        DB::raw('YEAR(Z.created_at)')
                                    );

                    } else if($CASE_FILTER[3]) {

                        $START_DATE = $START_DATE.' 00:00:00';
                        $END_DATE = $END_DATE.' 23:59:59';
                        
                        $_table = $query
                                    ->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                                    ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )            
                                    ->where(function ($where) use ($search)  {
                                        $where->where(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('CONCAT(B.short, " - ", B.name)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('FORMAT(SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('COUNT(A.id)'), 'LIKE', "%{$search}%")
                                        // ->orWhere('Z.created_at', 'LIKE', "%{$search}%");
                                        // ->orWhere(DB::raw('C.total_item)'), 'LIKE', "%{$search}%")
                                        // ->orWhere(DB::raw('SUM(Z.final_total), 0)'), 'LIKE', "%{$search}%");;
                                    })
                                    ->groupBy(
                                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                                        // DB::raw('DAY(Z.created_at)'), 
                                        DB::raw('MONTH(Z.created_at)'), 
                                        DB::raw('YEAR(Z.created_at)')
                                    );

                    }

                    $totalFiltered = count($_table->get());
                    $_populate = $_table->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();


                }

                


                if (!empty($_populate)) {


                    $no  = $start + 1;
                    $row = 0;

                    foreach ($_populate as $a) {

                        
                        
                        $d['no']                                = $no++;
                        $d['wh_name']                           = $a->wh_name;
                        $d['ttl_warrior_transaksi']             = $a->ttl_warrior_transaksi;
                        $d['quantity']                          = OrderItem::select(DB::raw('SUM(order_item.total_item) AS total_item'))->where('order_item.order_id', $a->id)->first()->total_item;
                        
                        $d['nominal_transaksi']                 = $a->nominal_transaksi;
                        $d['total_transaksi']                   = $a->total_transaksi;
                        $d['total_ongkir']                      = $a->total_ongkir;
                        $d['created_at']                        = $a->created_at;
                        // $d['created_at']                        = $a->month < 10 ?  $a->year.'-'.'0'.$a->month :  $a->year.'-'.$a->month;

                        $row++;
                        $data[] = $d;

                    }

                }


                $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

                return json_encode($json_data);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }

        
    }


    public function getReport($type, Request $req) {


            $start_date = $req->start_date;
            $end_date   = $req->end_date;
            
            // $TODAY  = Carbon::now();
            $START_DATE  = $start_date ? $start_date : null;
            $END_DATE    = $end_date   ? $end_date   : null;

            /* ------------------------------- CASE FILTER ------------------------------ */


            $CASE_FILTER = [
                0 => (empty($start_date)) || (empty($end_date)),
                1 => (!empty($start_date)) || (!empty($end_date)),   
            ];


            switch ($type) {

                case self::DAY:

                /* -------------------------------------------------------------------------- */
                /*                                    DAY                                   */
                /* -------------------------------------------------------------------------- */

                    try {

                        if($CASE_FILTER[0]) {

                            $order = DB::table('order')
                            ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                            ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                            ->leftjoin('shipment_types  AS C', 'C.order_id', '=', 'order.id')
                            ->leftjoin('couriers        AS D', 'D.id',       '=', 'C.courier_id')
                            ->select(
                                'A.id',
                                'order.id AS order_id',
                                'A.fullname',
                                'D.name AS courier_name',
                                DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                                'order.status AS status_order',
                                DB::raw('FORMAT(order.total_price, 0) AS nominal_transaksi'),
                                DB::raw('FORMAT(order.final_total, 0) AS total_transaksi'),
                                DB::raw('FORMAT(order.total_ongkir, 0) AS total_ongkir'),
                                DB::raw('DATE_FORMAT(order.created_at, "%d-%m-%Y") AS created_at'),
                                DB::raw('DAY(order.created_at) AS day'),
                                DB::raw('MONTH(order.created_at) AS month'),
                                DB::raw('YEAR(order.created_at) AS year'),
                                DB::raw('SUM(order.final_total) AS sum_final_total')
                            )
                            ->where(function ($where) {
                                $where->whereNotNull('order.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('order.status', [1, 2, 8]);          
                                    })
                            ->orderBy('order.created_at', 'ASC')
                            // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                            ->groupBy('order.id')
                            ->get();
        
                            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order);
        
                        } else {
            
                            $START_DATE = $START_DATE.' 00:00:00';
                            $END_DATE = $END_DATE.' 23:59:59';

                            $order = DB::table('order')
                            ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                            ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                            ->leftjoin('shipment_types  AS C', 'C.order_id', '=', 'order.id')
                            ->leftjoin('couriers        AS D', 'D.id',       '=', 'C.courier_id')
                            ->select(
                                'A.id',
                                'order.id AS order_id',
                                'A.fullname',
                                'D.name AS courier_name',
                                DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                                'order.status AS status_order',
                                DB::raw('FORMAT(order.total_price, 0) AS nominal_transaksi'),
                                DB::raw('FORMAT(order.final_total, 0) AS total_transaksi'),
                                DB::raw('FORMAT(order.total_ongkir, 0) AS total_ongkir'),
                                DB::raw('DATE_FORMAT(order.created_at, "%d-%m-%Y") AS created_at'),
                                DB::raw('DAY(order.created_at) AS day'),
                                DB::raw('MONTH(order.created_at) AS month'),
                                DB::raw('YEAR(order.created_at) AS year'),
                                DB::raw('SUM(order.final_total) AS sum_final_total')
                            )
                            ->where(function ($where) {
                                $where->whereNotNull('order.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('order.status', [1, 2, 8]);          
                                    })
                            ->whereBetween(DB::raw('order.created_at'), [$START_DATE, $END_DATE])
                            ->orderBy('order.created_at', 'DESC')
                            // ->groupBy('A.id', 'B.short', DB::raw('DAY(order.created_at)'), DB::raw('MONTH(order.created_at)'), DB::raw('YEAR(order.created_at)'))
                            ->groupBy('order.id')
                            ->get();
        
                            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order);
            
                        }


                    } catch (Exception $e) {

                        return __jsonResp(false, $e->getMessage(), 500, $e);
                    
                    }

                break;

                case self::MONTH:

                /* -------------------------------------------------------------------------- */
                /*                                    MONTH                                   */
                /* -------------------------------------------------------------------------- */

                    try {
                        
                        if($CASE_FILTER[0]) {

                            $order = DB::table('order')
                            ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                            ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                            ->select(
                                'A.id',
                                'order.id AS order_id',
                                'A.fullname',
                                DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                                'order.status AS status_order',
                                'order.total_price AS nominal_transaksi',
                                'order.final_total AS total_transaksi',
                                'order.total_ongkir AS total_ongkir',
                                'order.created_at',
                                DB::raw('DAY(order.created_at) AS day'),
                                DB::raw('MONTH(order.created_at) AS month'),
                                DB::raw('YEAR(order.created_at) AS year'),
                                DB::raw('SUM(order.final_total) AS sum_final_total')
                            )
                            ->where(function ($where) {
                                $where->whereNotNull('order.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('order.status', [1, 2, 8]);          
                                    })
                            ->groupBy('A.id', 'wh_name', 'month', 'year')
                            ->get();
                            // ->toSql();
                
                            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order);
        
                        } else {
            
                            $order = DB::table('order')
                            ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                            ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                            ->select(
                                'A.id',
                                'order.id AS order_id',
                                'A.fullname',
                                DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                                'order.status AS status_order',
                                'order.total_price AS nominal_transaksi',
                                'order.final_total AS total_transaksi',
                                'order.total_ongkir AS total_ongkir',
                                'order.created_at',
                                DB::raw('DAY(order.created_at) AS day'),
                                DB::raw('MONTH(order.created_at) AS month'),
                                DB::raw('YEAR(order.created_at) AS year'),
                                DB::raw('SUM(order.final_total) AS sum_final_total')
                            )
                            ->where(function ($where) {
                                $where->whereNotNull('order.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('order.status', [1, 2, 8]);          
                                    })
                            ->groupBy('A.id', 'wh_name', 'month', 'year')
                            ->whereBetween(DB::raw('order.created_at'), [$START_DATE, $END_DATE])
                            ->get();
        
                            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order);
            
                        }


                    } catch (Exception $e) {

                        return __jsonResp(false, $e->getMessage(), 500, $e);
                    
                    }


                break;

                case self::YEAR:

                /* -------------------------------------------------------------------------- */
                /*                                    YEAR                                    */
                /* -------------------------------------------------------------------------- */

                    try {
                                
                        if($CASE_FILTER[0]) {

                            $order = DB::table('order')
                            ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                            ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                            ->select(
                                'A.id',
                                'order.id AS order_id',
                                'A.fullname',
                                DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                                'order.status AS status_order',
                                'order.total_price AS nominal_transaksi',
                                'order.final_total AS total_transaksi',
                                'order.total_ongkir AS total_ongkir',
                                'order.created_at',
                                DB::raw('DAY(order.created_at) AS day'),
                                DB::raw('MONTH(order.created_at) AS month'),
                                DB::raw('YEAR(order.created_at) AS year'),
                                DB::raw('SUM(order.final_total) AS sum_final_total')
                            )
                            ->where(function ($where) {
                                $where->whereNotNull('order.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('order.status', [1, 2, 8]);          
                                    })
                            ->groupBy('A.id', 'wh_name', 'year')
                            ->get();
                            // ->toSql();
        
                            return $order;
        
                            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order);
        
                        } else {
            
                            $order = DB::table('order')
                            ->leftJoin('users AS A',   'A.id', '=', 'order.user_id')
                            ->leftJoin('warehouse AS B',   'B.id', '=', 'order.warehouse_id')
                            ->select(
                                'A.id',
                                'order.id AS order_id',
                                'A.fullname',
                                DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                                'order.status AS status_order',
                                'order.total_price AS nominal_transaksi',
                                'order.final_total AS total_transaksi',
                                'order.total_ongkir AS total_ongkir',
                                'order.created_at',
                                DB::raw('DAY(order.created_at) AS day'),
                                DB::raw('MONTH(order.created_at) AS month'),
                                DB::raw('YEAR(order.created_at) AS year'),
                                DB::raw('SUM(order.final_total) AS sum_final_total')
                            )
                            ->where(function ($where) {
                                $where->whereNotNull('order.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('order.status', [1, 2, 8]);          
                                    })
                            ->groupBy('A.id', 'wh_name', 'year')
                            ->whereBetween(DB::raw('order.created_at'), [$START_DATE, $END_DATE])
                            ->get();
        
                            return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order);
            
                        }


                    } catch (Exception $e) {

                        return __jsonResp(false, $e->getMessage(), 500, $e);
                    
                    }

                break;

                case self::WARRIOR:

                /* -------------------------------------------------------------------------- */
                /*                                  WARRIOR                                   */
                /* -------------------------------------------------------------------------- */

                    $order_item = DB::table('order_item AS Z')
                    ->leftJoin('order AS A',   'A.id', '=', 'Z.order_id')
                    ->leftJoin('users AS B',   'B.id', '=', 'A.user_id')
                    ->leftJoin('warehouse AS C',   'C.id', '=', 'A.warehouse_id')
                    ->leftJoin('product AS D',   'D.id', '=', 'Z.product_id')
                    ->select(
                        'Z.id',
                        'A.id AS order_id',
                        DB::raw("
                            IFNULL(B.fullname, '-') AS fullname
                        "),
                        DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                        'A.status AS status_order',
                        'D.prod_number',
                        'D.prod_name',
                        'Z.total_item AS quantity',
                        'Z.price AS price',
                        'Z.total_price AS total_price',
                        'Z.created_at',
                        DB::raw('DAY(Z.created_at) AS day'),
                        DB::raw('MONTH(Z.created_at) AS month'),
                        DB::raw('YEAR(Z.created_at) AS year'),
                    )
                    ->where(function ($where) {
                        $where->whereNotNull('A.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('A.status', [1, 2, 8]);          
                            })
                    // ->groupBy('B.id', 'C.short', DB::raw('DAY(A.created_at)'), DB::raw('MONTH(A.created_at)'), DB::raw('YEAR(A.created_at)'))
                    ->orderBy('Z.created_at', 'DESC')
                    ->get();

                    return __jsonResp(true, 'Data Berhasil diambil', 200, null, $order_item);

                break;

                case self::MUTASI_WP:

                /* -------------------------------------------------------------------------- */
                /*                                MUTASI_WP                                   */
                /* -------------------------------------------------------------------------- */

                    $_table = DB::table('history_in_out_wp AS Z')
                    ->select(
                        'Z.id',
                        'Z.user_id',
                        'fullname',
                        'Z.total',
                        'Z.type',
                        'Z.created_at',
                        DB::raw('CONCAT(E.short, " - ", E.name) AS wh_name'),
                    )
                    ->leftJoin('users            AS B', 'Z.user_id', '=', 'B.id')
                    ->leftJoin('user_profile     AS C', 'B.id', '=', 'C.user_id')
                    ->leftJoin('rajaongkir_city  AS D', 'C.place_id', '=', 'D.city_id')
                    ->leftJoin('warehouse        AS E', 'D.warehouse_id', '=', 'E.id')
                    ->get();

                    return __jsonResp(true, 'Data Berhasil diambil', 200, null, $_table);
                break;


                case self::SALDO_WP:

                /* -------------------------------------------------------------------------- */
                /*                                SALDO_WP                                    */
                /* -------------------------------------------------------------------------- */

                    $_table = DB::table('users AS Z')
                    ->leftJoin('user_profile     AS A', 'Z.id', '=', 'A.user_id')
                    ->leftJoin('rajaongkir_city  AS B', 'A.place_id', '=', 'B.city_id')
                    ->leftJoin('warehouse        AS C', 'B.warehouse_id', '=', 'C.id')
                    ->select(
                        'Z.id',
                        'Z.fullname',
                        DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                        'A.warpay'
                    )
                    ->where('Z.role', '=', 4)
                    ->get();

                    return __jsonResp(true, 'Data Berhasil diambil', 200, null, $_table);
                break;

                case self::BRAND:

                    /* -------------------------------------------------------------------------- */
                    /*                                BRAND                                       */
                    /* -------------------------------------------------------------------------- */

                        $_table = DB::table('order_item AS Z')
                        ->join('order AS A',   'A.id', '=', 'Z.order_id')
                        ->join('users AS B',   'B.id', '=', 'A.user_id')
                        ->join('warehouse AS C',   'C.id', '=', 'A.warehouse_id')
                        ->join('product AS D',   'D.id', '=', 'Z.product_id')
                        ->join('brands AS E',   'E.id_brand', '=', 'D.brand_id')
                        ->select(
                            // 'Z.id',
                            // 'A.user_id AS id_warrior',
                            // 'A.id AS order_id',
                            // DB::raw("
                            //     IFNULL(B.fullname, '-') AS fullname
                            // "),
                            // DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                            // 'A.status AS status_order',
                            // DB::raw("
                            //     IFNULL(D.prod_number, '-') AS prod_number
                            // "),
                            // DB::raw("
                            //     IFNULL(D.prod_name, '-') AS prod_name
                            // "),
                            DB::raw('SUM(Z.total_item) AS quantity'),
                            DB::raw("
                                IFNULL(E.brand_name, '-') AS brand_name
                            "),
                            // DB::raw("
                            //     IFNULL(E.brand_logo, 'assets/product_image/_blank.jpg') AS brand_logo
                            // "),
                            // 'E.id_brand',
                            // DB::raw('FORMAT(Z.price, 0) AS price'),
                            // DB::raw('FORMAT(Z.total_price, 0) AS total_price'),
                            // 'Z.created_at',
                            // DB::raw('DAY(Z.created_at) AS day'),
                            // DB::raw('MONTH(Z.created_at) AS month'),
                            // DB::raw('YEAR(Z.created_at) AS year'),
                        )
                        ->groupBy('E.brand_name')
                        ->whereNotIn('A.status', [1, 2, 8])
                        ->get();

                        return __jsonResp(true, 'Data Berhasil diambil', 200, null, $_table);
                break;


                case self::TRANS_PER_WH:

                    /* -------------------------------------------------------------------------- */
                    /*                                TRANS_PER_WH                                */
                    /* -------------------------------------------------------------------------- */



                    try {


                        $ID_WAREHOUSE = $this->convertZEROtoPST($req->warehouse_id);

                        
                        $CASE_FILTER = [
                            0 => ((empty($START_DATE)) || (empty($END_DATE))) && (empty($ID_WAREHOUSE)),
                            1 => (!empty($START_DATE)) && (!empty($END_DATE)) && (empty($ID_WAREHOUSE)),   
                            2 => ((empty($START_DATE)) || (empty($END_DATE))) && (!empty($ID_WAREHOUSE)),
                            3 => (!empty($START_DATE)) && (!empty($END_DATE)) && (!empty($ID_WAREHOUSE)),  
                        ];
    
                        $query = DB::table('order AS Z')
                        ->leftJoin('users AS A',   'A.id', '=', 'Z.user_id')
                        ->leftJoin('warehouse AS B',   'B.id', '=', 'Z.warehouse_id')
                        // ->leftJoin('order_item AS C',   'C.order_id', '=', 'Z.id')
                        ->leftJoin('order_item AS C', function ($join) {
                            $join->on('C.order_id', '=', 'Z.id')
                                 ->on(
                                    'C.id',
                                    '=',
                                    DB::raw("(select min(`id`) from order_item where Z.id = order_item.order_id)")
                                 );
                        })
                        ->select(
                            DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                            DB::raw('SUM(Z.total_price) AS nominal_transaksi'),
                            DB::raw('SUM(Z.total_price) + SUM(Z.total_ongkir) AS total_transaksi'),
                            DB::raw('SUM(Z.total_ongkir) AS total_ongkir'),
                            DB::raw('COUNT(Z.user_id) AS ttl_warrior_transaksi'),
                            // 'Z.created_at',
                            DB::raw('DAY(Z.created_at) AS day'),
                            DB::raw('MONTH(Z.created_at) AS month'),
                            DB::raw('YEAR(Z.created_at) AS year'),
                            DB::raw('DATE_FORMAT(Z.created_at, "%m-%Y") AS created_at'),
                        );

                        $_table = null;

                        if($CASE_FILTER[0]) {


                            $_table = $query
                            ->where(function ($where) {
                                $where->whereNotNull('Z.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('Z.status', [1, 2, 8]);          
                            })
                            ->groupBy(
                                DB::raw('CONCAT(B.short, " - ", B.name)'),
                                // DB::raw('DAY(Z.created_at)'),
                                DB::raw('MONTH(Z.created_at)'), 
                                DB::raw('YEAR(Z.created_at)')
                            )
                            ->orderBy('Z.created_at', 'ASC')
                            ->get();

                            
                        
                        } else if($CASE_FILTER[1]){
                            
                            $START_DATE = $START_DATE.' 00:00:00';
                            $END_DATE = $END_DATE.' 23:59:59';
    
                            $_table = $query
                            ->where(function ($where) use ($START_DATE, $END_DATE) {
                                $where->whereNotNull('Z.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('Z.status', [1, 2, 8])
                                        ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );          
                            })
                            ->groupBy(
                                DB::raw('CONCAT(B.short, " - ", B.name)'),
                                // DB::raw('DAY(Z.created_at)'),
                                DB::raw('MONTH(Z.created_at)'), 
                                DB::raw('YEAR(Z.created_at)')
                            )
                            ->orderBy('Z.created_at', 'ASC')
                            ->get();
    
    
                        } else if($CASE_FILTER[2]){
    
                            $_table = $query
                            ->where(function ($where) use ($ID_WAREHOUSE) {
                                $where
                                        ->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                                        ->whereNotNull('Z.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('Z.status', [1, 2, 8]);          
                            })
                            
                            ->groupBy(
                                DB::raw('CONCAT(B.short, " - ", B.name)'),
                                // DB::raw('DAY(Z.created_at)'),
                                DB::raw('MONTH(Z.created_at)'), 
                                DB::raw('YEAR(Z.created_at)')
                            )
                            ->orderBy('Z.created_at', 'ASC')
                            ->get();
    
    
                        } else if($CASE_FILTER[3]){

                            $START_DATE = $START_DATE.' 00:00:00';
                            $END_DATE = $END_DATE.' 23:59:59';
    
                            $_table = $query
                            ->where(function ($where) use ($ID_WAREHOUSE) {
                                $where->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                                        ->whereNotNull('Z.user_id')
                                        ->whereNotNull('A.id')
                                        ->whereNotIn('Z.status', [1, 2, 8]);          
                            })
                            ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )
                            ->groupBy(
                                DB::raw('CONCAT(B.short, " - ", B.name)'),
                                // DB::raw('DAY(Z.created_at)'),
                                DB::raw('MONTH(Z.created_at)'), 
                                DB::raw('YEAR(Z.created_at)')
                            )
                            ->orderBy('Z.created_at', 'ASC')
                            ->get();
    
    
                        }

                        // return $_table;

                        // if(count($_table) > 0) {
                        //     $temp = [
                        //         'header'=> ['Tanggal'],
                        //         'body' => []
                        //     ];
                        //     $arr = array();

                        //     foreach($_table as $row) {


                        //         if (!in_array($row->wh_name, $temp['header'])) {
                        //             $temp['header'][] = $row->wh_name;   
                        //         }


                        //         // foreach ($temp['header'] as $val) {
                        //         //     if($val == 'Tanggal') {
                        //         //         $temp['body'][] = [$row->created_at, $row->wh_name, $row->total_transaksi];
                                        
                                        
                        //         //     }
                        //         // }
                        //         // $temp[$row->created_at][$row->wh_name] = $row->total_transaksi; 
                        //         // $idx = array_search($row->wh_name, $temp['header']);
                        //         // foreach ($temp['header'] as $key => $val) {
                        //         //     if($val == $row->wh_name) {
                        //         //         $arr[$row->wh_name] = $row->total_transaksi;
                        //         //     }

                        //         // }

                        //         // if(!array_keys($arr, $row->created_at)) {

                        //             // $arr[$row->created_at][$row->wh_name] = 0;  
                        //         // }

                        //         $arr[] = [$row->created_at,$row->wh_name, +$row->total_transaksi];  


                        //     }


                        //     $newArr = array();
                        //     foreach ($arr as $key2 => $val2) {
                        //         $newArr[] = [$key2, $val2];
                        //     }


                        // }

                        // $header = ['Tanggal'];
                        // $date = [];
                        // $wh = [];
                        // foreach($_table as $row) {
                        //     if(!in_array($row->wh_name, $header, true)){
                        //         array_push($header, $row->wh_name);
                        //     }
                        //     if(!in_array([$row->created_at], $date, true)){

                        //         foreach($header as $h) {
                        //             if($h == $row->wh_name) {
                        //                 // $idx = array_search($row->wh_name, $header);
                        //                 // // array_push($wh, $row->created_at = $idx);

                        //                 // $tmp = new \stdClass();
                        //                 // $tmp->created_at = $row->created_at;
                        //                 // $tmp->idx = $idx;
                        //                 // $wh[] = [$tmp];

                        //                 array_push($date,[$row->created_at]);
       
                        //             }
                        //         }

                        //     }


                        // }


                        // // foreach($date as $d) {
                        // //     foreach($d as $_) {
                        // //        return $_;
                        // //     }
                        // // }

                        // return [$header, $date, $wh, $_table];




                        return __jsonResp(true, 'Data Berhasil diambil', 200, null, $_table);


                    } catch (Exception $e) {
                            return __jsonResp(false, $e->getMessage(), 500, $e);
                    }




                    
                       
                break;
            }


    }

    public function exportTable($type, Request $request) {


        $DATA          = [];
        $QUERY         = null;

        $START_DATE              = $request->start_date;
        $END_DATE                = $request->end_date;

        $CASE_FILTER = [
            0 => (empty($START_DATE)) || (empty($END_DATE)),
            1 => (!empty($START_DATE)) && (!empty($END_DATE)),   
        ];

        if($type == self::DAY) {

            try{

                /* -------------------------------- Get Data -------------------------------- */

                $QUERY =  DB::table('order AS Z')
                ->leftJoin('users AS A',   'A.id', '=', 'Z.user_id')
                ->leftJoin('warehouse AS B',   'B.id', '=', 'Z.warehouse_id')
                ->leftjoin('shipment_types  AS C', 'C.order_id', '=', 'Z.id')
                ->leftjoin('couriers        AS D', 'D.id',       '=', 'C.courier_id')
                ->select(
                    'A.id',
                    'Z.id AS order_id',
                    'A.fullname',
                    DB::raw('CONCAT(D.name, " (", C.courier_service, ")") AS courier_name'),
                    DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                    'Z.status AS status_order',
                    DB::raw('FORMAT(Z.total_price, 0) AS nominal_transaksi'),
                    DB::raw('FORMAT(Z.final_total, 0) AS total_transaksi'),
                    DB::raw('FORMAT(Z.total_ongkir, 0) AS total_ongkir'),
                    DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                    DB::raw('DAY(Z.created_at) AS day'),
                    DB::raw('MONTH(Z.created_at) AS month'),
                    DB::raw('YEAR(Z.created_at) AS year'),
                    DB::raw('FORMAT(SUM(Z.final_total), 0) AS sum_final_total')
                );
                

                if($CASE_FILTER[0]) {

                    $QUERY->where(function ($where) {
                        $where->whereNotNull('Z.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('Z.status', [1, 2, 8]);
                    })
                    // ->groupBy('A.id', 'B.short', DB::raw('DAY(Z.created_at)'), DB::raw('MONTH(Z.created_at)'), DB::raw('YEAR(Z.created_at)'))
                    ->groupBy('Z.id')
                    ->orderBy('Z.created_at', 'DESC');


                } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY->where(function ($where) use ($START_DATE, $END_DATE) {
                        $where->whereNotNull('Z.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('Z.status', [1, 2, 8])
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );          
                    })
                    // ->groupBy('A.id', 'B.short', DB::raw('DAY(Z.created_at)'), DB::raw('MONTH(Z.created_at)'), DB::raw('YEAR(Z.created_at)'))
                    ->groupBy('Z.id')
                    ->orderBy('Z.created_at', 'DESC');
                    
                }


                if(!empty($QUERY->get()) ){

                    $no  = 1;

                    foreach($QUERY->get() as $key => $a){

                        $d['no']        = $no++;
                        $d['id'] = $a->id;
                        $d['created_at'] = $a->created_at;
                        $d['fullname']	= $a->fullname;
                        $d['wh_name']     = $a->wh_name;
                        $d['nominal_transaksi']     = $a->nominal_transaksi;
                        $d['total_ongkir']     = $a->total_ongkir;
                        $d['total_transaksi']     = $a->total_transaksi;
                        $d['courier_name']     = $a->courier_name;

                        array_push($DATA, $d);

                    }

                }

                /* -------------------------------- Column -------------------------------- */
                
                $SPREADSHEET = new Spreadsheet();

                $SHEET = $SPREADSHEET->getActiveSheet();
                $SHEET->setCellValue('A1', 'No');
                $SHEET->setCellValue('B1', 'Tanggal');
                $SHEET->setCellValue('C1', 'ID Warrior');
                $SHEET->setCellValue('D1', 'Nama Warrior');
                $SHEET->setCellValue('E1', 'Warehouse');
                $SHEET->setCellValue('F1', 'Nominal Transaksi');
                $SHEET->setCellValue('G1', 'Ongkir');
                $SHEET->setCellValue('H1', 'Total Transaksi');
                $SHEET->setCellValue('I1', 'Kurir Service');

                $tag = 'A1:I1';


                /* -------------------------------- Style  -------------------------------- */
                
                $styleFont = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => 'ffffff'],
                        // 'size'  => 12,
                        // 'name'  => 'Calibri'
                    ]
                ];

                $SHEET->getRowDimension('1')->setRowHeight(30);
                $SHEET->getStyle($tag)->getAlignment()->setHorizontal('center');
                $SHEET->getStyle($tag)->getAlignment()->setVertical('center');

                $SHEET->getStyle($tag)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3465a4');
                $SHEET->getStyle($tag)->getFont()->setBold( true );
                $SHEET->getStyle($tag)->applyFromArray($styleFont);


                foreach (range('A','I') as $v) {

                    if($v != 'A' && $v != 'C') {
                        $SHEET->getColumnDimension($v)->setWidth(20);
                    }

                    if($v == 'C') {
                        $SHEET->getColumnDimension($v)->setWidth(15);
                    }

                    if($v == 'F' && $v == 'G' && $v == 'H') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('left');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }


                    if($v != 'D' && $v != 'E' && $v != 'I' && $v != 'F' && $v != 'G' && $v != 'H') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('center');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }

                }


                /* -------------------------- Convert ------------------------- */

                $rows = 2;

                foreach($DATA as $col) {
                    $SHEET->setCellValue('A' . $rows, $col['no']);
                    $SHEET->setCellValue('B' . $rows, $col['created_at']);
                    $SHEET->setCellValue('C' . $rows, $col['id']);
                    $SHEET->setCellValue('D' . $rows, $col['fullname']);
                    $SHEET->setCellValue('E' . $rows, $col['wh_name']);
                    $SHEET->setCellValue('F' . $rows, $col['nominal_transaksi']);
                    $SHEET->setCellValue('G' . $rows, $col['total_ongkir']);
                    $SHEET->setCellValue('H' . $rows, $col['total_transaksi']);
                    $SHEET->setCellValue('I' . $rows, $col['courier_name']);

                    $rows++;
                }

                $fileName = 'REPORT-BELANJA-'.date("Y-m-d-His").".xlsx";
                $writer = new Xlsx($SPREADSHEET);
                $writer->save("export/product/".$fileName);

                $genURL = url('/')."/export/product/".$fileName;

                return response()->json([
                    'status' => true,
                    'message' => 'Export Data Berhasil',
                    'data' => $genURL,
                ], 200);

            }catch(Exception $e){

                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);

            }
        } else if($type == self::BRAND) {

            try{

                /* -------------------------------- Get Data -------------------------------- */

                $QUERY =  DB::table('order_item AS Z')
                ->join('order AS A',   'A.id', '=', 'Z.order_id')
                ->join('users AS B',   'B.id', '=', 'A.user_id')
                ->join('warehouse AS C',   'C.id', '=', 'A.warehouse_id')
                ->join('product AS D',   'D.id', '=', 'Z.product_id')
                ->join('brands AS E',   'E.id_brand', '=', 'D.brand_id')
                ->leftJoin('principles AS F', 'D.principle_id', '=', 'F.id')
                ->select(
                    'Z.id',
                    'A.user_id AS id_warrior',
                    'A.id AS order_id',
                    DB::raw("
                        IFNULL(B.fullname, '-') AS fullname
                    "),
                    DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                    'A.status AS status_order',
                    DB::raw("
                        IFNULL(D.prod_number, '-') AS prod_number
                    "),
                    DB::raw("
                        IFNULL(D.prod_name, '-') AS prod_name
                    "),
                    'Z.total_item AS quantity',
                    DB::raw("
                        IFNULL(E.brand_name, '-') AS brand_name
                    "),
                    DB::raw("
                        IFNULL(E.brand_logo, 'assets/product_image/_blank.jpg') AS brand_logo
                    "),
                    'E.id_brand',
                    DB::raw('FORMAT(Z.price, 0) AS price'),
                    DB::raw('FORMAT(Z.total_price, 0) AS total_price'),
                    DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                    DB::raw('DAY(Z.created_at) AS day'),
                    DB::raw('MONTH(Z.created_at) AS month'),
                    DB::raw('YEAR(Z.created_at) AS year'),
                    'F.code AS principle_kode',
                );

                if($CASE_FILTER[0]) {

                    $QUERY->where(function ($where) {
                        $where->whereNotNull('A.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('A.status', [1, 2, 8]);
                    })
                    ->orderBy('Z.created_at', 'DESC');


                } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY->where(function ($where) use ($START_DATE, $END_DATE) {
                        $where->whereNotNull('A.user_id')
                        ->whereNotNull('A.id')
                        ->whereNotIn('A.status', [1, 2, 8])
                        ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );          
                    })
                    ->orderBy('Z.created_at', 'DESC');
                    
                }


                if(!empty($QUERY->get()) ){

                    $no  = 1;

                    foreach($QUERY->get() as $key => $a){

                        $d['no']        = $no++;
                        $d['id'] = $a->id;
                        $d['created_at'] = $a->created_at;
                        $d['brand_name']	= $a->brand_name;
                        $d['principle_kode']     = $a->principle_kode;
                        $d['prod_name']     = $a->prod_name;
                        $d['quantity']     = $a->quantity;

                        array_push($DATA, $d);

                    }

                }

                /* -------------------------------- Column -------------------------------- */
                
                $SPREADSHEET = new Spreadsheet();

                $SHEET = $SPREADSHEET->getActiveSheet();
                $SHEET->setCellValue('A1', 'No');
                $SHEET->setCellValue('B1', 'Tanggal');
                $SHEET->setCellValue('C1', 'Nama Brand');
                $SHEET->setCellValue('D1', 'Kode Principle');
                $SHEET->setCellValue('E1', 'Nama Produk');
                $SHEET->setCellValue('F1', 'Quantity');

                $tag = 'A1:F1';


                /* -------------------------------- Style  -------------------------------- */
                
                $styleFont = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => 'ffffff'],
                    ]
                ];

                $SHEET->getRowDimension('1')->setRowHeight(30);
                $SHEET->getStyle($tag)->getAlignment()->setHorizontal('center');
                $SHEET->getStyle($tag)->getAlignment()->setVertical('center');

                $SHEET->getStyle($tag)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3465a4');
                $SHEET->getStyle($tag)->getFont()->setBold( true );
                $SHEET->getStyle($tag)->applyFromArray($styleFont);


                foreach (range('A','F') as $v) {

                    if($v != 'A' && $v != 'E') {
                        $SHEET->getColumnDimension($v)->setWidth(20);
                    }

                    if($v == 'E') {
                        $SHEET->getColumnDimension($v)->setWidth(60);
                    }


                    if($v != 'C' && $v != 'E') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('center');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }

                }


                /* -------------------------- Convert ------------------------- */

                $rows = 2;

                foreach($DATA as $col) {
                    $SHEET->setCellValue('A' . $rows, $col['no']);
                    $SHEET->setCellValue('B' . $rows, $col['created_at']);
                    $SHEET->setCellValue('C' . $rows, $col['brand_name']);
                    $SHEET->setCellValue('D' . $rows, $col['principle_kode']);
                    $SHEET->setCellValue('E' . $rows, $col['prod_name']);
                    $SHEET->setCellValue('F' . $rows, $col['quantity']);

                    $rows++;
                }

                $fileName = 'REPORT-BRAND-'.date("Y-m-d-His").".xlsx";
                $writer = new Xlsx($SPREADSHEET);
                $writer->save("export/product/".$fileName);

                $genURL = url('/')."/export/product/".$fileName;

                return response()->json([
                    'status' => true,
                    'message' => 'Export Data Berhasil',
                    'data' => $genURL,
                ], 200);

            }catch(Exception $e){

                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);

            }
        } else if($type == self::TRANS_PER_WH) {

            try{

                $ID_WAREHOUSE = $this->convertZEROtoPST($request->warehouse_id);
        

                $CASE_FILTER = [
                    0 => ((empty($START_DATE)) || (empty($END_DATE))) && (empty($ID_WAREHOUSE)),
                    1 => (!empty($START_DATE)) && (!empty($END_DATE)) && (empty($ID_WAREHOUSE)),   
                    2 => ((empty($START_DATE)) || (empty($END_DATE))) && (!empty($ID_WAREHOUSE)),
                    3 => (!empty($START_DATE)) && (!empty($END_DATE)) && (!empty($ID_WAREHOUSE)),  
                ];


                /* -------------------------------- Get Data -------------------------------- */


                $QUERY = DB::table('order AS Z')
                ->leftJoin('users AS A',   'A.id', '=', 'Z.user_id')
                ->leftJoin('warehouse AS B',   'B.id', '=', 'Z.warehouse_id')
                // ->leftJoin('order_item AS C',   'C.order_id', '=', 'Z.id')
                ->leftJoin('order_item AS C', function ($join) {
                    $join->on('C.order_id', '=', 'Z.id')
                         ->on(
                            'C.id',
                            '=',
                            DB::raw("(select min(`id`) from order_item where Z.id = order_item.order_id)")
                         );
                })
                ->select(
                    'Z.id',
                    // DB::raw('SUM(C.total_item) AS quantity'),
                    DB::raw('CONCAT(B.short, " - ", B.name) AS wh_name'),
                    DB::raw('FORMAT(SUM(Z.total_price), 0) AS nominal_transaksi'),
                    DB::raw('FORMAT(SUM(Z.total_price) + SUM(Z.total_ongkir), 0) AS total_transaksi'),
                    DB::raw('FORMAT(SUM(Z.total_ongkir), 0) AS total_ongkir'),
                    DB::raw('COUNT(Z.user_id) AS ttl_warrior_transaksi'),
                    // 'Z.created_at',
                    DB::raw('DAY(Z.created_at) AS day'),
                    DB::raw('MONTH(Z.created_at) AS month'),
                    DB::raw('YEAR(Z.created_at) AS year'),
                    DB::raw('DATE_FORMAT(Z.created_at, "%m-%Y") AS created_at'),
                );


                if($CASE_FILTER[0]) {

                    $QUERY->where(function ($where) {
                        $where->whereNotNull('Z.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('Z.status', [1, 2, 8]);          
                    })
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )
                    ->orderBy('Z.created_at', 'DESC');


                } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY->where(function ($where) use ($START_DATE, $END_DATE) {
                        $where->whereNotNull('Z.user_id')
                            ->whereNotNull('A.id')
                            ->whereNotIn('Z.status', [1, 2, 8])
                            ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );          
                    })
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )
                    ->orderBy('Z.created_at', 'DESC');
                    
                } else if($CASE_FILTER[2]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY->where(function ($where) use ($START_DATE, $END_DATE, $ID_WAREHOUSE) {
                        $where
                            ->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                            ->whereNotNull('Z.user_id')
                            ->whereNotNull('A.id')
                            ->whereNotIn('Z.status', [1, 2, 8]);
                    })
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )
                    ->orderBy('Z.created_at', 'DESC');
                    
                } else if($CASE_FILTER[3]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY
                    ->where(function ($where) use ($START_DATE, $END_DATE, $ID_WAREHOUSE) {
                        $where
                            ->where('Z.warehouse_id', '=', $this->convertPSTtoZERO($ID_WAREHOUSE))
                            ->whereNotNull('Z.user_id')
                            ->whereNotNull('A.id')
                            ->whereNotIn('Z.status', [1, 2, 8])
                            ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );          
                    })
                    ->groupBy(
                        DB::raw('CONCAT(B.short, " - ", B.name)'),
                        // DB::raw('DAY(Z.created_at)'), 
                        DB::raw('MONTH(Z.created_at)'), 
                        DB::raw('YEAR(Z.created_at)')
                    )
                    ->orderBy('Z.created_at', 'DESC');
                    
                }


                if(!empty($QUERY->get()) ){

                    $no  = 1;

                    foreach($QUERY->get() as $key => $a){

                        $d['no']        = $no++;
                        $d['created_at'] = $a->created_at;
                        $d['wh_name']	= $a->wh_name;
                        $d['ttl_warrior_transaksi']     = $a->ttl_warrior_transaksi;
                        $d['quantity']     = OrderItem::select(DB::raw('SUM(order_item.total_item) AS total_item'))->where('order_item.order_id', $a->id)->first()->total_item;
                        $d['nominal_transaksi']     = $a->nominal_transaksi;
                        $d['total_ongkir']     = $a->total_ongkir;
                        $d['total_transaksi']     = $a->total_transaksi;

                        array_push($DATA, $d);

                    }

                }

                /* -------------------------------- Column -------------------------------- */
                
                $SPREADSHEET = new Spreadsheet();

                $SHEET = $SPREADSHEET->getActiveSheet();
                $SHEET->setCellValue('A1', 'No');
                $SHEET->setCellValue('B1', 'Tanggal (Bulan-Tahun)');
                $SHEET->setCellValue('C1', 'Warehouse');
                $SHEET->setCellValue('D1', 'Total Warrior Transaksi');
                $SHEET->setCellValue('E1', 'Quantity');
                $SHEET->setCellValue('F1', 'Nominal Transaksi');
                $SHEET->setCellValue('G1', 'Total Ongkir');
                $SHEET->setCellValue('H1', 'Total Transaksi');

                $tag = 'A1:H1';


                /* -------------------------------- Style  -------------------------------- */
                
                $styleFont = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => 'ffffff'],
                        // 'size'  => 12,
                        // 'name'  => 'Calibri'
                    ]
                ];

                $SHEET->getRowDimension('1')->setRowHeight(30);
                $SHEET->getStyle($tag)->getAlignment()->setHorizontal('center');
                $SHEET->getStyle($tag)->getAlignment()->setVertical('center');

                $SHEET->getStyle($tag)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3465a4');
                $SHEET->getStyle($tag)->getFont()->setBold( true );
                $SHEET->getStyle($tag)->applyFromArray($styleFont);


                foreach (range('A','H') as $v) {

                    if($v != 'A') {
                        $SHEET->getColumnDimension($v)->setWidth(20);
                    }

                    if($v == 'D' || $v == 'F' || $v == 'G' || $v == 'H')  {
                        $SHEET->getColumnDimension($v)->setWidth(30);
                    }

                    if($v == 'G') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('left');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }

                    if($v != 'C' && $v != 'F' && $v != 'G' && $v != 'H') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('center');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }

                }


                /* -------------------------- Convert ------------------------- */

                $rows = 2;

                foreach($DATA as $key => $col) {
                    $SHEET->setCellValue('A' . $rows, $col['no']);
                    $SHEET->setCellValue('B' . $rows, $col['created_at']);
                    $SHEET->setCellValue('C' . $rows, $col['wh_name']);
                    $SHEET->setCellValue('D' . $rows, $col['ttl_warrior_transaksi']);
                    $SHEET->setCellValue('E' . $rows, $col['quantity']);
                    $SHEET->setCellValue('F' . $rows, $col['nominal_transaksi']);
                    $SHEET->setCellValue('G' . $rows, $col['total_ongkir']);
                    $SHEET->setCellValue('H' . $rows, $col['total_transaksi']);
                    
                    $rows++;
                }

           
                $fileName = 'REPORT-TRANSAKSI-PER-CABANG'.date("Y-m-d-His").".xlsx";
                $writer = new Xlsx($SPREADSHEET);
                $writer->save("export/product/".$fileName);

                $genURL = url('/')."/export/product/".$fileName;

                return response()->json([
                    'status' => true,
                    'message' => 'Export Data Berhasil',
                    'data' => $genURL,
                ], 200);

            }catch(Exception $e){

                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);

            }
        } else if($type == self::WARRIOR) { 
 
 
            try{

                /* -------------------------------- Get Data -------------------------------- */

                $QUERY =  DB::table('order_item AS Z')
                ->leftJoin('order AS A',   'A.id', '=', 'Z.order_id')
                ->leftJoin('users AS B',   'B.id', '=', 'A.user_id')
                ->leftJoin('warehouse AS C',   'C.id', '=', 'A.warehouse_id')
                ->leftJoin('product AS D',   'D.id', '=', 'Z.product_id')
                ->leftJoin('principles AS E', 'D.principle_id', '=', 'E.id')
                ->select(
                    'Z.id',
                    'A.user_id AS id_warrior',
                    'A.id AS order_id',
                    DB::raw("
                        IFNULL(B.fullname, '-') AS fullname
                    "),
                    DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                    'A.status AS status_order',
                    'D.prod_number',
                    'D.prod_name',
                    DB::raw('FORMAT(D.prod_modal_price, 0) AS prod_modal_price'),
                    'Z.total_item AS quantity',
                    DB::raw('FORMAT(Z.price, 0) AS price'),
                    DB::raw('FORMAT(Z.total_price, 0) AS total_price'),
                    DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                    DB::raw('DAY(Z.created_at) AS day'),
                    DB::raw('MONTH(Z.created_at) AS month'),
                    DB::raw('YEAR(Z.created_at) AS year'),
                    'E.code AS principle_kode',
                );

                if($CASE_FILTER[0]) {

                    $QUERY->where(function ($where) {
                        $where->whereNotNull('A.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('A.status', [1, 2, 8]);
                    })
                    ->orderBy('Z.created_at', 'DESC');


                } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY->where(function ($where) use ($START_DATE, $END_DATE) {
                        $where->whereNotNull('A.user_id')
                                ->whereNotNull('A.id')
                                ->whereNotIn('A.status', [1, 2, 8])
                                ->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] );          
                    })
                    ->orderBy('Z.created_at', 'DESC');
                    
                }

                if(!empty($QUERY->get() )){

                    $no  = 1;

                    foreach($QUERY->get() as $key => $a){

                        $d['no']        = $no++;
                        $d['created_at'] = $a->created_at;
                        $d['order_id']	= $a->order_id;
                        $d['wh_name']     = $a->wh_name;
                        $d['prod_name']     = $a->prod_name;
                        $d['principle_kode'] = $a->principle_kode;
                        $d['quantity']     =  $a->quantity;
                        $d['price']     =  $a->price;
                        $d['prod_modal_price']     =  $a->prod_modal_price;
                        $d['total_price']     =  $a->total_price;
                        $d['id_warrior']     =  $a->id_warrior;
                        $d['fullname']     =  $a->fullname;

                        array_push($DATA, $d);

                    }

                }

                /* -------------------------------- Column -------------------------------- */
                
                $SPREADSHEET = new Spreadsheet();

                $SHEET = $SPREADSHEET->getActiveSheet();
                $SHEET->setCellValue('A1', 'No');
                $SHEET->setCellValue('B1', 'Tanggal');
                $SHEET->setCellValue('C1', 'Order ID');
                $SHEET->setCellValue('D1', 'Warehouse');
                $SHEET->setCellValue('E1', 'Kode Principle');
                $SHEET->setCellValue('F1', 'Nama Produk');
                $SHEET->setCellValue('G1', 'Quantity');
                $SHEET->setCellValue('H1', 'HPD');
                $SHEET->setCellValue('I1', 'Harga');
                $SHEET->setCellValue('J1', 'Total');
                $SHEET->setCellValue('K1', 'ID Warrior');
                $SHEET->setCellValue('L1', 'Nama Warrior');

                $tag = 'A1:L1';


                /* -------------------------------- Style  -------------------------------- */
                
                $styleFont = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => 'ffffff'],
                    ]
                ];

                $SHEET->getRowDimension('1')->setRowHeight(30);
                $SHEET->getStyle($tag)->getAlignment()->setHorizontal('center');
                $SHEET->getStyle($tag)->getAlignment()->setVertical('center');

                $SHEET->getStyle($tag)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3465a4');
                $SHEET->getStyle($tag)->getFont()->setBold( true );
                $SHEET->getStyle($tag)->applyFromArray($styleFont);


                foreach (['A','B', 'C', 'G', 'K'] as $v) {

                    if($v !== 'A') {
                        $SHEET->getColumnDimension($v)->setWidth(15);
                    }
                    
                    $SHEET->getStyle($v)->getAlignment()->setHorizontal('center');
                    $SHEET->getStyle($v)->getAlignment()->setVertical('center');

                }


                foreach (['D', 'E','F', 'H', 'I', 'J', 'L'] as $v) {

                    if($v == 'F') {
                        $SHEET->getColumnDimension($v)->setWidth(43);
                    } else if($v == 'L') {
                        $SHEET->getColumnDimension($v)->setWidth(25);
                    } else {
                        $SHEET->getColumnDimension($v)->setWidth(16);
                    }
                    
                }


                /* -------------------------- Convert ------------------------- */

                $rows = 2;

                foreach($DATA as $key => $col) {
                    $SHEET->setCellValue('A' . $rows, $col['no']);
                    $SHEET->setCellValue('B' . $rows, $col['created_at']);
                    $SHEET->setCellValue('C' . $rows, $col['order_id']);
                    $SHEET->setCellValue('D' . $rows, $col['wh_name']);
                    $SHEET->setCellValue('E' . $rows, $col['principle_kode']);
                    $SHEET->setCellValue('F' . $rows, $col['prod_name']);
                    $SHEET->setCellValue('G' . $rows, $col['quantity']);
                    $SHEET->setCellValue('H' . $rows, $col['prod_modal_price']);
                    $SHEET->setCellValue('I' . $rows, $col['price']);
                    $SHEET->setCellValue('J' . $rows, $col['total_price']);
                    $SHEET->setCellValue('K' . $rows, $col['id_warrior']);
                    $SHEET->setCellValue('L' . $rows, $col['fullname']);
                    
                    $rows++;
                }

           
                $fileName = 'REPORT-TRANSAKSI-PER-WARRIOR'.date("Y-m-d-His").".xlsx";
                $writer = new Xlsx($SPREADSHEET);
                $writer->save("export/product/".$fileName);

                $genURL = url('/')."/export/product/".$fileName;

                return response()->json([
                    'status' => true,
                    'message' => 'Export Data Berhasil',
                    'data' => $genURL,
                ], 200);

            }catch(Exception $e){

                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);

            }

            
        } else if($type == self::MUTASI_WP) {
            
            try{

                /* -------------------------------- Get Data -------------------------------- */

                $ID_WARRIOR            = $request->id_warrior;

                $CASE_FILTER = [
                    0 => ((empty($START_DATE)) || (empty($END_DATE))) && (empty($ID_WARRIOR)),
                    1 => (!empty($START_DATE)) && (!empty($END_DATE)) && (empty($ID_WARRIOR)),   
                    2 => ((empty($START_DATE)) || (empty($END_DATE))) && (!empty($ID_WARRIOR)),
                    3 => (!empty($START_DATE)) && (!empty($END_DATE)) && (!empty($ID_WARRIOR)),  
                ];

                $QUERY = DB::table('history_in_out_wp AS Z')
                        ->select(
                            'Z.id',
                            'Z.user_id',
                            'B.fullname',
                            'Z.total',
                            'Z.type',
                            'F.fullname AS assign_by',
                            DB::raw('DATE_FORMAT(Z.created_at, "%d-%m-%Y") AS created_at'),
                            DB::raw('FORMAT(C.warpay, 0) AS warpay'),
                            DB::raw('CONCAT(E.short, " - ", E.name) AS wh_name'),
                        )
                        ->leftJoin('users            AS B', 'Z.user_id', '=', 'B.id')
                        ->leftJoin('user_profile     AS C', 'B.id', '=', 'C.user_id')
                        ->leftJoin('rajaongkir_city  AS D', 'C.place_id', '=', 'D.city_id')
                        ->leftJoin('warehouse        AS E', 'D.warehouse_id', '=', 'E.id')
                        ->leftJoin('users            AS F', 'Z.by', '=', 'F.id');

                if($CASE_FILTER[0]) {
                    
                    $QUERY->orderBy('Z.created_at', 'DESC');
                
                } else if($CASE_FILTER[1]) {

                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';

                    $QUERY->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )->orderBy('Z.created_at', 'DESC');

                } else if($CASE_FILTER[2]) {

                    $QUERY->where('Z.user_id', '=', $ID_WARRIOR)->orderBy('Z.created_at', 'DESC');

                } else if($CASE_FILTER[3]) {


                    $START_DATE = $START_DATE.' 00:00:00';
                    $END_DATE = $END_DATE.' 23:59:59';
                    
                    $QUERY->where('Z.user_id', '=', $ID_WARRIOR)->whereBetween( DB::raw('Z.created_at') , [ $START_DATE, $END_DATE ] )->orderBy('Z.created_at', 'DESC'); 

                }

                if(!empty($QUERY->get() )){

                    $no  = 1;

                    foreach($QUERY->get() as $key => $a){

                        $d['no']        = $no++;
                        $d['created_at'] = $a->created_at;
                        $d['user_id']	= $a->user_id;
                        $d['fullname']     = $a->fullname;
                        $d['warpay_user']     =  $a->warpay;
                        $d['warpay_in']                         = ($a->type == SELF::PLUS && $a->total ) ? $a->total : '-';
                        $d['warpay_out']                        = ($a->type == SELF::MIN && $a->total ) ? $a->total : '-';
                        $d['wh_name']     =  $a->wh_name;
                        $d['id_transaksi']                      = $a->id;
                        $d['assign_by']                      = $a->assign_by;
                        

                        array_push($DATA, $d);

                    }

                }

                /* -------------------------------- Column -------------------------------- */
                
                $SPREADSHEET = new Spreadsheet();

                $SHEET = $SPREADSHEET->getActiveSheet();
                $SHEET->setCellValue('A1', 'No');
                $SHEET->setCellValue('B1', 'Tanggal');
                $SHEET->setCellValue('C1', 'ID Warrior');
                $SHEET->setCellValue('D1', 'Nama Warrior');
                $SHEET->setCellValue('E1', 'Saldo WP');
                $SHEET->setCellValue('F1', 'Debit WP');
                $SHEET->setCellValue('G1', 'Kredit WP');
                $SHEET->setCellValue('H1', 'Warehouse');
                $SHEET->setCellValue('I1', 'ID Transaksi');
                $SHEET->setCellValue('J1', 'BY');

                $tag = 'A1:J1';


                /* -------------------------------- Style  -------------------------------- */
                
                $styleFont = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => 'ffffff'],
                    ]
                ];

                $SHEET->getRowDimension('1')->setRowHeight(30);
                $SHEET->getStyle($tag)->getAlignment()->setHorizontal('center');
                $SHEET->getStyle($tag)->getAlignment()->setVertical('center');

                $SHEET->getStyle($tag)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3465a4');
                $SHEET->getStyle($tag)->getFont()->setBold( true );
                $SHEET->getStyle($tag)->applyFromArray($styleFont);


                foreach (range('A','J') as $v) {

                    if($v !== 'A') {
                        $SHEET->getColumnDimension($v)->setWidth(15);
                    }
                    

                    if($v !== 'D' && $v !== 'H' && $v !== 'E') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('center');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }

                    if($v == 'E') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('right');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('right');
                    }
                    


                }


                /* -------------------------- Convert ------------------------- */

                $rows = 2;

                foreach($DATA as $key => $col) {
                    $SHEET->setCellValue('A' . $rows, $col['no']);
                    $SHEET->setCellValue('B' . $rows, $col['created_at']);
                    $SHEET->setCellValue('C' . $rows, $col['user_id']);
                    $SHEET->setCellValue('D' . $rows, $col['fullname']);
                    $SHEET->setCellValue('E' . $rows, $col['warpay_user']);
                    $SHEET->setCellValue('F' . $rows, $col['warpay_in']);
                    $SHEET->setCellValue('G' . $rows, $col['warpay_out']);
                    $SHEET->setCellValue('H' . $rows, $col['wh_name']);
                    $SHEET->setCellValue('I' . $rows, $col['id_transaksi']);
                    $SHEET->setCellValue('J' . $rows, $col['assign_by']);
                    
                    $rows++;
                }

            
                $fileName = 'REPORT-MUTASI-WARRIOR'.date("Y-m-d-His").".xlsx";
                $writer = new Xlsx($SPREADSHEET);
                $writer->save("export/product/".$fileName);

                $genURL = url('/')."/export/product/".$fileName;

                return response()->json([
                    'status' => true,
                    'message' => 'Export Data Berhasil',
                    'data' => $genURL,
                ], 200);
        


            }catch(Exception $e){

                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);

            }
        } else if($type == self::SALDO_WP) {
            
            try {

                $WAREHOUSE_ID = $this->convertZEROtoPST($request->warehouse_id);

                $QUERY = DB::table('users AS Z')
                ->leftJoin('user_profile     AS A', 'Z.id', '=', 'A.user_id')
                ->leftJoin('rajaongkir_city  AS B', 'A.place_id', '=', 'B.city_id')
                ->leftJoin('warehouse        AS C', 'B.warehouse_id', '=', 'C.id')
                ->select(
                    'Z.id',
                    'A.code',
                    'Z.fullname',
                    DB::raw('CONCAT(C.short, " - ", C.name) AS wh_name'),
                    DB::raw('IFNULL( A.warpay, 0) as warpay')
                );

                if(empty($WAREHOUSE_ID)) {

                    $QUERY->where('Z.role', '=', 4)->orderBy('A.warpay', 'DESC');


                } else if(!empty($WAREHOUSE_ID)) {

                    $QUERY->where('Z.role', '=', 4)
                    ->where('C.id', '=', $this->convertPSTtoZERO($WAREHOUSE_ID))
                    ->orderBy('A.warpay', 'DESC');
                    
                }

                if(!empty($QUERY->get() )){

                    $no  = 1;

                    foreach($QUERY->get() as $key => $a){

                        $d['no']        = $no++;
                        $d['code'] = $a->code;
                        $d['fullname']	= $a->fullname;
                        $d['wh_name']     = $a->wh_name;
                        $d['warpay']     =  $a->warpay;

                        array_push($DATA, $d);

                    }

                }

                /* -------------------------------- Column -------------------------------- */
                
                $SPREADSHEET = new Spreadsheet();

                $SHEET = $SPREADSHEET->getActiveSheet();
                $SHEET->setCellValue('A1', 'No');
                $SHEET->setCellValue('B1', 'ID Warrior');
                $SHEET->setCellValue('C1', 'Nama Warrior');
                $SHEET->setCellValue('D1', 'Warehouse');
                $SHEET->setCellValue('E1', 'Saldo');

                $tag = 'A1:E1';


                /* -------------------------------- Style  -------------------------------- */
                
                $styleFont = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => 'ffffff'],
                    ]
                ];

                $SHEET->getRowDimension('1')->setRowHeight(30);
                $SHEET->getStyle($tag)->getAlignment()->setHorizontal('center');
                $SHEET->getStyle($tag)->getAlignment()->setVertical('center');

                $SHEET->getStyle($tag)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3465a4');
                $SHEET->getStyle($tag)->getFont()->setBold( true );
                $SHEET->getStyle($tag)->applyFromArray($styleFont);


                foreach (range('A','E') as $v) {

                    if($v !== 'A') {
                        $SHEET->getColumnDimension($v)->setWidth(15);
                    } 
                    
                    if ($v == 'B' || $v == 'C' || $v == 'D') {
                        $SHEET->getColumnDimension($v)->setWidth(22);
                    }
                    
                    if($v === 'A') {
                        $SHEET->getStyle($v)->getAlignment()->setHorizontal('center');
                        $SHEET->getStyle($v)->getAlignment()->setVertical('center');
                    }
                }


                /* -------------------------- Convert ------------------------- */

                $rows = 2;

                foreach($DATA as $key => $col) {
                    $SHEET->setCellValue('A' . $rows, $col['no']);
                    $SHEET->setCellValue('B' . $rows, $col['code']);
                    $SHEET->setCellValue('C' . $rows, $col['fullname']);
                    $SHEET->setCellValue('D' . $rows, $col['wh_name']);
                    $SHEET->setCellValue('E' . $rows, $col['warpay']);
                    
                    $rows++;
                }

           
                $fileName = 'REPORT-WARPAY-USERS'.date("Y-m-d-His").".xlsx";
                $writer = new Xlsx($SPREADSHEET);
                $writer->save("export/product/".$fileName);

                $genURL = url('/')."/export/product/".$fileName;

                return response()->json([
                    'status' => true,
                    'message' => 'Export Data Berhasil',
                    'data' => $genURL,
                ], 200);


                
            } catch(Exception $e){

                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat kesalahan pada sistem internal.',
                    'error'   => $e->getMessage()
                ], 500);

            }
            
        }
    }
}
