<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

use DB;

use Validator;
use Exception;

use App\User;
use App\UserProfile;
use App\UserRole;
use App\OTPCode;
use App\Warehouse;
use App\Place;
use App\Jobs\SendEmailJob;

class AuthController extends Controller
{
    public function Login(Request $request)
    {
    	$rules = [
            'media' => 'required|max:1',
            'email' => 'required|string|email|max:100|regex:/^\S*$/u'
        ];

        try{

            switch($request->media){
                case 1:
                    // Mobile
                    break;
                case 2:
                    $rules['password'] = 'required|string|min:6|max:100';
                    break;
                default:
                    return response()->json([
                        'message' => 'Masukkan media dengan 1 atau 2.'
                    ], 400);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }
            
            $user = User::where('email', $request->email)->first();

            if(!$user){
                return response()->json([
                    'message' => "Email yang Anda masukkan belum terdaftar."
                ], 404);
            }

            if(array_key_exists('password', $rules)){

                if(Hash::check($request->password, $user->password)){

                    if($user->email_confirmed === 'Y') {
                        
                        if($user->active == 1){

                                if(!$user->token){
                                    $user->token = _generateToken();
                                }

                                $user->updated_by = $user->id;
                                $user->save();

                                $profile = $user->profile;

                                return response()->json([
                                    'message' => 'Login berhasil.',
                                    'data'    =>
                                        [
                                            compact('user', 'profile')
                                        ],
                                    'token'   => $user->token
                                ], 200);

                        }else{

                                $user->updated_by = $user->id;
                                $user->token      = null;
                                $user->save();

                                return response()->json([
                                    'message' => 'Akun Anda telah dinonaktifkan sementara/selamanya.'
                                ], 401);

                        }

                    } else {


                        // $this->emailVerification([
                        //     'email' => $user->email,
                        //     'link'  => url('users/verification') . '/' . Crypt::encryptString($user->email)
                        // ]);

                        return response()->json([
                            'status'  => false,
                            'message' => 'Aktifasi Email anda terlebih dahulu. ',
                        ], 501);


                    }

                }else{

                    return response()->json([
                        'message' => 'Password yang Anda masukkan salah.'
                    ], 401);

                }

            }else{

                if($user->active == 1){

                    if($user->email_confirmed === 'Y')  {

                        return $this->ResendOTP('login', $user->email);
                    
                    } else {

                        return response()->json([
                            'status'  => false,
                            'message' => 'Aktifasi Email anda terlebih dahulu. ',
                        ], 501);

                    }

                }elseif($user->active == 2){

                    return $this->ResendOTP('register', $user->email);

                }else{

                    $user->updated_by = $user->id;
                    $user->token      = null;
                    $user->save();

                    return response()->json([
                        'message' => 'Akun Anda telah dinonaktifkan sementara/selamanya.'
                    ], 401);

                }

            }

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function CheckOTP(Request $request)
    {
        $rules = [
            'otp'   => 'required|min:4|max:4',
            'email' => 'required|string|email|max:100|regex:/^\S*$/u',
            'type'  => 'required|string|max:10|regex:/^\S*$/u'
        ];

        try{

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            $otp  = OTPCode::where([
                'email' => $request->email,
                'type'  => $request->type
            ])
            ->where('expired_at', '>=', _time())
            ->first();

            if(!$otp){
                return response()->json([
                    'message' => "Waktu sesi OTP telah habis, silahkan kirim ulang OTP."
                ], 404);
            }

            
            if($request->otp == _decryptNumber($otp->otp)){

                $otp->delete();

                $user    = User::where('email', $request->email)->first();
                $profile = $user->profile;

                $user->active     = 1;
                $user->updated_by = $user->id;
                $user->token      = _generateToken();
                $user->save();

                $user['warehouse_id'] = Place::find($profile->place_id)->warehouse_id;
                $user['warehouse_name'] = Warehouse::find($user['warehouse_id'])->name;

                $message = '';
                $code    = '';

                if(!$user->pin){

                    $message = 'Buat PIN akun SGS Anda.';
                    $code    = 0;

                }else{

                    $message = 'Login berhasil.';
                    $code    = 1;

                }

                return response()->json([
                    'message' => $message,
                    'code'    => $code,
                    'data'    => [
                        'user' => $user,
                        'profile' => $profile,
                        'token' => $user->token
                    ],
                ], 200);

            }else{

                return response()->json([
                    'message' => 'OTP yang Anda masukkan salah.'
                ], 400);

            }

        }catch(Exception $e){

            __error([__FUNCTION__, $e->getMessage()]);

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
            ], 500);

        }
    }

