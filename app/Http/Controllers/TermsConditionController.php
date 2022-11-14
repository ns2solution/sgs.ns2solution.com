<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\TermsCondition;
use Exception;
use Carbon\Carbon;

class TermsConditionController extends Controller
{
    public function get(Request $req) {

        $type = $req->type;

        try {
            
            $terms = TermsCondition::first(); 

            if($type == 'WEB') {

                return __jsonResp(true, 'Data Berhasil diambil', 200, null, $terms);
            
            } else {
                
                // $new = json_encode($terms->content, JSON_UNESCAPED_SLASHES);
                $time = date('d/m/Y', strtotime($terms->updated_at));

                // $terms->content = str_replace('"', '', $new);
                $terms->time = $time;

                return __jsonResp(true, 'Data Berhasil diambil', 200, null, $terms);
            }

       
        } catch (Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);
        }

    }

    public function save(Request $req) {

        try{

            $id = $req->id;

            if($id) {

                $terms = TermsCondition::find($id);
                $terms->content =  $req->content;
                $terms->save();

            } else {

                $term = TermsCondition::create($req->all());

            }

            return __jsonResp(true, 'Data Berhasil disimpan', 200, null, $terms);

        }catch(Exception $e) {

            return __jsonResp(false, $e->getMessage(), 500, $e);
        }

    }
}
