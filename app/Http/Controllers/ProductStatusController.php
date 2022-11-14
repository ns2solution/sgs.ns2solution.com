<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Exception;

use App\ProductStatus;

class ProductStatusController extends Controller
{
    public $model = 'Prduct Status';

    public function get($id = null)
    {
        try {
            if ($id) {
                $ps = ProductStatus::findOrFail($id);
            } else {
                $ps = ProductStatus::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $ps
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
            'status_name'   => 'required'
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $ps = ProductStatus::create($request->except('token'));

            return response()->json([
                'message' => 'Berhasil Tambah Product Status',
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
            'status_name'   => 'required'
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $ps = ProductStatus::findOrFail($id);
            $ps_new = $ps->update($request->except('token'));

            return response()->json([
                'message' => 'Berhasil Ubah Product Status',
                'data'    => $ps
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
            $ps = ProductStatus::findOrFail($id);
            $ps_new = $ps->delete();

            return response()->json([
                'message' => 'Berhasil Hapus Product Status',
                'data'    => $ps
            ], 200);
        } catch (Exception $e) {
            return response()->json([
            'message' => 'Terdapat kesalahan pada sistem internal.',
            'error'   => $e->getMessage()
        ], 500);
        }
    }
}