    public function ResendOTP($type, $email)
    {
        try{

            $user = User::where('email', $email)->first();

            if(!$user){
                return response()->json([
                    'message' => "Email tidak ditemukan."
                ], 404);
            }

            $code = '';
            $otp  = OTPCode::where([
                        'email' => $email,
                        'type'  => $type
                    ])
                    ->where('expired_at', '>=', _time())
                    ->first();

            if(!$otp){
                $code = _generateOTP();
            }else{
                $code = $otp->otp;
            }

            $data = [
                'type'  => $type,
                'email' => $user->email,
                'name'  => $user->fullname,
                'otp'   => $code
            ];

            $this->SendOTPCode($data);

            unset($data['otp'], $data['type']);

            return response()->json([
                'message' => 'Kami telah mengirimkan kode OTP ke alamat email Anda.',
                'data'    => $data
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    private function __generateUserCode($wh_code, $latest_user)
    {
        $code_1 = 'SWU';
        $code_2 =  $wh_code;
        $code_3 = date('m');
        $code_4 = date('y');
        $code_5 = str_pad(($latest_user != 0) ? ($latest_user + 1) : 1, 3, "0", STR_PAD_LEFT);

    	return $code_1 . $code_2 . $code_3 . $code_4 . $code_5;
    }

    public function Register(Request $request)
    {
        DB::beginTransaction();

        $rules = [
            'fullname'       => 'required|string|min:4|max:100',
            'email'          => 'required|string|email|max:100|regex:/^\S*$/u|unique:users,deleted_at',
            'phone'          => 'required|min:8|max:13|regex:/^\S*$/u',
            'address'        => 'required|min:10|max:255',
            'place_id'       => 'required|regex:/^\S*$/u',
            'subdistrict_id' => 'required|regex:/^\S*$/u',
            'photo'          => 'required',
            'photo_ktp'      => 'required',
            'gender'         => 'required',
            'birth_date'         => 'required',
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

            $CHECK_PHONE = UserProfile::where('phone', $request->phone)->first();

            if($CHECK_PHONE){
                return response()->json([
                    'message' => 'No HP sudah terdaftar sebelumnya.'
                ], 400);
            }

            $dec_photo     = base64_decode($request->photo);
            $dec_photo_ktp = base64_decode($request->photo_ktp);

            $ext_photo     = explode('/', getimagesizefromstring($dec_photo)['mime'])[1];
            $ext_photo_ktp = explode('/', getimagesizefromstring($dec_photo_ktp)['mime'])[1];

            $conf_ext  = _getOptionImageExtension();
            $conf_size = _getOptionImageSize();

            if(!in_array($ext_photo, $conf_ext)){
                return response()->json([
                    'message' => 'Foto profil harus berupa berkas berjenis JPG / JPEG / PNG'
                ], 400);
            }

            if(!in_array($ext_photo_ktp, $conf_ext)){
                return response()->json([
                    'message' => 'Foto KTP harus berupa berkas berjenis JPG / JPEG / PNG'
                ], 400);
            }

            if(round(_getBase64Size($request->photo), 1) > $conf_size){
                return response()->json([
                    'message' => 'Ukuran foto profil tidak boleh lebih dari ' . $conf_size . ' MB'
                ], 400);
            }

            if(round(_getBase64Size($request->photo_ktp), 1) > $conf_size){
                return response()->json([
                    'message' => 'Ukuran foto KTP tidak boleh lebih dari ' . $conf_size . ' MB'
                ], 400);
            }

            $max_usr_profile = UserProfile::get();
            $lts_usr_profile = count($max_usr_profile);

            $wh_code = DB::table('rajaongkir_subdistrict as a')
            ->join('rajaongkir_city as b', 'a.city_id', '=', 'b.city_id')
            ->join('warehouse as c', 'b.warehouse_id', '=', 'c.id')
            ->where('a.subdistrict_id', '=', $request->subdistrict_id)
            ->select('c.code')
            ->get()[0]->code;

            // DB::transaction(function () use ($request, $dec_photo, $dec_photo_ktp, $ext_photo, $ext_photo_ktp, $wh_code, $lts_usr_profile){

            $photo         = 'assets/photo/' . _generateToken(40) . '.' . $ext_photo;
            file_put_contents($photo, $dec_photo);

            $photo_ktp     = 'assets/photo_ktp/' . _generateToken(40) . '.' . $ext_photo_ktp;
            file_put_contents($photo_ktp, $dec_photo_ktp);

            $user = User::create([
                'fullname' => $request->fullname,
                'email'    => $request->email,
                'role'     => 4,
                'active'   => 2
            ]);

            $user->created_by = $user->id;
            $user->updated_by = $user->id;
            $user->save();

            $user_code = $this->__generateUserCode($wh_code, $lts_usr_profile);

            $profile = UserProfile::create([
                'user_id'        => $user->id,
                'phone'          => $request->phone,
                'address'        => $request->address,
                'place_id'       => $request->place_id,
                'subdistrict_id' => $request->subdistrict_id,
                'photo'          => $photo,
                'photo_ktp'      => $photo_ktp,
                'created_by'     => $user->id,
                'updated_by'     => $user->id,
                'code'           => $user_code,
                'gender'         => $request->gender,
                'birth_date'     => $request->birth_date,
            ]);

            // });

            $user    = User::where('email', $request->email)->first();
            $profile = $user->profile;

            $data = [
                'type'  => 'register',
                'email' => $user->email,
                'name'  => $user->fullname,
                'otp'   => _generateOTP()
            ];

            $this->SendOTPCode($data);

            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran berhasil, cek alamat email Anda.',
                'data'    =>
                    [
                        compact('user', 'profile')
                    ]
            ], 200);

        }catch(Exception $e){

            DB::rollback();

            return response()->json([
                'message' => $e->getMessage(),
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    private function SendOTPCode($data)
    {
        try{

            dispatch(new SendEmailJob($data));

            OTPCode::where([
                'email' => $data['email'],
                'type'  => $data['type']
            ])->delete();

            $save = OTPCode::create([
                'email' => $data['email'],
                'otp'   => $data['otp'],
                'type'  => $data['type']
            ]);

            $save->expired_at = $save->created_at->addMinutes(4320);
            $save->save();

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }

    public function GetDataToken($email, $token)
    {
        try{

            $token = User::where(['email' => $email, 'token' => $token])
                        ->first();

            if(!$token){
                return response()->json([
                    'message' => 'Invalid token.'
                ], 401);
            }

            $role      = UserRole::find($token->role)->role;
            $warehouse = Warehouse::find($token->wh_id);

            if($warehouse){
                $warehouse = $warehouse->code . ' - ' . $warehouse->name . ' (' . $warehouse->short . ')';
            }

            return response()->json([
                'message'   => 'Get data berhasil.',
                'user'      => $token,
                'profile'   => $token->profile,
                'role'      => $role,
                'warehouse' => $warehouse
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => '[Middleware] ' . $e->getMessage()
            ], 500);

        }
    }


    private function emailVerification($data)
    {
        $data['type'] = 'verification';

        dispatch(new SendEmailJob($data));

        return 'Success';
    }


    public function resendVerification($email)
    {
        try{

            $user  = User::where('email', $email)->first();

            if(!$user){
                return response()->json([
                    'message' => "Email not registered."
                ], 404);
            }

            if($user->email_confirmed == 'N'){

                $this->emailVerification([
                    'email' => $email,
                    'link'  => url('users/verification') . '/' . Crypt::encryptString($email)
                ]);

            }

            echo "<script>window.close();</script>";

        }catch(Exception $e){

            return ____error([__FUNCTION__, $e->getMessage()]);

        }
    }

    public function checkVerification($token)
    {
        try{

            $email = Crypt::decryptString($token);
            $user  = User::where('email', $email)->first();

            if(!$user){
                return response()->json([
                    'status' => false,
                    'message' => "Email not registered."
                ], 404);
            }

            if($user->email_confirmed == 'N'){

                $user->email_confirmed = 'Y';
                $user->save();

                return redirect(env('WEB_URL') . '?title=Verified&message=Email berhasil diverifikasi.');

            }else{

                return redirect(env('WEB_URL'));

            }

        }catch(Exception $e){

            return ____error([__FUNCTION__, $e->getMessage()]);

        }
    }



	public function updateWeb(Request $request) {

		
		$rules = null;

		if($request->confirmnewpassword) {

			
			$rules = [
				// 'oldpassword'    => 'required|string|max:100',
				'password'    => 'required|string|max:100|regex:/^(?=.*\d)(?=.*[A-z])([^\s]){6,16}$/',
				'confirmnewpassword'    => 'required|string|max:100',
			];


		} else {
			
			$rules = [
				'fullname'    => 'required|string|min:4|max:100',
				'email2'		=> 'required|string|email'
			];

		}



        try{

        	$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
			} 

			
            if(array_key_exists('confirmnewpassword', $rules)) {

				$user = User::where('id', $request->id)->first();
				
                // if(Hash::check($request->oldpassword, $user->password)){

					if($request->password === $request->confirmnewpassword) {

						$user->password = Hash::make($request->confirmnewpassword);
						$user->save();

						return response()->json([
							'status'  => true,
							'message' => 'Password berhasil diperbaharui.',
						], 200);
					
					} else {

						return response()->json([
							'status'  => false,
							'message' => 'Konfirmasi Password tidak sesuai.',
						], 500);
					

					}

				// } else {
					
				// 	return response()->json([
				// 		'message' => 'Password lama tidak sesuai.',
				// 	], 500);
				
				// }

			}

			else {
				
				unset($request['by']);
				
				// # 1 cek email dulu

				$cekEmail = User::where('email', $request->email2)->first();

				if($cekEmail) {

					if($cekEmail->id !== (int)$request->id) {

						return response()->json([
							'status'  => false,
							'message' => 'Email sudah digunakan.',
						], 400);

                    } 
                    
                    else {


                        $user = User::find($request->id);
                        $user->email = $request->email2;
                        $user->fullname = $request->fullname;
                        $user->save();                        

						// return response()->json([
						// 	'status'  => false,
						// 	'message' => 'Email harus berbeda.',
                        // ], 400);
                        
                        return response()->json([
                            'message' => 'Data pengguna berhasil diperbarui.',
                            'data'    => $user,
                            'status'  => true,
                        ], 200);
            
	
					}

				} else {

                    // # Email yg gak sama


                    $user = User::find($request->id);
                    $user->email = $request->email2;
                    $user->fullname = $request->fullname;
                    $user->email_confirmed = 'N';
                    $user->save();


                    $this->emailVerification([
                        'email' => $user->email,
                        'link'  => url('users/verification') . '/' . Crypt::encryptString($user->email)
                    ]);
                    


                    return response()->json([
                        'message' => 'Data pengguna berhasil diperbarui. Email aktivasi sudah terkirim, harap aktivasi email anda',
                        'data'    => $user,
                        'status'  => true,
                    ], 200);
        


                }
				
			
			}


		}catch(Exception $e){

			return response()->json([
				'status'  => false,
				'message' => 'Terdapat kesalahan pada sistem internal.',
				'error'   => $e->getMessage()
			], 500);

		}

	}




}
