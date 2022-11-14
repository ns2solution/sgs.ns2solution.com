<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use App\UserProfile;
use App\User;
use App\Setting;
use App\Jobs\SendEmailJob;

use Validator;
use Exception;
use DB;

class ProfileController extends Controller
{

	private function __convertionWarpay()
    {
        return (int)Setting::first()->convertion_warpay;
	}

	public function show(Request $request)
	{

		try {

			$user = User::where('email', $request->email)->first();
			$userProfile = UserProfile::where('user_id', $user->id)->first();

			return response()->json([
				'status' => true,
				'message' => 'OK',
				'data' => [
					'user' => $user,
					'profile' => $userProfile,
				],
			], 200);

		} catch (\Exception $e) {

			return response()->json([
				'status' => false,
				'message' => $e->getMessage(),
				'data' => null,
			], 500);

		}

	}
	
	public function update(Request $request)
	{
		try{

			$userProfile = UserProfile::find($request->data['id']);

            if(!$userProfile){
                return response()->json([
                    'message' => "User profile tidak ditemukan."
                ], 404);
            }

            $place_id   = $request->data['place'] != '' ? $request->data['place'] : null;
            $date_birth = $request->data['birth'] != '' ? $request->data['birth'] : null;

			$update = UserProfile::where('id', $request->data['id'])
			->update([
				'phone'			=> $request->data['no_hp'],
				'address'		=> $request->data['address'],
				'place_id'		=> $place_id,
				'postal_code'	=> $request->data['postal'],
				'gender'		=> $request->data['gender'],
				'date_birth'	=> $date_birth,
				'updated_by'	=> $request->data['by']
			]);

			if($update){

				return response()->json([
					'message' => 'Data pengguna berhasil diperbarui.',
					'data'    => $update
				], 200);

			}else{

				return response()->json([
					'message' => 'Terdapat kesalahan pada sistem internal.'
				], 500);

			}

		}catch(Exception $e){

			return response()->json([
				'message' => 'Terdapat kesalahan pada sistem internal.',
				'error'   => $e->getMessage()
			], 500);

		}
	}


	public function updateWarrior(Request $request, $id)
	{

		$rules = [];

		try{

			if($request->file('photo')) {

				$rules['photo'] 	= 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
	
			} else if ($request->file('photo_ktp')) {
	
				$rules['photo_ktp'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
				
			}

			$validator 			= Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
			}
			
			$userProfile 				= UserProfile::find($id);

            if($request->file('photo')) {

                $photo 					= _uploadFile($request->file('photo'), 'photo');
				$userProfile->photo 	= $photo;
			
			} else if($request->file('photo_ktp')) {
			
				$photo_ktp 				= _uploadFile($request->file('photo_ktp'), 'photo_ktp');
				$userProfile->photo_ktp = $photo_ktp;
			
			}
			
			$user						= $userProfile->user;
			$user->active				= $request->status;


			$user->save();
			$userProfile->save();

			return response()->json([
				'status'  => true,
				'message' => 'Data pengguna berhasil diperbarui.',
			], 200);

		}catch(Exception $e){

			return response()->json([
				'status'  => false,
				'message' => 'Terdapat kesalahan pada sistem internal.',
				'error'   => $e->getMessage()
			], 500);

		}
	}

