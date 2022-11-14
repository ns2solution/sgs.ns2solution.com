<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Exception;

use App\ProductType;

class ProductTypeController extends Controller
{
    public function get($id = null)
    {
        try {
            if ($id) {
                $pt = ProductType::findOrFail($id);
            } else {
                $pt = ProductType::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $pt
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $rules = [
            'product_type'   => 'required'
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $ps = ProductType::create($request->except('token'));
            
            return response()->json([
                'message' => 'Berhasil Tambah Product Type',
                'data'    => $ps
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'product_type'   => 'required'
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $pt = ProductType::findOrFail($id);
            $pt_new = $pt->update($request->except('token'));
            
            return response()->json([
                'message' => 'Berhasil Ubah Product Type',
                'data'    => $pt
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }
    
    public function delete($id)
    {
        try {
            $pt = ProductType::findOrFail($id);
            $pt_new = $pt->delete();
            
            return response()->json([
                'message' => 'Berhasil Hapus Product Type',
                'data'    => $pt
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }
}