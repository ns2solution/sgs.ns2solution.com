<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Exception;
use Validator;
use Illuminate\Support\Facades\DB;

use App\User;
use App\UserAddress;
use App\UserProfile;

class UserAddressController extends Controller
{
	public function get($id = null, Request $request)
	{
		try{

			$user    = User::where('email', $request->email)->first();
            $profile = UserProfile::where('user_id', $user->id)->first();
            $subdistrict = DB::table('rajaongkir_subdistrict')->where('subdistrict_id', $profile->subdistrict_id)->first();
            
            $data = [];

            $default = [
                'id'                => 0,
                'user_id'           => $user->id,
                'address_label'     => $profile->usr_addr_address_label ? $profile->usr_addr_address_label : 'Alamat Utama',
                'address'           => $profile->usr_addr_address ? $profile->usr_addr_address : $profile->address,
                'subdistrict_id'    => $profile->usr_addr_subdistrict_id ? $profile->usr_addr_subdistrict->subdistrict_id : $subdistrict->subdistrict_id,
                'province_id'       => $profile->usr_addr_subdistrict_id ? $profile->usr_addr_subdistrict->province_id : $subdistrict->province_id,
                'city_id'           => $profile->usr_addr_subdistrict_id ? $profile->usr_addr_subdistrict->city_id : $subdistrict->city_id,
                'postal_code'       => $profile->usr_addr_postal_code ? $profile->usr_addr_postal_code : $profile->postal_code,
                'receiver_name'     => $profile->usr_addr_receiver_name ? $profile->usr_addr_receiver_name : $user->fullname,
                'receiver_phone'    => $profile->usr_addr_receiver_phone ? $profile->usr_addr_receiver_phone : $profile->phone,
                'primary'           => $profile->usr_addr_primary ? $profile->usr_addr_primary : 1
            ];

            array_push($data, $default);

            $address = UserAddress::select(
                'id', 'user_id', 'address_label',
                'address', 'subdistrict_id', 'postal_code',
                'receiver_name', 'receiver_phone', 'primary'
            )
            ->where('user_id', $user->id)
            ->get();

            foreach($address as $index => $a){

                $a->province_id = $a->subdistrict->province_id;
                $a->city_id     = $a->subdistrict->city_id;

                unset($a->subdistrict);

                array_push($data, $a);

                if($a->primary == 1){
                    $data[0]['primary'] = 0;
                }

            }

            if($id !== null){
                foreach($data as $a){

                    if($a['id'] == $id){
                        return response()->json([
                            'message' => 'Data berhasil diambil.',
                            'data'    => $a
                        ], 200);
                    }

                }

                return response()->json([
                    'message' => 'ID alamat tidak ditemukan.'
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

    public function createUpdate($id = null, Request $request)
    {
    	DB::beginTransaction();

    	$rules = [
            'address_label'  => 'required|max:20',
            'address'		 => 'required|min:10|max:255',
            'subdistrict_id' => 'required|regex:/^\S*$/u',
            'postal_code'	 => 'required|regex:/^\S*$/u',
            'receiver_name'	 => 'required|string|min:4|max:100',
            'receiver_phone' => 'required|min:8|max:13|regex:/^\S*$/u',
            'primary'		 => 'required|regex:/^\S*$/u'
        ];

    	try{

    		$validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => ucfirst($validator->errors()->first())
                ], 400);
            }

            if(substr($request->receiver_phone, 0, 1) != '8'){
                return response()->json([
                    'message' => 'No HP harus berupa nomor yang valid.'
                ], 400);
            }

            if($request->primary != '0' && $request->primary != '1'){
            	return response()->json([
                    'message' => 'Primary harus berupa value yang valid.'
                ], 400);
            }

            if($id && $id != 0){
            	$check = UserAddress::find($id);
            	if(!$check){
            		return response()->json([
	                    'message' => "ID alamat tidak ditemukan."
	                ], 404);
            	}
            }

            $user = User::where('email', $request->email)
    					->first();

            $data 			 = $request->except('token', 'email');
            $data['user_id'] = $user->id;

            if($request->primary == '1' && $id != 0){

            	UserAddress::where([
            		'user_id' => $data['user_id'],
            		'primary' => '1'
            	])->update([
            		'primary' => '0'
            	]);

            }

            if($id === null){

            	UserAddress::create($data);

            	$message = 'Alamat user berhasil ditambahkan.';

            }else if($id != 0) {

            	UserAddress::where([
            		'id'	=> $id
            	])->update(
            		$data
            	);

            	$message = 'Alamat user berhasil diperbarui.';

            } else {

                $profile = $user->profile;

                $profile->usr_addr_receiver_name            = $request->receiver_name;
                $profile->usr_addr_receiver_phone           = $request->receiver_phone;
                $profile->usr_addr_primary                  = $request->primary;
                $profile->usr_addr_subdistrict_id           = $request->subdistrict_id;
                $profile->usr_addr_address                  = $request->address;
                $profile->usr_addr_address_label            = $request->address_label;
                $profile->usr_addr_postal_code              = $request->postal_code;
                
                $profile->save();

                $message = 'Alamat user berhasil diperbarui.';
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'data'    => $data
            ], 200);

    	}catch(Exception $e){

    		DB::rollback();

    		return response()->json([
    			'message' => 'Terdapat kesalahan pada sistem internal.',
    			'error'   => $e->getMessage()
    		], 500);

    	}
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {

            $address = UserAddress::find($id);
            $address->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Alamat user berhasil dihapus.',
                'data'    => $address
            ], 200);

        } catch (Exception $e) {

    		DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => $e->getMessage()
            ], 500);

        }
    }
}