	public function updateMobile(Request $request)
    {
    	$rules = [
            'fullname'    => 'required|string|min:4|max:100',
            'phone'       => 'required|min:8|max:13|regex:/^\S*$/u',
            'address'     => 'required|min:10|max:255',
            'place_id'    => 'required|exists:place,id|regex:/^\S*$/u',
            'postal_code' => 'min:6|max:6|regex:/^\S*$/u',
            'gender'      => 'min:1|max:1',
            'date_birth'  => 'date_format:Y-m-d'
        ];

        try{

        	$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            if(substr($request->phone, 0, 1) != '8'){
                return response()->json([
                    'message' => 'No HP harus berupa nomor yang valid.'
                ], 400);
            }

            if($request->gender){
            	if($request->gender != 'L' && $request->gender != 'P'){
            		return response()->json([
	                    'message' => 'Gender harus berupa value yang valid.'
	                ], 400);
            	}
            }

            $user = User::where('email', $request->email)->first();
            if(!$user){
                return response()->json([
                    'message' => "User tidak ditemukan.",
                    'data'    => ['email' => $request->email]
                ], 404);
            }

            $data = [
            	'fullname'		=> $request->fullname,
            	'phone'			=> $request->phone,
            	'address'		=> $request->address,
            	'place_id'		=> $request->place_id,
            	'updated_by'	=> $user->id
            ];

            $request->postal_code != '' ? $data['postal_code'] = $request->postal_code : '';
            $request->gender 	  != '' ? $data['gender'] 	   = $request->gender      : '';
            $request->date_birth  != '' ? $data['date_birth']  = $request->date_birth  : '';

            DB::transaction(function () use($user, $request, $data){

            	$user->fullname   = $data['fullname'];
            	$user->updated_by = $user->id;
            	$user->save();

            	unset($data['fullname']);

            	UserProfile::where('user_id', $user->id)->update($data);

            });

            return response()->json([
            	'message' => 'Data pengguna berhasil diperbarui.',
            	'data'    => $data
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }



    public function profileUpdateFull(Request $request)
    {

        // $validator = Validator::make($request->all(), [
            // 'fullname' => "filled|email|min:10|max:50|unique:users,username,{$user->id}",
            // 'name' => 'filled|string|min:4',
            // 'photo' => 'filled|mimes:jpg,jpeg,png|max:2048',
            // 'base64image' => 'filled|string',
        // ]);

        // if ($validator->fails()) {

        //     return response()->json([
        //         'status' => false,
        //         'message' => $validator->errors()->first(),
        //         'error' => $validator->errors()
        //     ], 400);

        // }

        // $store = $user->role_id == 3 ? Store::where('user_id', $user->id)->first() : Store::find($id);
        
        try {

            DB::beginTransaction();

			$user = User::where('email', $request->email)->first();
			$id = $user->id;

			if (is_null($user)) {

				return response()->json([
					'status' => false,
					'message' => 'Akun kamu tidak ditemukan'
				], 400);

			}

			$photo = null;
			$photo_ktp = null;

			if ($request->has('photo')) {

				$dec_photo = base64_decode($request->photo);
				$ext_photo = explode('/', getimagesizefromstring($dec_photo)['mime'])[1];
				
				$conf_ext = _getOptionImageExtension();
				$conf_size = _getOptionImageSize();
	
				if(!in_array($ext_photo, $conf_ext)){
					return response()->json([
						'message' => 'Foto profil harus berupa berkas berjenis JPG / JPEG / PNG'
					], 400);
				}
	
				if(round(_getBase64Size($request->photo), 1) > $conf_size){
					return response()->json([
						'message' => 'Ukuran foto profil tidak boleh lebih dari ' . $conf_size . ' MB'
					], 400);
				}
	
				$photo = 'assets/photo/' . _generateToken(40) . '.' . $ext_photo;
				file_put_contents($photo, $dec_photo);

			}

			if ($request->has('photo_ktp')) {
				
				$dec_photo_ktp = base64_decode($request->photo_ktp);
				$ext_photo_ktp = explode('/', getimagesizefromstring($dec_photo_ktp)['mime'])[1];
				
				$conf_ext = _getOptionImageExtension();
				$conf_size = _getOptionImageSize();
	
				if(!in_array($ext_photo_ktp, $conf_ext)){
					return response()->json([
						'message' => 'Foto KTP harus berupa berkas berjenis JPG / JPEG / PNG'
					], 400);
				}
	
				// if(round(_getBase64Size($request->photo_ktp), 1) > $conf_size){
				// 	return response()->json([
				// 		'message' => 'Ukuran foto KTP tidak boleh lebih dari ' . $conf_size . ' MB'
				// 	], 400);
				// }
	
				$photo_ktp = 'assets/photo_ktp/' . _generateToken(40) . '.' . $ext_photo_ktp;
				file_put_contents($photo_ktp, $dec_photo_ktp);

			}
			
			$cekEmail = User::where('email', $request->email2)->first();

			if (!is_null($cekEmail)) {

				if ($cekEmail->id !== (int)$id) {

					return response()->json([
						'status'  => false,
						'message' => 'Email sudah digunakan.',
					], 400);

				} else {

					$dataUser = [];
					if ($request->has('email2')) $dataUser['email'] = $request->email2;
					if ($request->has('fullname')) $dataUser['fullname'] = $request->fullname;
					$dataUser['email_confirmed'] = 'N';
					$user->update($dataUser);

					$dataUserProfile = $request->only('phone');
		
					if ($request->has('photo')) $dataUserProfile['photo'] = $photo;
					if ($request->has('photo_ktp')) $dataUserProfile['photo_ktp'] = $photo_ktp;

					UserProfile::updateOrCreate(['user_id' => $user->id], $dataUserProfile);

					DB::commit();
		
					return response()->json([
						'message' => 'Data pengguna berhasil diperbarui.',
						'data'    => $user,
						'status'  => true,
					], 200);

				}

			} else {

				// # Email yg gak sama

				$dataUser = [];
				if ($request->has('email2')) $dataUser['email'] = $request->email2;
				if ($request->has('fullname')) $dataUser['fullname'] = $request->fullname;
				$dataUser['email_confirmed'] = 'N';
				$user->update($dataUser);

				$dataUserProfile = $request->only('phone');
		
				if ($request->has('photo')) $dataUserProfile['photo'] = $photo;
				if ($request->has('photo_ktp')) $dataUserProfile['photo_ktp'] = $photo_ktp;

				UserProfile::updateOrCreate(['user_id' => $user->id], $dataUserProfile);

				if ($request->has('email2')) {

					$this->emailVerification([
						'email' => $request->email2,
						'link'  => url('users/verification') . '/' . Crypt::encryptString($request->email2)
					]);

				}

				DB::commit();

				return response()->json([
					'message' => 'Data pengguna berhasil diperbarui. Email aktivasi sudah terkirim, harap aktivasi email anda',
					'data'    => $user,
					'status'  => true,
				], 200);
	

			}

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Maaf! Terjadi kesalahan pada sistem...',
                'error' => $e->getMessage()
            ], 500);

        }

    }


	private function emailVerification($data)
    {

        $data['type'] = 'verification';

        dispatch(new SendEmailJob($data));

        return 'Success';

    }


    public function updatePhoto(Request $request)
    {
    	$rules = [
    		'type'  => 'required|string|regex:/^\S*$/u',
    		'image' => 'required'
    	];

    	try{

    		$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $user = User::where('email', $request->email)->first();
            if(!$user){
                return response()->json([
                    'message' => "User tidak ditemukan.",
                    'data'    => ['email' => $request->email]
                ], 404);
            }

            $type = ['photo', 'photo_ktp'];

            if(!in_array($request->type, $type)){
            	return response()->json([
            		'message' => 'Type harus berupa value yang valid.'
            	], 400);
            }

            $dec_photo = base64_decode($request->image);
            $ext_photo = explode('/', getimagesizefromstring($dec_photo)['mime'])[1];

            $conf_ext  = _getOptionImageExtension();
            $conf_size = _getOptionImageSize();

            if(!in_array($ext_photo, $conf_ext)){
                return response()->json([
                    'message' => 'Foto harus berupa berkas berjenis JPG / JPEG / PNG'
                ], 400);
            }

            if(round(_getBase64Size($request->image), 1) > $conf_size){
                return response()->json([
                    'message' => 'Ukuran foto tidak boleh lebih dari ' . $conf_size . ' MB'
                ], 400);
            }

            $image = 'assets/' . $request->type . '/' . _generateToken(40) . '.' . $ext_photo;
            file_put_contents($image, $dec_photo);

            switch($request->type){

            	case 'photo':
            	case 'photo_ktp':
            		UserProfile::where('user_id', $user->id)->update([
            			$request->type => $image,
            			'updated_by'   => $user->id
            		]);
            		break;
            		
            }

            return response()->json([
            	'message' => 'Foto berhasil disimpan.'
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

            $userProfile = UserProfile::find($id);

            if(!$userProfile){
                return response()->json([
                    'message' => "User profile tidak ditemukan."
                ], 404);
            }

            $userProfile->deleted_by = $by;
            $userProfile->save();
            $userProfile->delete();

            $userProfile->user->deleted_by = $by;
            $userProfile->user->save();
            $userProfile->user->delete();

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

    public function dataTable(Request $request)
    {
    	try{

    		$columns = [null, null, 'id', 'user_id', null, null, null, null, 'gender', 'date_birth', 'warpay', 'point', null, null, 'created_at', 'created_by', 'updated_at', 'updated_by'];

	    	$limit = $request->input('length');
	        $start = $request->input('start');
	        $order = $columns[$request->input('order.0.column')];
	        $dir   = $request->input('order.0.dir');

	        $data          = array();
	        $totalData     = UserProfile::where('deleted_at', NULL)->count();
	        $totalFiltered = $totalData;
			$posts	       = '';

	    	if(empty($request->input('search.value'))){

				$posts = UserProfile::leftJoin('users','users.id','=','user_profile.user_id')
								->when($request, function ($query) use ($request) {
									if ($request->has('warehouse_id')) {
										if(!strcmp($request->warehouse_id,'') == 0){
											$query->where([
												['users.wh_id','=', $request->warehouse_id]
											]);
										}
									}
								})
								->select('user_profile.*')
	    						->offset($start)
	    						->limit($limit)
	    						->orderBy($order, $dir)
	    						->get();

	    	}else{

	    		$search = $request->input('search.value');

				$tb = UserProfile::leftJoin('users','users.id','=','user_profile.user_id')
								->where('user_profile.id', 'LIKE', "%{$search}%")
	            				->orWhere('user_profile.user_id', 'LIKE', "%{$search}%")
	            				->orWhere('user_profile.phone', 'LIKE', "%{$search}%")
								->orWhere('user_profile.postal_code', 'LIKE', "%{$search}%")
								->when($request, function ($query) use ($request) {
									if ($request->has('warehouse_id')) {
										if(!strcmp($request->warehouse_id,'') == 0){
											$query->where([
												['users.wh_id','=', $request->warehouse_id]
											]);
										}
									}
								})
								->select('user_profile.*');

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

	        		$d['no']	  	  = $no++;
	        		$d['id']	  	  = $a->id;
	        		$d['user_id']  	  = $a->user_id;
	        		$d['phone']	  	  = $a->phone 	  	!= '' ? $a->phone    	: '-';
	        		$d['address'] 	  = $a->address   	!= '' ? $a->address  	: '-';
	        		$d['place_id'] 	  = $a->place_id 	!= '' ? $a->place_id 	: '-';
	        		$d['postal_code'] = $a->postal_code != '' ? $a->postal_code : '-';
	        		$d['gender'] 	  = $a->gender   	!= '' ? $a->gender  	: '-';
	        		$d['photo'] 	  = $a->photo   	!= '' ? $a->photo 		: '-';
	        		$d['photo_ktp']   = $a->photo_ktp 	!= '' ? $a->photo_ktp 	: '-';

	        		$d['date_birth']  = $a->date_birth  != '' ? _setCustomDate($a->date_birth)   : '-';

	        		$d['created_at'] = _customDate($a->created_at);
	        		$d['created_by'] = $a->created_by;
	        		$d['updated_at'] = _customDate($a->updated_at);
	        		$d['updated_by'] = $a->updated_by;

                    if($a->user->role == 4){
                        $d['warpay'] = $a->warpay;
                        $d['point']  = $a->point;
                    }else{
                        $d['warpay'] = '-';
                        $d['point']  = '-';
                    }

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
