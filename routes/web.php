<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'AuthController@Login')->name('login');
Route::get('UPT-SESSION/{data}', function($data) {
    
    $user = base64_decode($data);
    $user = json_decode($user);
    
    Session::put('user', $user);
    Session::put('email', $user->email);

    echo "<script>window.close();</script>";

});

Route::get('login/{token}', 'AuthController@SaveToken');
Route::get('logout', 'AuthController@Logout')->name('logout');

Route::group(['middleware' => 'sidebar'], function(){

    // Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    
    Route::prefix('dashboard')->group(function () {
        Route::get('shoppingreport', 'DashboardController@shoppingReport')->name('shoppingreport');
        Route::get('brandreport', 'DashboardController@brandReport')->name('brandreport');
        Route::get('perwhreport', 'DashboardController@perWhReport')->name('perwhreport');
        Route::get('shoppingperwarrreport', 'DashboardController@shoppingperWarReport')->name('shoppingperwarrreport');
        Route::get('mutationwarreport', 'DashboardController@mutationWarReport')->name('mutationwarreport');
        Route::get('saldowpreport', 'DashboardController@saldoWpReport')->name('saldowpreport');
    });


	Route::get('users', 'DashboardController@users')->name('users');
    Route::get('buyers', 'DashboardController@buyers')->name('buyers');
    Route::get('buyers-view', 'DashboardController@buyers_view')->name('buyers-view');
	Route::get('profile', 'DashboardController@profile')->name('profile');
	Route::get('category', 'DashboardController@category')->name('category');
    Route::get('top-product', 'DashboardController@top_product')->name('top-product');
    Route::get('products', 'DashboardController@product')->name('products');
    Route::get('product-view', 'DashboardController@product_view')->name('product-view');
    Route::get('topproductpoint', 'DashboardController@top_product_point')->name('topproductpoint');
    Route::get('product-point', 'DashboardController@product_point')->name('product-point');
	// Route::get('produk-poin', 'DashboardController@tukar_poin')->name('product-point');
	Route::get('warehouse', 'DashboardController@warehouse')->name('warehouse');
    Route::get('principle', 'DashboardController@principle')->name('principle');
    Route::get('brand', 'DashboardController@brand')->name('brand');
    Route::get('promosi', 'DashboardController@promosi')->name('promosi');
    Route::get('add-point', 'DashboardController@point')->name('add-point');
    Route::get('stocks', 'DashboardController@stock')->name('stocks');
    Route::get('stockproductpoint', 'DashboardController@stock_product_point')->name('stockproductpoint');
    Route::get('convertion', 'DashboardController@convertion')->name('convertion');
    Route::get('topup-wp', 'DashboardController@topup_wp')->name('topup-wp');
    Route::get('transfer-wp', 'DashboardController@transfer_wp')->name('transfer-wp');
    Route::get('terms-condition', 'DashboardController@terms_condition')->name('terms-condition');
    Route::get('order', 'DashboardController@order')->name('order');
    Route::get('courier', 'DashboardController@courier')->name('courier');
    Route::get('alasan', 'DashboardController@alasan')->name('alasan');
    Route::get('order-point', 'DashboardController@order_point')->name('order-point');


});

Route::get('my-profile', 'DashboardController@account')->name('my-profile');

Route::get('payment-gateway', function() {
    return view('pg');
});