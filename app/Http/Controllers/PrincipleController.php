<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use Exception;

use App\Principle;

class PrincipleController extends Controller
{
    public function get($id = null)
    {
        try {

            if ($id) {
                $principle = Principle::findOrFail($id);
            } else {
                $principle = Principle::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $principle
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
            'p_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'code'   => 'required',
            'name'   => 'required'
        ];

        try {

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $upload = _uploadFile($request->file('p_logo'), 'principle');

            $request['logo']       = $upload;
            $request['created_by'] = $request->by;
            $request['updated_by'] = $request->by;

            $principle = Principle::create($request->except('token', 'email', 'id', 'p_logo', 'by', '_token'));
            
            return response()->json([
                'message' => 'Principle berhasil ditambahkan.',
                'data'    => $principle
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
            'logo' => 'image|mimes:jpeg,png,jpg|max:2048',
            'code' => 'required',
            'name' => 'required'
        ];

        try {

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            if($request->file('p_logo')) {
                $upload = _uploadFile($request->file('p_logo'), 'principle');
                $request['logo'] = $upload;
            }

            $request['updated_by'] = $request->by;

            $principle = Principle::where('id', $id)->update($request->except('token', 'email', 'id', 'p_logo', 'by', '_token'));
            
            return response()->json([
                'message' => 'Data principle berhasil diperbarui.',
                'data'    => $principle
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function delete(Request $request)
    {
        try{

            $id = $request->data['id'];
            $by = $request->data['by'];

            $pr = Principle::find($id);

            if(!$pr){
                return response()->json([
                    'message' => "Principle tidak ditemukan."
                ], 404);
            }

            $pr->deleted_by = $by;
            $pr->save();
            $pr->delete();

            return response()->json([
                'message' => 'Principle berhasil dihapus.',
                'data'    => $request->data
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
        try{

            $columns = [null, null, 'id', null, 'code', 'name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = Principle::where('deleted_at', NULL)->count();
            $totalFiltered = $totalData;
            $posts         = '';

            if(empty($request->input('search.value'))){

                $posts = Principle::where('deleted_at', NULL)
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

            }else{

                $search = $request->input('search.value');

                $tb = Principle::where('id', 'LIKE', "%{$search}%")
                                ->orWhere('code', 'LIKE', "%{$search}%")
                                ->orWhere('name', 'LIKE', "%{$search}%");

                $posts = $tb->offset($start)
                                ->limit($limit)
                                ->orderBy($order, $dir)
                                ->get();

                $totalFiltered = $tb->count();

            }

            if(!empty($posts)){

                $no  = $start + 1;
                $row = 0;

                foreach($posts as $key => $a){

                    $d['no']   = $no++;
                    $d['id']   = $a->id;
                    $d['logo'] = $a->logo != '' ? $a->logo : '-';
                    $d['code'] = $a->code;
                    $d['name'] = $a->name;

                    $d['created_at'] = _customDate($a->created_at);
                    $d['created_by'] = $a->created_by;
                    $d['updated_at'] = _customDate($a->updated_at);
                    $d['updated_by'] = $a->updated_by;

                    $row++;
                    $data[] = $d;

                }

            }

            $json_data = array("draw" => intval($request->input('draw')), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);

            echo json_encode($json_data);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}