<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use DB;
use Exception;
use Validator;

class RajaOngkirCommand extends Command
{
    protected $signature   = 'rajaongkir';
    protected $description = 'Raja Ongkir Cron';

    public function __construct()
    {
        parent::__construct();

        $this->URL = _con('RAJAONGKIR-URL');
        $this->KEY = _con('RAJAONGKIR-KEY');
        $this->TBL = [
            'province'    => 'rajaongkir_province',
            // 'city'        => 'rajaongkir_city',
            'subdistrict' => 'rajaongkir_subdistrict'
        ];
    }

    public function handle()
    {
        $this->p('---------------- RAJA ONGKIR ----------------');
        $this->p('');

        DB::beginTransaction();

        $this->p('DB: Begin Transaction');
        $this->p('');

        try{

            // 1
            $this->deleteData();
            $this->p('Truncate all table rajaongkir.', 'success');

            // 2
            $PROVINCE = $this->dataGET('api/province');
            $this->insertData('province', $PROVINCE);
            $this->p('Get data province.', 'success');

            // 3
            $CITY = $this->dataGET('api/city');
            // $this->insertData('city', $CITY);
            $this->p('Get data city.', 'success');

            // 4
            $this->p('Get data subdistrict.', 'process');
            $progressBar = $this->output->createProgressBar(count($CITY['rajaongkir']['results']));
            $progressBar->start();

            // 5
            foreach($CITY['rajaongkir']['results'] as $a){
                $SUBDISTRICT = $this->dataGET('api/subdistrict?city=' . $a['city_id']);
                $this->insertData('subdistrict', $SUBDISTRICT);
                $progressBar->advance();
            }
            $this->info("");
            $this->p('Get data subdistrict.', 'success');

            $progressBar->finish();
            DB::commit();

            $this->p('');
            $this->p('DB: Commit.');

        }catch(Exception $e){

            DB::rollback();

            $this->p('');
            $this->p('DB: Rollback.');
            $this->p('');
            $this->p('Terdapat kesalahan pada sistem internal.', 'error');
            $this->p($e->getMessage(), 'error');
            
        }

        $this->p('');
        $this->p('----------------------------------------------');
    }

    private function insertData($type, $data)
    {
        $data = $data['rajaongkir']['results'];

        DB::table($this->TBL[$type])->insert($data);

        return 'OK';
    }

    private function deleteData()
    {
        foreach($this->TBL as $a){

            DB::table($a)->delete();

        }

        return 'OK';
    }

    private function dataGET($url)
    {
        $response = Http::withHeaders([

            'key' => $this->KEY

        ])->get($this->URL . $url);

        $this->checkError($response);

        return $response;
    }

    private function checkError($var)
    {
        $var = $var['rajaongkir'];

        if($var['status']['code'] !== 200){

            throw new Exception($var['status']['description']);

        }
    }

    private function p($msg, $type = null)
    {
        switch($type){

            case 'success':
                $msg = "Success: <fg=white>" . $msg . "</>";
                break;

            case 'error':
                $msg = "<fg=red>Error:</> <fg=white>" . $msg . "</>";
                break;

            case 'process':
                $msg = "<fg=blue>Processing:</> <fg=white>" . $msg . "...</>";

            default:
                $msg = $msg;

        }

        return $this->info("> " . $msg);
    }
}
