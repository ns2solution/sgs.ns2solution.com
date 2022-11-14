<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Validator;
use Exception;

use App\Alasan;

class AlasanController extends Controller
{
    public function get(Request $request, $id = null)
    {
        try{
            if($id){
                $data = Alasan::find($id);
            }else{
                $data =  Alasan::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $data
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
        try {
            $columns = ['id', null, 'alasan', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Alasan::count();
            $totalFiltered = $totalData;
            $posts	       = '';

            if (empty($request->input('search.value'))) {

                $posts = Alasan::select(
                            'master_alasan.*'
                        )
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

            } else {

                $search = $request->input('search.value');

                $tb = Alasan::select(
                            'master_alasan.*'
                        )
                        ->where('alasan', 'LIKE', "%{$search}%");

                $posts = $tb->offset($start)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();

                $totalFiltered = $tb->count();

            }

            if (!empty($posts)) {
                $no  = $start + 1;
                $row = 0;

                foreach ($posts as $a) {

                    $d['no']            = $no++;
                    $d['id']            = $a->id;
                    $d['alasan']        = $a->alasan;
                    $d['created_at']    = _customDate($a->created_at);
                    $d['created_by']    = $a->created_by;
                    $d['updated_at']    = _customDate($a->updated_at);
                    $d['updated_by']    = $a->updated_by;

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

    public function create(Request $request)
    {

        $rules = [
            'alasan'   => 'required'
        ];

        try {
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $by = $request->by;
            $request['created_by'] = $by;

            $alasan = Alasan::create($request->except('token', 'email', 'id'));

            return response()->json([
                'status' => true,
                'message' => 'Alasan berhasil ditambahkan.',
                'data'    => $alasan
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }

    }

    public function update(Request $request, $id)
    {

        $rules = [
            'alasan'   => 'required'
        ];

        try {

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $by = $request->by;
            $request['updated_by'] = $by;

            $alasan = Alasan::where('id', $id)->update($request->except('token', 'email', 'by', 'logo'));

            return response()->json([
                'status' => true,
                'message' => 'Data alasan berhasil diperbarui.',
                'data'    => $alasan
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function delete(Request $request, $id)
    {
        try {
            
            $alasan = Alasan::where('id', $id);
            $alasan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Alasan berhasil dihapus.',
                'data'    => $alasan
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
