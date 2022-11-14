<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\File;

use Exception;
use Validator;

class ProductCSVController extends Controller
{
    //
    public function export(Request $request)
    {
        try{

            // $inputFile =  "assets\bulk\b70758678499ded1dfa6f3726e51c2d2950a71c2.xlsx";

            $path = _uploadFile($request->file('bulk_file'), 'bulk');

            $path =  str_replace('/\/','/\\/', $path);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            // return $path;
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = [];
            $no=1;
            $row_iteration=0;
            $data = [];
            $post_column = [];
            $fetch_column = [];
            $fetch_table = [];

            $post_column = [
                'prod_number',
                'prod_name',
                'prod_modal_price',
                'prod_base_price',
                'diskon',
                'prod_gram',
                'prod_satuan',
                'principle_id',
                'brand_id',
                'category_id',
                'sub_category_id',
                'prod_type_id',
                'prod_status_id',
                'prod_description'
            ];
            
            $fetch_column[0] = 'prod_number';
            $fetch_column[7] = 'code';
            $fetch_column[8] = 'code';
            $fetch_column[9] = 'category_name';
            $fetch_column[10] = 'category_name';
            $fetch_column[11] = 'product_type';
            $fetch_column[12] = 'status_name';
            
            $fetch_table[0] = 'product';
            $fetch_table[7] = 'principles';
            $fetch_table[8] = 'brands';
            $fetch_table[9] = 'category';
            $fetch_table[10] = 'category';
            $fetch_table[11] = 'product_type';
            $fetch_table[12] = 'product_status';

            
            //validation sheet
            foreach ($worksheet->toArray() AS $cells) {
                $i = 0;
                $validation_fetch_message = [];

                $validation_fetch_message[0] = 'prod_number Sudah Terdaftar Pada Baris '.$no;
                $validation_fetch_message[7] = 'principle Tidak Terdaftar Pada Baris '.$no;
                $validation_fetch_message[8] = 'brand Tidak Terdaftar Pada Baris '.$no;
                $validation_fetch_message[9] = 'category Kategori Tidak Terdaftar Pada Baris '.$no;
                $validation_fetch_message[10] = 'sub_category Tidak terdaftar category id : '.$no;
                $validation_fetch_message[11] = 'prod_type Produk Tidak Terdaftar Pada Baris '.$no;
                $validation_fetch_message[12] = 'prod_status Tidak Terdaftar Pada Baris '.$no;
                $validation_fetch_message[4] = 'diskon Harus Diisi 0 s/d 100 Pada Baris '.$no;

                $validation_message = [
                    'prod_number Tidak Boleh Kosong Pada Baris '.$no,
                    'prod_name Produk Tidak Boleh Kosong Pada Baris '.$no,
                    'HPD Tidak Boleh Kosong Pada Baris '.$no,
                    'prod_base_price Tidak Boleh Kosong Pada Baris '.$no,
                    'diskon Tidak Boleh Kosong Pada Baris '.$no,
                    'prod_height/netto Tidak Boleh Kosong Pada Baris '.$no,
                    'satuan Tidak Boleh Kosong Pada Baris '.$no,
                    'principle Tidak Boleh Kosong Pada Baris '.$no,
                    'brand Tidak Boleh Kosong Pada Baris '.$no,
                    'category Kategori Tidak Boleh Kosong Pada Baris '.$no,
                    'sub_category Tidak Boleh Kosong Pada Baris : '.$no,
                    'prod_type Produk Tidak Boleh Kosong Pada Baris '.$no,
                    'prod_status Tidak Boleh Kosong Pada Baris '.$no,
                    'description Tidak Boleh Kosong Pada Baris '.$no
                ];
                
                if($row_iteration > 0 && is_numeric($cells[2])){
                    for($i=0;$i<count($validation_message);$i++){
                        if(strcmp($cells[$i], '') == 0){
                            return response()->json([
                                'status' => false,
                                'message' => $validation_message[$i] .'--'.$cells[$i]
                            ], 500);
                        }

                        //validation percent diskon
                        if($i == 4 && $cells[$i] < 0 || $i == 4 && $cells[$i] > 100){
                            return response()->json([
                                'status' => false,
                                'message' => $validation_fetch_message[$i]
                            ], 500);
                        }

                        $data[$row_iteration-1][$post_column[$i]] = $cells[$i];
                        
                        if(isset($fetch_table[$i])){
                            if($i == 8){
                                $data_fetch = DB::table($fetch_table[$i])
                                ->leftJoin(
                                    'principles','principles.id',
                                    '=',
                                    $fetch_table[$i].'.principle_id'
                                )
                                ->select($fetch_table[$i].'.id_brand as id')
                                ->where([
                                    [$fetch_table[$i].'.code','=',$cells[$i]],
                                    ['principles.code','=',$cells[$i-1]]
                                ])->first();                        
                            }else{
                                $data_fetch = DB::table($fetch_table[$i])
                                ->select($fetch_table[$i].'.*')
                                ->where($fetch_column[$i],$cells[$i])
                                ->first();
                            }                            
                            
                            //validation product_number
                            if(!is_null($data_fetch) && $i == 0){
                                return response()->json([
                                    'status' => false,
                                    'message' => $validation_fetch_message[$i]
                                ], 500); 
                            }

                            if(is_null($data_fetch) && $i > 0){
                                return response()->json([
                                    'status' => false,
                                    'message' => $validation_fetch_message[$i]
                                ], 500);
                            }
                            
                            if($i > 0){
                                $data[$row_iteration-1][$post_column[$i]] = $data_fetch->id;
                            }
                        }
                    }
                }
                $no++;
                $row_iteration++;
                $rows[] = $cells;
            }

            DB::beginTransaction();

            DB::table('product')->insert($data);

            DB::commit();

            File::delete($path);

            return response()->json([
                'status' => true,
                'message' => 'Berhasil Tambah Bulk Produk',
                'data' => $data
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
