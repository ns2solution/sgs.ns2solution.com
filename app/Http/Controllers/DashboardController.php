<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function shoppingReport()
    {
        return view('dashboard.shopping-report');
    }

    public function brandReport()
    {
        return view('dashboard.brand-report');
    }

    public function perWhReport()
    {
        return view('dashboard.per-wh-report');
    }


    public function shoppingperWarReport() {
        return view('dashboard.shopping-per-warrior-report');
    }


    public function mutationWarReport() {
        return view('dashboard.mutation-warrior-report');
    }

    public function saldoWpReport() {
        return view('dashboard.saldo-wp-report');
    }


    public function users()
    {
        $user_role = _getDataJSON('user-role');
        $warehouse = _getDataJSON('warehouse');

        return view('management.users', [
            'user_role' => $user_role->data,
            'warehouse' => $warehouse->data
        ]);
    }

    public function buyers()
    {
        return view('master.buyer');
    }

    public function profile()
    {
        $place = _getDataJSON('place');

        return view('management.profile', ['place' => $place->data]);
    }

    public function category()
    {
        return view('master.category');
    }

    public function product()
    {
        return view('master.product');
    }


    public function product_point()
    {
        return view('master.product-point');
    }

    public function product_view()
    {
        return view('master.product_view');
    }

    public function top_product()
    {
        return view('master.product_top');
    }


    public function top_product_point()
    {
        return view('master.product_point_top');
    }

    public function buyers_view()
    {
        return view('master.buyer_view');
    }

    public function brand()
    {
        $principle = _getDataJSON('principle');

        return view('master.brand', ['principle' => $principle->data]);
    }

    public function tukar_poin()
    {
        return view('master.tukar_poin');
    }

    public function promosi()
    {
        return view('master.promotion');
    }

    public function warehouse()
    {
        $warehouse = _getDataJSON('warehouse');
        $provinsi  = _getDataJSON('provinsi');

        return view('master.warehouse', [
            'warehouse' => $warehouse->data,
            'provinsi'  => $provinsi->data
        ]);
    }

    public function account()
    {
        return view('dashboard.profil');
    }

    public function principle()
    {
        return view('master.principle');
    }


    public function transfer_wp()
    {
        return view('setting.transfer-wp');
    }
    

    public function point()
    {
        $buyer   = _getDataJSON('buyer');
        $setting = _getDataJSON('setting');

        return view('setting.point', [
            'buyer'   => $buyer->data,
            'setting' => $setting->data
        ]);
    }

    public function stock()
    {
        $warehouse = _getDataJSON('warehouse');

        return view('master.stock', ['warehouse' => $warehouse->data]);
    }


    public function stock_product_point()
    {
        $warehouse = _getDataJSON('warehouse');

        return view('master.stock-product-point', ['warehouse' => $warehouse->data]);
    }

    public function convertion()
    {
        $setting = _getDataJSON('setting');

        return view('setting.convertion', ['setting' => $setting->data]);
    }


    public function topup_wp()
    {
        return view('setting.topup-wp');
    }

    public function terms_condition(){
        return view('setting.terms-condition');
    }


    public function order()
    {
        $warehouse = _getDataJSON('warehouse');
        
        return view('order.order_list', ['warehouse' => $warehouse->data]);
    }

    public function order_point()
    {
        $warehouse = _getDataJSON('warehouse');
        
        return view('order.order_list_point', ['warehouse' => $warehouse->data]);
    }

    public function courier()
    {
        $warehouse = _getDataJSON('warehouse');

        return view('master.courier', ['warehouse' => $warehouse->data]);
    }

    public function alasan()
    {
        $warehouse = _getDataJSON('warehouse');

        return view('master.alasan', ['warehouse' => $warehouse->data]);
    }
}
