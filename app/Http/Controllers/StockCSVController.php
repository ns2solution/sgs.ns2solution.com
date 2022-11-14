<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\Fill;


use App\StockProduct;
use App\Warehouse;
use App\User;
use App\Product;


use Exception;
use Validator;

class StockCSVController extends Controller
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
            $wh_id = $request->warehouse_id;
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
                                'message' => 'Warehouse yang diupdate harus sesuai - '.$wh->name.' ('.$wh->short.')',
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
                        $data['product_id'] = $cells[0];
                    }

                    if($cells[3] == ''){
                        $data['stock'] = intval(0);
                    }else{
                        $data['stock'] = intval($cells[3]);
                    }


                    StockProduct::where(['warehouse_id' => (int) $wh_id, 'product_id'   => $cells[0]])->forceDelete();
                    
                    StockProduct::create([
                        'warehouse_id' => (int) $wh_id,
                        'product_id'   => $cells[0],
                        'stock'        => $cells[3],
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

            $wh_id = $request->warehouse_id;
            $wh_name = Warehouse::find($wh_id)->short;

            $posts = Product::leftJoin('principles AS A', 'A.id', '=', 'product.principle_id')
                            ->select('product.*', 'A.code', 'A.name AS principle_name')
                            ->where('product.deleted_at', NULL)->get();


            if(!empty($posts)){

                $no  = 1;
                $row = 0;

                foreach($posts as $key => $a){

                    $stock = 0;

                    $get_stock = StockProduct::where([
                        'warehouse_id' =>  $wh_id,
                        'product_id'   => $a->id
                    ])->first();

                    if($get_stock){
                        $stock      = $get_stock->stock;
                    }

                    $d['no']        = $no++;
                    $d['product_id'] = $a->id;
                    $d['product_number'] = $a->prod_number;
                    $d['product_name']	= $a->prod_name;
                    $d['stock']     = $stock;
                    $d['principle']     = $a->principle_name;

                    $row++;
                    $data[] = $d;

                }

            }

            $spreadsheet = new Spreadsheet();

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Warehouse');
            $sheet->setCellValue('B1', $wh_name);
            $sheet->setCellValue('A2', 'ID');
            $sheet->setCellValue('B2', 'Nama Produk');
            $sheet->setCellValue('C2', 'No Produk');
            $sheet->setCellValue('D2', 'Stok');
            $sheet->setCellValue('E2', 'Principle');


            $styleFont = array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'ffffff'),
                    'size'  => 12,
                    'name'  => 'Verdana'
            ));

            $sheet->getRowDimension('1')->setRowHeight(60);
            $sheet->getColumnDimension('A')->setWidth(23);
            $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1:B1')->getAlignment()->setVertical('center');

            $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A2:E2')->getAlignment()->setVertical('center');

            $sheet->getStyle('A')->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A')->getAlignment()->setVertical('center');

            $sheet->getStyle('D')->getAlignment()->setHorizontal('left');
            $sheet->getStyle('D')->getAlignment()->setVertical('center');


            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0095ff');
            $sheet->getStyle('A1:B1')->getFont()->setBold( true );
            $sheet->getStyle('A1')->applyFromArray($styleFont);

            $styleFontB1[] = $styleFont;
            $styleFontB1['font']['color']['rgb'] = '000000';
            $sheet->getStyle('B1')->applyFromArray($styleFontB1);


            $sheet->getRowDimension('2')->setRowHeight(40);
            $sheet->getColumnDimension('B')->setWidth(60);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(25);

            $sheet->getStyle('A2:E2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('48a868');
            $sheet->getStyle('A2:E2')->getFont()->setBold( true );
            $styleFontA2toE2[] = $styleFont;
            $styleFontA2toE2['font']['color']['rgb'] = 'ffffff';
            $sheet->getStyle('A2:E2')->applyFromArray($styleFontA2toE2);

            $rows = 3;

            foreach($data as $p){
                $sheet->setCellValue('A' . $rows, $p['product_id']);
                $sheet->setCellValue('B' . $rows, $p['product_name']);
                $sheet->setCellValue('C' . $rows, $p['product_number']);
                $sheet->setCellValue('D' . $rows, $p['stock']);
                $sheet->setCellValue('E' . $rows, $p['principle']);

                $rows++;
            }

            $fileName = 'PRD-EXPRT-'.date("Y-m-d-His").".xlsx";
            $writer = new Xlsx($spreadsheet);
            $writer->save("export/product/".$fileName);


            // header("Content-Type: application/vnd.ms-excel");

            // return redirect(url('/')."/export/product/".$fileName);

            $genURL = url('/')."/export/product/".$fileName;

            return response()->json([
                'status' => true,
                'message' => 'Export Stock Product Berhasil',
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
