<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\UserProfile;
use Validator;

use DB;

class UserController extends Controller
{
    public function get($id = null)
    {
    	try{

    		if($id){
                $user = User::find($id);
                if(!$user){
                    return response()->json([
                        'message' => "User tidak ditemukan.",
                        'data'    => ['id' => $id]
                    ], 404);
                }
            }else {
                $user = User::all();
            }

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $user
            ], 200);

    	}catch(Exception $e){

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function getBuyer()
    {
        try{

            $user = User::where('role', 4)
            ->leftJoin('user_profile AS a','a.user_id','=','users.id')
            ->select('users.*', 'a.warpay')
            ->get();
            
            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => $user
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function dataTableBuyer(Request $request)
    {
        try{

            $user                   = User::where('email', $request->email)->select('role', 'wh_id')->first();
            $role_id                = $user->role;
            $wh_id                  = $user->wh_id;

            /*
                --- R  O  L  E ---
                1 : Super
                2 : Admin Pusat
                3 : Admin Gudang
                4 : Buyer
                ------------------
            */

            $buyer = null;

            if($role_id == 3) {
                
                $buyer = User::leftJoin('user_profile AS a','a.user_id','=','users.id')
                ->leftJoin('rajaongkir_city AS b', 'a.place_id', '=', 'b.city_id')
                ->where(['users.role' => 4, 'b.warehouse_id' => $wh_id]);
                //->get();

            } else {

                $buyer = User::leftJoin('user_profile AS a','a.user_id','=','users.id')
                ->leftJoin('rajaongkir_city AS b', 'a.place_id', '=', 'b.city_id')
                ->where(['users.role' => 4]);
                //->get();
            
            }


            $buyer = $buyer->select('a.id', 'fullname', 'gender', 'photo', 'photo_ktp', 'email', 'phone', 'active', 'a.postal_code', 'address', 'users.created_at');


            $columns = [null, null, 'id', 'fullname', 'gender', 'photo', 'photo_ktp', 'email', 'phone', 'active', 'postal_code', 'address', 'created_at'];

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            $data          = array();
            $totalData     = count($buyer->get());
            $totalFiltered = $totalData;
            $buyers	       = '';

            if (empty($request->input('search.value'))) {

                $buyers = $buyer
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

            } else {

                $search = $request->input('search.value');

                $tb = $buyer
                        ->where(function ($query) use ($search) {
                            $query->where('fullname', 'LIKE', "%{$search}%");
                            $query->orWhere('email', 'LIKE', "%{$search}%");
                            $query->orWhere('phone', 'LIKE', "%{$search}%");
                            $query->orWhere('a.postal_code', 'LIKE', "%{$search}%");
                            $query->orWhere('address', 'LIKE', "%{$search}%");
                        });

                $buyers = $tb->offset($start)
                            ->limit($limit)
                            ->orderBy($order, $dir)
                            ->get();

                $totalFiltered = $tb->count();

            }

            if (!empty($buyers)) {
                $no  = $start + 1;
                $row = 0;

                foreach ($buyers as $a) {

                    $a->active == '0' ? $status = "<b style='color:red;'>Tidak Aktif</b>" : $status = "<b style='color:green;'>Aktif</b>";

                    $d['no']         = $no++;
                    $d['id']   = $a->id;
                    $d['fullname'] = $a->fullname;
                    $d['gender'] = $a->gender;
                    $d['photo'] = $a->photo;
                    $d['photo_ktp'] = $a->photo_ktp;
                    $d['email'] = $a->email;
                    $d['phone'] = $a->phone;
                    $d['active'] = $status;
                    $d['status'] = $a->active;
                    $d['postal_code'] = $a->postal_code;
                    $d['address'] = $a->address;
                    $d['created_at'] = _customDate($a->created_at);

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
    }

    public function getRequest(Request $request)
    {
    	try{

            if($request->has('email')){
                $data = User::leftJoin('user_role','user_role.id','=','users.role')
                ->where('email',$request->email)
                ->select(
                    'users.*',
                    'user_role.role as role_name'
                )
                ->first();
            }
            
            
            if(!$data){
                return response()->json([
                    'message' => "User tidak ditemukan.",
                    'data'    => ['email' => $request->email]
                ], 404);
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

    public function createUpdate(Request $request)
    {
    	try{

    		$user    = null;
    		$message = null;

            $rules = [
                'email'          => 'unique:users',
            ];

            $validator = Validator::make($request->data, $rules);

            if ($validator->fails()) {
                return __jsonResp(false, ucfirst($validator->errors()->first()), 400);
            }
            
            $data = [
    			'fullname'   => $request->data['fullname'],
    			'email'      => $request->data['email'],
    			'password'	 => Hash::make('sgswarrior2020'),
    			'role'       => $request->data['role'],
    			'active'   	 => $request->data['active'],
    			'created_by' => $request->data['by'],
    			'updated_by' => $request->data['by']
    		];

            if($request->data['wh_id'] != ''){
                $data['wh_id'] = $request->data['wh_id'];
            }else{
                if($data['role'] == 1){
                    $data['wh_id'] = 0;
                }
            }

    		if($request->data['id'] == ''){

    			if($data['role'] == '4'){
    				unset($data['password']);
    			}

                DB::transaction(function () use($data){

                    $user = User::create($data);

                    $profile = UserProfile::create([
                        'user_id'    => $user->id,
                        'photo'      => '-',
                        'photo_ktp'  => '-',
                        'created_by' => $user->created_by,
                        'updated_by' => $user->updated_by
                    ]);

                });

    			$message = 'Pengguna berhasil ditambahkan.';

    		}else{

    			unset($data['created_by'], $data['password']);

    			$user = User::where('id', $request->data['id'])->update($data);
    			$message = 'Data pengguna berhasil diperbarui.';

    		}

    		return response()->json([
                'message' => $message,
                'data'    => $user
            ], 200);

    	}catch(Exception $e){

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

            $user = User::find($id);

            if(!$user){
                return response()->json([
                    'message' => "User tidak ditemukan."
                ], 404);
            }

            $user->deleted_by = $by;
            $user->save();
            $user->delete();

            $user->profile->deleted_by = $by;
            $user->profile->save();
            $user->profile->delete();

            return response()->json([
                'message' => 'Data pengguna berhasil dihapus.',
                'data'    => $request->data
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function getWarpayPoint(Request $request)
    {
        try{

            $user = User::where([
                'email' => $request->email,
                'token' => $request->token
            ])->first();

            $data = UserProfile::where('user_id', $user->id)->first();

            return response()->json([
                'message' => 'Data berhasil diambil.',
                'data'    => [
                    'email'      => $request->email,
                    'warpay_int' => $data->warpay,
                    'warpay'     => _numberFormat($data->warpay) . ' WP',
                    'point_int'  => $data->point,
                    'point'      => _numberFormat($data->point) . ' Point'
                ]
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

    		$columns = [null, null, 'id', 'fullname', 'email', 'role', null, 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'];

	    	$limit = $request->input('length');
	        $start = $request->input('start');
	        $order = $columns[$request->input('order.0.column')];
	        $dir   = $request->input('order.0.dir');

	        $data          = array();
	        $totalData     = User::where('deleted_at', NULL)->count();
	        $totalFiltered = $totalData;
	        $posts	       = '';

	    	if(empty($request->input('search.value'))){

                $posts = User::where('deleted_at', NULL)
	    						->offset($start)
	    						->limit($limit)
	    						->orderBy($order, $dir)
	    						->get();

	    	}else{

	    		$search = $request->input('search.value');

                $tb = User::where('id', 'LIKE', "%{$search}%")
	            				->orWhere('fullname', 'LIKE', "%{$search}%")
	            				->orWhere('email', 'LIKE', "%{$search}%");

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

	        		$media  = null;

	        		if($a->password != null && $a->pin != null){
	        			$media = 1;
	        		}elseif($a->password != null && $a->pin == null){
	        			$media = 2;
	        		}elseif($a->password == null && $a->pin != null){
	        			$media = 3;
	        		}else{
	        			$media = 4;
	        		}

	        		$a->active == '0' ? $status = "<b style='color:red;'>Tidak Aktif</b>" : $status = "<b style='color:green;'>Aktif</b>";

	        		$d['no']	   = $no++;
	        		$d['id']	   = $a->id;
                    $d['fullname'] = $a->fullname;
                    $d['photo'] = $a->profile && $a->profile->photo ? $a->profile->photo : null;
                    $d['photo_ktp'] = $a->profile && $a->profile->photo_ktp ? $a->profile->photo_ktp : null;
	        		$d['email']	   = $a->email;
	        		$d['role']	   = $a->user_role->role;
	        		$d['media']	   = $media;
	        		$d['status']   = $status;

	        		$d['created_at'] = _customDate($a->created_at);
	        		$d['created_by'] = $a->created_by;
	        		$d['updated_at'] = _customDate($a->updated_at);
	        		$d['updated_by'] = $a->updated_by;

                    $d['wh_id'] = $a->wh_id;

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