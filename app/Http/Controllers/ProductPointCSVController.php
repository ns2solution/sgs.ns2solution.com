<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\File;

use Exception;
use Validator;

class ProductPointCSVController extends Controller
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
                'prod_base_point',
                'prod_gram',
                'prod_satuan',
                // 'principle_id',
                // 'brand_id',
                // 'category_id',
                // 'sub_category_id',
                'prod_status_id',
                // 'prod_description'
            ];
            
            $fetch_column[0] = 'prod_number';
            // $fetch_column[5] = 'code';
            // $fetch_column[6] = 'code';
            // $fetch_column[7] = 'category_name';
            // $fetch_column[8] = 'category_name';
            $fetch_column[9] = 'status_name';
            
            $fetch_table[0] = 'product_point';
            // $fetch_table[5] = 'principles';
            // $fetch_table[6] = 'brands';
            // $fetch_table[7] = 'category';
            // $fetch_table[8] = 'category';
            $fetch_table[9] = 'product_status';

            
            //validation sheet
            foreach ($worksheet->toArray() AS $cells) {
                $i = 0;
                $validation_fetch_message = [];

                $validation_fetch_message[0] = 'Nomor Produk Sudah Terdaftar Pada Baris '.$no;
                // $validation_fetch_message[5] = 'Principle Tidak Terdaftar Pada Baris '.$no;
                // $validation_fetch_message[6] = 'Brand Tidak Terdaftar Pada Baris '.$no;
                // $validation_fetch_message[7] = 'Kategori Tidak Terdaftar Pada Baris '.$no;
                // $validation_fetch_message[8] = 'Sub Kategori Tidak terdaftar Kategori : '.$no;
                $validation_fetch_message[9] = 'Status Tidak Terdaftar Pada Baris '.$no;

                $validation_message = [
                    'Nomor Produk Tidak Boleh Kosong Pada Baris '.$no,
                    'Nama Produk Produk Tidak Boleh Kosong Pada Baris '.$no,
                    'Point Tidak Boleh Kosong Pada Baris '.$no,
                    'Height/Netto Tidak Boleh Kosong Pada Baris '.$no,
                    'Satuan Tidak Boleh Kosong Pada Baris '.$no,
                    // 'Principle Tidak Boleh Kosong Pada Baris '.$no,
                    // 'Brand Tidak Boleh Kosong Pada Baris '.$no,
                    // 'Kategori Tidak Boleh Kosong Pada Baris '.$no,
                    // 'Sub Kategori Tidak Boleh Kosong Pada Baris : '.$no,
                    'Status Tidak Boleh Kosong Pada Baris '.$no,
                    // 'Deskripsi Tidak Boleh Kosong Pada Baris '.$no
                ];
                
                if($row_iteration > 0 && is_numeric($cells[2])){
                    for($i=0;$i<count($validation_message);$i++){
                        if(strcmp($cells[$i], '') == 0){
                            return response()->json([
                                'status' => false,
                                'message' => $validation_message[$i] .'--'.$cells[$i]
                            ], 500);
                        }

                        $data[$row_iteration-1][$post_column[$i]] = $cells[$i];
                        
                        if(isset($fetch_table[$i])){
                            // if($i == 6){
                            //     $data_fetch = DB::table($fetch_table[$i])
                            //     ->leftJoin(
                            //         'principles','principles.id',
                            //         '=',
                            //         $fetch_table[$i].'.principle_id'
                            //     )
                            //     ->select($fetch_table[$i].'.id_brand as id')
                            //     ->where([
                            //         [$fetch_table[$i].'.code','=',$cells[$i]],
                            //         ['principles.code','=',$cells[$i-1]]
                            //     ])->first();                        
                            // }else{
                                $data_fetch = DB::table($fetch_table[$i])
                                ->select($fetch_table[$i].'.*')
                                ->where($fetch_column[$i],$cells[$i])
                                ->first();
                            // }                            
                            
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


            DB::table('product_point')->insert($data);

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
