<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\Fill;


use App\StockProductPoint;
use App\Warehouse;
use App\User;
use App\ProductPoint;


use Exception;
use Validator;

class StockProductPointCSVController extends Controller
{
    public function import(Request $request)
    {
        try{

            $path = _uploadFile($request->file('bulk_file'), 'bulk');

            $path =  str_replace('/\/','/\\/', $path);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            // return $path;
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = [];
            $no=1;

            $user_id = $request->by;
            $wh_id = $request->warehouse_id ? $request->warehouse_id : 0;
            $user = User::find($user_id);
            $wh = Warehouse::find($wh_id);

            DB::beginTransaction();
            foreach ($worksheet->toArray() AS $cells) {
                if($no < 2) {
                    if($cells[1] == ''){
                        return response()->json([
                            'status' => false,
                            'message' => 'Kode warehouse tidak boleh kosong',
                        ], 200);
                    }else{
                        $data['warehouse_id'] = $wh_id;
                    }


                    // admin gudang
                    if($user->wh_id != 0) {
                        if($wh->short !== $cells[1]) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Warehouse yang diupdate harus - '.$wh->name.' ('.$wh->short.')',
                            ], 200);
                        }
                    } else {

                    // admin pusat
                        if($wh->short !== $cells[1]) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Warehouse yang diupdate harus sesuai - '.$wh->name.' ('.$wh->short.')',
                            ], 200);
                        }

                    }

                }

                if($no > 2){

                    if($cells[0] == ''){
                        return response()->json([
                            'status' => false,
                            'message' => 'Produk id tidak boleh kosong '.$no,
                        ], 200);
                    }else{
                        $data['product_point_id'] = $cells[0];
                    }

                    if($cells[2] == ''){
                        $data['stock'] = intval(0);
                    }else{
                        $data['stock'] = intval($cells[1]);
                    }


                    StockProductPoint::updateOrCreate([
                        'warehouse_id' => (int) $wh_id,
                        'product_point_id'   => $cells[0]
                    ],[
                        'stock'        => $cells[2],
                        'created_by'   => $user_id
                    ]);

                }

                $no++;
            }


            DB::commit();

            File::delete($path);

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Berhasil update stock bulk',
            ], 200);


        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 200);
        }
    }


    public function export(Request $request) {

        try{

            $data          = array();
            $posts         = '';

            $wh_id = 0; // PST - Pusat  //$request->warehouse_id;
            $wh_name = Warehouse::find($wh_id)->short;

            $posts = ProductPoint::leftJoin('principles AS A', 'A.id', '=', 'product_point.principle_id')
                            ->select('product_point.*', 'A.code', 'A.name AS principle_name')
                            ->where('product_point.deleted_at', NULL)->get();


            if(!empty($posts)){

                $no  = 1;
                $row = 0;

                foreach($posts as $key => $a){

                    $stock = 0;

                    $get_stock = StockProductPoint::where([
                        'warehouse_id' =>  $wh_id,
                        'product_point_id'   => $a->id
                    ])->first();

                    if($get_stock){
                        $stock      = $get_stock->stock;
                    }

                    $d['no']        = $no++;
                    $d['product_point_id'] = $a->id;
                    $d['product_name']	= $a->prod_name;
                    $d['stock']     = $stock;
                    // $d['principle']     = $a->principle_name;

                    $row++;
                    $data[] = $d;

                }

            }

            $spreadsheet = new Spreadsheet();

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Warehouse');
            $sheet->setCellValue('A2', 'Product ID');
            $sheet->setCellValue('B2', 'Product Name');
            $sheet->setCellValue('C2', 'Stock');
            // $sheet->setCellValue('D2', 'Principle');
            $sheet->setCellValue('B1', $wh_name);


            $styleFont = array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'ffffff'),
                    'size'  => 12,
                    'name'  => 'Verdana'
            ));

            $tag_1 = 'A1:B1';
            $tag_2 = 'A2:C2';

            $sheet->getRowDimension('1')->setRowHeight(60);
            $sheet->getColumnDimension('A')->setWidth(23);
            $sheet->getStyle($tag_1)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($tag_1)->getAlignment()->setVertical('center');

            $sheet->getStyle($tag_2)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($tag_2)->getAlignment()->setVertical('center');

            $sheet->getStyle('A')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A')->getAlignment()->setVertical('center');

            $sheet->getStyle('C')->getAlignment()->setHorizontal('left');
            $sheet->getStyle('C')->getAlignment()->setVertical('center');


            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0095ff');
            $sheet->getStyle($tag_1)->getFont()->setBold( true );
            $sheet->getStyle('A1')->applyFromArray($styleFont);

            $styleFontB1[] = $styleFont;
            $styleFontB1['font']['color']['rgb'] = '000000';
            $sheet->getStyle('B1')->applyFromArray($styleFontB1);


            $sheet->getRowDimension('2')->setRowHeight(40);
            $sheet->getColumnDimension('B')->setWidth(70);
            $sheet->getColumnDimension('D')->setWidth(30);

            $sheet->getStyle($tag_2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('48a868');
            $sheet->getStyle($tag_2)->getFont()->setBold( true );
            $styleFontA2toD2[] = $styleFont;
            $styleFontA2toD2['font']['color']['rgb'] = 'ffffff';
            $sheet->getStyle($tag_2)->applyFromArray($styleFontA2toD2);

            $rows = 3;

            foreach($data as $p){
                $sheet->setCellValue('A' . $rows, $p['product_point_id']);
                $sheet->setCellValue('B' . $rows, $p['product_name']);
                $sheet->setCellValue('C' . $rows, $p['stock']);
                // $sheet->setCellValue('D' . $rows, $p['principle']);

                $rows++;
            }

            $fileName = 'PRD-POINT-EXPRT-'.date("Y-m-d-His").".xlsx";
            $writer = new Xlsx($spreadsheet);
            $writer->save("export/product/".$fileName);


            // header("Content-Type: application/vnd.ms-excel");

            // return redirect(url('/')."/export/product/".$fileName);

            $genURL = url('/')."/export/product/".$fileName;

            return response()->json([
                'status' => true,
                'message' => 'Export Stock Product Point Berhasil',
                'data' => $genURL,
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
