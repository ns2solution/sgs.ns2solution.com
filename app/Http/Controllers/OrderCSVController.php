<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use App\POPrinciple;
use App\EksportSetting;

use Exception;
use Validator;

class OrderCSVController extends Controller
{
    //
    public const ALL_WAREHOUSE = 'all-warehouse';

    public function export(Request $request) {

        try{

            $data          = array();
            $posts         = '';

            $wh_id = $request->warehouse_id;
            $status_order = $request->status_order;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $eksport_type = intval($request->eksport_type);
            if(!empty($start_date) && !empty($end_date)){
                $eksport_date_start = format_date_to_dateonly($start_date);
                $eksport_time_start = format_date_to_timeonly($start_date);
                $eksport_date_end = format_date_to_dateonly($end_date);
                $eksport_time_end = format_date_to_timeonly($end_date);
            }else{
                $order_list_first = DB::table('order AS a')
                ->select('a.created_at')
                ->latest()->first();
                $order_list_last = DB::table('order AS a')
                ->select('a.created_at')
                ->first();
                $eksport_date_start = format_date_to_dateonly($order_list_first->created_at);
                $eksport_time_start = format_date_to_timeonly($order_list_first->created_at);
                $eksport_date_end = format_date_to_dateonly($order_list_last->created_at);
                $eksport_time_end = format_date_to_timeonly($order_list_last->created_at);
            }
            //check data from setting
            $eksport_setting = DB::table('eksport_settings AS a')
                    ->select('a.*')
                    ->where('eksport_type_id',$eksport_type);

            $cetakan = isset($eksport_setting->latest()->first()->cetakan) ? $eksport_setting->latest()->first()->cetakan : 0;
    
            //$posts = Product::where('deleted_at', NULL)->get();
            //edited
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

            $status_order = !empty($request->status_order) ? explode(",",$status_order) : [1,2,3,4,5,6,7,8,9,10];

            if(empty($request->input('search.value'))){

                $order_details = DB::table('order_item AS b')
                        ->select(
                            'a.id', 
                            'a.no_po', 
                            'a.warehouse_id', 
                            'c.id AS prod_id',
                            'c.principle_id', 
                            'c.prod_number', 
                            'c.prod_name', 
                            'c.prod_modal_price',
                            'c.prod_base_price',
                            'c.diskon AS diskon',
                            'c.prod_number AS kode_produk',
                            'd.path AS prod_image', 
                            'e.id AS status_id',
                            'e.status_name AS status_po', 
                            'f.id AS buyer_id', 
                            'f.fullname AS buyer_name', 
                            'g.name AS warehouse_name',
                            'b.price AS harga_jual',
                            'b.total_price AS total_jual',
                            'b.total_item AS qty_dikonfirmasi',
                            'b.total_item_before AS qty_dipesan',
                            'h.code as principle_code',
                            'a.status',  
                            'a.created_at', 
                            'a.updated_at'
                         )
                        //->leftJoin('order_item AS a', function ($join) {
                        //    $join->on('a.id', '=', 'b.order_id')
                        //        ->on(
                        //            'b.id', '=', DB::raw("(SELECT min(id) FROM order_item  WHERE order_item.order_id = a.id)")
                        //        );
                        //})
                        ->leftJoin('order AS a', function ($join) {
                            $join->on('a.id', '=', 'b.order_id');
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
                        ->leftJoin('warehouse AS g', function ($join) {
                            $join->on('a.warehouse_id', '=', 'g.id');
                        })
                        ->leftJoin('principles AS h', function ($join) {
                            $join->on('c.principle_id', '=', 'h.id');
                        });
                    
                if($case_filter_order[1]) {
                    $order_details = $order_details->where('a.deleted_at', NULL)
                        ->where('a.warehouse_id', $wh_id)
                        
                        ->get();

                } else if($case_filter_order[2]) {

                    $order_details = $order_details->where('a.deleted_at', NULL)
                        
                        ->get();

                } else if($case_filter_order[3]) {
                    $order_details = $order_details->where('a.deleted_at', NULL)
                        ->where('a.warehouse_id', $wh_id)
                        ->whereBetween(DB::raw('CAST(a.created_at AS DATE)'), [$start_date, $end_date])
                        
                        ->get();


                } else if($case_filter_order[4]) {

                    $order_details = $order_details->where('a.deleted_at', NULL)
                        ->whereBetween(DB::raw('DATE(a.created_at)'), [$start_date, $end_date])
                        
                        ->get();

                } else if($case_filter_order[5]) {
                    $order_details = $order_details->where('a.deleted_at', NULL)
                        ->where('a.warehouse_id', $wh_id)
                        ->whereBetween(DB::raw('DATE(a.created_at)'), [$start_date, $end_date])
                        ->whereIn('a.status', $status_order)
                        
                        ->get();

                } else if($case_filter_order[6]) {
                    $order_details = $order_details->where('a.deleted_at', NULL)
                    ->whereBetween(DB::raw('DATE(a.created_at)'), [$start_date, $end_date])
                    ->whereIn('a.status', $status_order)
                    
                    ->get();
                        

                } else if($case_filter_order[7]) {
                    $order_details = $order_details->where('a.deleted_at', NULL)
                        ->where('a.warehouse_id', $wh_id)
                        ->whereIn('a.status', $status_order)
                        
                        ->get();

                } else if($case_filter_order[8]) {
                    $order_details = $order_details
                        ->where('a.deleted_at', NULL)
                        ->whereIn('a.status', $status_order)
                        
                        ->get();

                }

            } 

            //if(!empty($order_details)){
//
            //    $no  = 1;
            //    $row = 0;
//
            //    foreach($order_details as $key => $a){
//
            //        //get detail order
            //        
            //        $stock = 0;
//
            //        $get_stock = StockProduct::where([
            //            'warehouse_id' =>  $wh_id,
            //            'product_id'   => $a->id
            //        ])->first();
//
            //        if($get_stock){
            //            $stock      = $get_stock->stock;
            //        }
//
            //        $d['no']        = $no++;
            //        $d['product_id'] = $a->id;
            //        $d['product_name']	= $a->prod_name;
            //        $d['stock']     = $stock;
//
            //        $row++;
            //        $data[] = $d;
//
            //    }
//
            //}

            $range_header = '';
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $cetakan += 1;
            
            $sheet->setCellValue('A1', 'Tanggal');
            $sheet->setCellValue('A2', 'Jam');
            $sheet->setCellValue('A3', 'Nomor Penarikan');
            $sheet->setCellValue('B1', $eksport_date_start . ' - '. $eksport_date_end);
            $sheet->setCellValue('B2', $eksport_time_start . ' - '. $eksport_time_end);
            $sheet->setCellValue('B3', $cetakan);
            $sheet->setCellValue('D1', 'Tanggal Penarikan');
            $sheet->setCellValue('D2', 'Jam Penarikan');
            $sheet->setCellValue('E1', get_date_now());
            $sheet->setCellValue('E2', get_time_now());

            $rows = 6;

            $styleFont = array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'ffffff'),
                    'size'  => 12,
                    'name'  => 'Verdana'
            ));

            if($eksport_type == 1){
                $range_header = 'A5:L5';
                $sheet->setCellValue('A5', 'Buyer');
                $sheet->setCellValue('B5', 'PO-SGS');
                $sheet->setCellValue('C5', 'Principle');
                $sheet->setCellValue('D5', 'PO Principle');
                $sheet->setCellValue('E5', 'Warehouse');
                $sheet->setCellValue('F5', 'Nama Produk');
                $sheet->setCellValue('G5', 'Status');
                $sheet->setCellValue('H5', 'HPD');
                $sheet->setCellValue('I5', 'H. Jual');
                $sheet->setCellValue('J5', 'Qty dipesan');
                $sheet->setCellValue('K5', 'Qty dikonfirmasi');
                $sheet->setCellValue('L5', 'Total');

                $sheet->getStyle('A')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A')->getAlignment()->setVertical('center');

                $sheet->getStyle('C')->getAlignment()->setHorizontal('left');
                $sheet->getStyle('C')->getAlignment()->setVertical('center');
                
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(70);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(25);
                $sheet->getColumnDimension('K')->setWidth(25);
            }else{
                $range_header = 'A5:I5';
                $sheet->setCellValue('A5', 'Nama Produk');
                $sheet->setCellValue('B5', 'Kode Produk');
                $sheet->setCellValue('C5', 'Principle');
                $sheet->setCellValue('D5', 'HPD');
                $sheet->setCellValue('E5', 'Diskon');
                $sheet->setCellValue('F5', 'Qty');
                $sheet->setCellValue('G5', 'Warehouse');
                $sheet->setCellValue('H5', 'Harga Setelah diskon');
                $sheet->setCellValue('I5', 'Total');

                //$sheet->getStyle('A')->getAlignment()->setHorizontal('center');
                //$sheet->getStyle('A')->getAlignment()->setVertical('center');

                $sheet->getStyle('C')->getAlignment()->setHorizontal('right');
                //$sheet->getStyle('C')->getAlignment()->setVertical('center');
                
                $sheet->getColumnDimension('A')->setWidth(70);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(25);
                $sheet->getColumnDimension('K')->setWidth(25);

            }

            $sheet->getStyle($range_header)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($range_header)->getAlignment()->setVertical('center');

            $sheet->getRowDimension('5')->setRowHeight(40);

            $sheet->getStyle($range_header)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($range_header)->getAlignment()->setVertical('center');
            $sheet->getStyle($range_header)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('48a868');
            $sheet->getStyle($range_header)->getFont()->setBold( true );
            $styleFontA2toL2[] = $styleFont;
            $styleFontA2toL2['font']['color']['rgb'] = 'ffffff';
            $sheet->getStyle($range_header)->applyFromArray($styleFontA2toL2);

            foreach($order_details as $p){
                $po_principles = DB::table('po_principle AS a')
                ->select(
                    'a.no_po AS no_po_principle',
                    'b.code AS principle_code'
                )
                ->leftJoin('principles AS b', function ($join) {
                    $join->on('a.principle_id', '=', 'b.id');
                })
                ->where([
                    ['order_id','=',$p->id],
                    ['principle_id','=',$p->principle_id]
                ])
                ->get();
                $po_principle_num = [];
                $no_po_principle = "";
                $po_principle_code = [];
                $code_po_principle = "";
                if(count($po_principles)){
                    foreach($po_principles as $po_principle) {
                        $po_principle_num[] = $po_principle->no_po_principle;
                        $po_principle_code[] = $po_principle->principle_code;
                    }
                    $no_po_principle = implode(', ', $po_principle_num);
                    $code_po_principle = implode(', ', $po_principle_code);
                }else{
                    $no_po_principle = "-";
                    $code_po_principle ="-";
                }
                //$warehouse = Warehouse::where('id',$request->warehouse);
                if($p->status_id > 2){
                    $p->qty_dipesan = $p->qty_dipesan ? $p->qty_dipesan : $p->qty_dikonfirmasi;
                }else{
                    $p->qty_dipesan = $p->qty_dipesan ? $p->qty_dipesan : "-" ;
                }
                if($eksport_type == 1){
                    $sheet->setCellValue('A' . $rows, $p->buyer_name);
                    $sheet->setCellValue('B' . $rows, $p->no_po);
                    $sheet->setCellValue('C' . $rows, $p->principle_code);
                    $sheet->setCellValue('D' . $rows, $no_po_principle);
                    $sheet->setCellValue('E' . $rows, $p->warehouse_name);
                    $sheet->setCellValue('F' . $rows, $p->prod_name);
                    $sheet->setCellValue('G' . $rows, $p->status_po);
                    $sheet->setCellValue('H' . $rows, $p->prod_modal_price);
                    $sheet->setCellValue('I' . $rows, $p->harga_jual);
                    $sheet->setCellValue('J' . $rows, $p->qty_dikonfirmasi);
                    $sheet->setCellValue('K' . $rows, $p->qty_dipesan);
                    $sheet->setCellValue('L' . $rows, $p->total_jual);
                }else{

                    $harga_diskon = $p->diskon ? $p->prod_modal_price - (($p->diskon/100) * $p->prod_modal_price): $p->prod_modal_price;
                    $harga_diskon = round(str_replace(',', '.', $harga_diskon), 0, PHP_ROUND_HALF_DOWN);

                    $total_jual = $p->qty_dikonfirmasi ? $p->qty_dikonfirmasi * $harga_diskon : 0;
                    $total_jual = round(str_replace(',', '.', $total_jual), 0, PHP_ROUND_HALF_DOWN);

                    $sheet->setCellValue('A' . $rows, $p->prod_name);
                    $sheet->setCellValue('B' . $rows, $p->kode_produk);
                    $sheet->setCellValue('C' . $rows, $p->principle_code);
                    $sheet->setCellValue('D' . $rows, $p->prod_modal_price);
                    $sheet->setCellValue('E' . $rows, $p->diskon);
                    $sheet->setCellValue('F' . $rows, $p->qty_dikonfirmasi);
                    $sheet->setCellValue('G' . $rows, $p->warehouse_name);
                    $sheet->setCellValue('H' . $rows, $harga_diskon);
                    $sheet->setCellValue('I' . $rows, $total_jual);
                }
                $rows++;
            }

            $sheet->getStyle('K6:K'.$rows)->getAlignment()->setHorizontal('right');
            $sheet->getStyle('J6:J'.$rows)->getAlignment()->setHorizontal('right');

            $fileName = 'ORD-EXPRT-'.date("Y-m-d-His").".xlsx";
            $writer = new Xlsx($spreadsheet);
            $writer->save("export/order/".$fileName);


            // header("Content-Type: application/vnd.ms-excel");

            // return redirect(url('/')."/export/product/".$fileName);

            //insert eksport_settings
            //$d_eksport = [];
            //$d_eksport['eksport_type_id'] = $eksport_type;
            //$d_eksport['cetakan'] = $cetakan;
            //$d_eksport['start_date'] = $start_date;
            //$d_eksport['end_date'] = $end_date;

            //DB::beginTransaction();

            //EksportSetting::create($d_eksport);
            
            $genURL = url('/')."/export/order/".$fileName;

            //DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Export Order Berhasil',
                'data' => $genURL,
            ], 200);

        }catch(Exception $e){
            //DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
