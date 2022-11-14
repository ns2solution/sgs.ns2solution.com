@extends('layouts.app')

@section('title', '| Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

<style>

.lazy-loading {
    position: relative;
    background-color: #FFF;
    border-radius: 6px;
    /* height: 500px; */
    overflow: hidden;
    /* width: 350px; */
    /* margin: 40px auto; */
    box-shadow: 0px 1px 13px -4px hsla(0, 0%, 0%, 0.09) !important;
    border: 1px solid #f1f1f1 !important;
    min-width: calc(25% - 30px);
    height: 335px;
    margin:0 15px 30px;
  }
  
.lazy-loading .shimmerBG {
-webkit-animation-duration: 1.2s;
        animation-duration: 1.2s;
-webkit-animation-fill-mode: forwards;
        animation-fill-mode: forwards;
-webkit-animation-iteration-count: infinite;
        animation-iteration-count: infinite;
-webkit-animation-name: shimmer;
        animation-name: shimmer;
-webkit-animation-timing-function: linear;
        animation-timing-function: linear;
background: #E5F0FB;
/* background: -webkit-gradient(linear, left top, right top, color-stop(4%, #c1dceb), color-stop(25%, #a3cbe1), color-stop(36%, #c1dceb)); */
/* background: linear-gradient(to right, #c1dceb 4%, #a3cbe1 25%, #c1dceb 36%); */
background:linear-gradient(to right, #d4d4d4 4%, #c7c7c7 25%, #e6e6e6 36%);
background-size: 1200px 100%;
}
@-webkit-keyframes shimmer {
0% {
    background-position: -100% 0;
}
100% {
    background-position: 100% 0;
}
}
@keyframes shimmer {
0% {
    background-position: -1200px 0;
}
100% {
    background-position: 1200px 0;
}
}
.lazy-loading .media {
height: 200px;
}
.lazy-loading .p-32 {
padding: 15px;
}
.lazy-loading .title-line {
height: 24px;
width: 100%;
margin-bottom: 12px;
border-radius: 20px;
}
.lazy-loading .content-line {
height: 8px;
width: 100%;
margin-bottom: 16px;
border-radius: 8px;
}
.lazy-loading .end {
width: 40%;
}

.m-t-24 {
margin-top: 24px;
}

.lazy-loading .media{
    height: 173px;
}

.test{
    border:1px solid red !important;
      
}

@media (max-width: 991.98px) {
    .lazy-loading-row .lazy-loading{
        -webkit-box-flex: 0;
        -ms-flex: 0 0 33.33%;
        flex: 0 0 33.33%;
        max-width:  calc(50% - 30px);
        margin: 0 15px 30px;
    }
 }

@media (max-width: 1199.98px) {
    .lazy-loading-row .lazy-loading{
        -webkit-box-flex: 0;
        -ms-flex: 0 0 33.33%;
        flex: 0 0 33.33%;
        max-width: calc(33.33% - 30px);
        margin: 0 15px 30px;
    }
}

</style>

<div class="container-fluid">

    <!-- <div class="row">
        <div class="col-12">
            <h4 class="mb-3 mb-md-0">Selamat Datang di Dashboard <c style="color:#2A8FCC;">{{ env('APP_NAME') }}</c></h4>
        </div>
    </div> -->

    <div class="row">

        <div class="col-12">
            <div class="row">
                @php
                    for ($x = 0; $x <= 6; $x++) {
                @endphp

                    <div class="col-12 mt-3 lazy-loading">
                        <div class="shimmerBG media"></div>
                        <div class="p-32">
                        <div class="shimmerBG title-line"></div>
                        <div class="shimmerBG title-line end"></div>

                        <div class="shimmerBG content-line m-t-24"></div>
                        <div class="shimmerBG content-line"></div>
                        <div class="shimmerBG content-line"></div>
                        <div class="shimmerBG content-line"></div>
                        <div class="shimmerBG content-line end"></div>
                        </div>
                </div>

                @php
                    }
                @endphp

            </div>
        </div>


        <div class="col-12">        
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Belanja</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="start-day-picker">
                                        <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-day" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="end-day-picker">
                                        <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-day" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-12 my-2">
                                    <div id="chart-report-belanja" style="widht:100%"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                            <table class="table table-hover mb-0" id="orderPerDay">
                                <thead>
                                <tr>
                                    <!-- <th class="pt-0">#</th> -->
                                    <th class="pt-0">No</th>
                                    <th class="pt-0">Tanggal</th>
                                    <th class="pt-0">ID Warrior</th>
                                    <th class="pt-0">Nama Warrior</th>
                                    <th class="pt-0">Warehouse</th>
                                    <th class="pt-0">Nominal Transaksi (Rp)</th>
                                    <th class="pt-0">Ongkir (Rp)</th>
                                    <th class="pt-0">Total Transaksi (Rp)</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

            <!-- <div class="row mt-3">
                <div class="col-12">
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Report Belanja Bulan-an</h6>
                                    </div>
                                    <hr>                    
                                    <div class="row justify-content-end">
                                        <div class="col-6 col-md-3">
                                            <div class="input-group date datepicker" id="start-month-picker">
                                                <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-month" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="input-group date datepicker" id="end-month-picker">
                                                <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-month" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="orderPerMonth">
                                        <thead>
                                        <tr>
                                            <th class="pt-0">No</th>
                                            <th class="pt-0">Bulan</th>
                                            <th class="pt-0">ID Warrior</th>
                                            <th class="pt-0">Nama Warrior</th>
                                            <th class="pt-0">Warehouse</th>
                                            <th class="pt-0">Nominal Transaksi (Rp)</th>
                                            <th class="pt-0">Ongkir (Rp)</th>
                                            <th class="pt-0">Total Transaksi (Rp)</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            
            <!-- <div class="row mt-3">
                <div class="col-12">
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Report Belanja Tahunan-an</h6>
                                    </div>
                                    <hr>                    
                                    <div class="row justify-content-end">
                                        <div class="col-6 col-md-3">
                                            <div class="input-group date datepicker" id="start-year-picker">
                                                <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-year" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="input-group date datepicker" id="end-year-picker">
                                                <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-year" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="orderPerYear">
                                        <thead>
                                        <tr>
                                            <th class="pt-0">No</th>
                                            <th class="pt-0">Tahun</th>
                                            <th class="pt-0">ID Warrior</th>
                                            <th class="pt-0">Nama Warrior</th>
                                            <th class="pt-0">Warehouse</th>
                                            <th class="pt-0">Nominal Transaksi (Rp)</th>
                                            <th class="pt-0">Ongkir (Rp)</th>
                                            <th class="pt-0">Total Transaksi (Rp)</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>


        <div class="row">
            <div class="col-12">
                <hr class="my-3">
            </div>
        </div>

        <div class="col-12 mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Brand (selama Transaksi)</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <!-- <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="start-day-brand-picker">
                                        <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-day-brand" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="end-day-brand-picker">
                                        <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-day-brand" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div> -->
                                <!-- <div class="col-4">
                                    <div class="row d-flex align-items-baseline">
                                        <div class="col">
                                            <select name="brand_id" id="brand-id" class="form-control">
						                    </select>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="col-12 my-2">
                                    <div id="chart-report-brand" style="widht:100%"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                            <table class="table table-hover mb-0" id="orderPerBrand">
                                <thead>
                                <tr>
                                    <th class="pt-0">No</th>
                                    <th class="pt-0">Tanggal</th>
                                    <th class="pt-0">Nama Brand</th>
                                    <th class="pt-0">SKU (Nama Produk)</th>
                                    <th class="pt-0">Quantity</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

           
        </div>



        <div class="row">
            <div class="col-12">
                <hr class="my-3">
            </div>
        </div>


        <div class="col-12 mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Per Cabang</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="start-day-trans-per-wh-picker">
                                        <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-day-trans-per-wh" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="end-day-trans-per-wh-picker">
                                        <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-day-trans-per-wh" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-12 my-2">
                                    <div id="chart-report-per-cabang" style="widht:100%"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                            <table class="table table-hover mb-0" id="orderPerTransWH">
                                <thead>
                                <tr>
                                    <!-- <th class="pt-0">#</th> -->
                                    <th class="pt-0">No</th>
                                    <th class="pt-0">Tanggal (YYYY-MM)</th>
                                    <th class="pt-0">Warehouse</th>
                                    <th class="pt-0">Total Warrior Transaksi</th>
                                    <th class="pt-0">Quantity</th>
                                    <th class="pt-0">Nominal Transaksi (Rp)</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

        </div>



        <div class="col-12 mt-4 d-none">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Belanja per warrior</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="start-day-warrior-picker">
                                        <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-day-warrior" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="end-day-warrior-picker">
                                        <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-day-warrior" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                            <table class="table table-hover mb-0" id="orderPerWarrior">
                                <thead>
                                <tr>
                                    <!-- <th class="pt-0">#</th> -->
                                    <th class="pt-0">No</th>
                                    <th class="pt-0">Tanggal</th>
                                    <th class="pt-0">Order ID</th>
                                    <th class="pt-0">WAREHOUSE</th>
                                    <th class="pt-0">SKU (Nama Produk)</th>
                                    <th class="pt-0">Quantity</th>
                                    <th class="pt-0">Harga</th>
                                    <th class="pt-0">Total</th>
                                    <th class="pt-0">ID WARRIROR</th>
                                    <th class="pt-0">NAMA WARRIROR</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

           
        </div>


        <div class="row">
            <div class="col-12">
                <hr class="my-3">
            </div>
        </div>

        <div class="col-12 mt-4 d-none">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Mutasi Warrior</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="start-day-mutasi-wp-picker">
                                        <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-day-mutasi-wp" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="input-group date datepicker" id="end-day-mutasi-wp-picker">
                                        <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-day-mutasi-wp" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="row d-flex align-items-baseline">
                                        <div class="col">
                                            <select name="warrior_id" id="warrior-id" class="form-control" style="width:100%">
						                    </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                            <table class="table table-hover mb-0" id="orderPerMutasiWP">
                                <thead>
                                <tr>
                                    <th class="pt-0">No</th>
                                    <th class="pt-0">Tanggal</th>
                                    <th class="pt-0">ID WARRIOR</th>
                                    <th class="pt-0">NAMA WARRIOR</th>
                                    <th class="pt-0">Saldo WP</th>
                                    <th class="pt-0">DEBIT WP</th>
                                    <th class="pt-0">KREDIT WP</th>
                                    <th class="pt-0">WAREHOUSE</th>
                                    <th class="pt-0">ID TRANSAKSI</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

           
        </div>


        <div class="row">
            <div class="col-12">
                <hr class="my-3">
            </div>
        </div>


        <div class="col-12 mt-4 d-none">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Saldo Warpay</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <div class="col-4">
                                    <div class="row d-flex align-items-baseline">
                                        <div class="col">
                                            <select name="warehouse_id" id="warehouse-id" class="form-control" style="width:100%">
						                    </select>
                                        </div>  -
                                    </div> 
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                            <table class="table table-hover mb-0" id="orderPerSaldoWP">
                                <thead>
                                <tr>
                                    <th class="pt-0">No</th>
                                    <th class="pt-0">ID WARRIOR</th>
                                    <th class="pt-0">NAMA WARRIOR</th>
                                    <th class="pt-0">WAREHOUSE</th>
                                    <th class="pt-0">Saldo</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

        </div>


    </div>

    
    <!-- <div class="row">        
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h6 class="card-title mb-0">Penjualan Perhari</h6>
                    <hr>                    
                    </div>
                    <hr>                    
                    <div id="chart_div" style="width:400; height:300"></div>
                </div> 
            </div>
        </div>
    </div> -->


@endsection


@section('js')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script>
$(document).ready(async function() {

    let PAGE = {
        isActive:false
    }
  
    const TYPE = {
        DAY: 'day',
        MONTH: 'month',
        YEAR: 'year',
        WARRIOR: 'warrior',
        MUTASI_WP: 'mutasi-wp',
        SALDO_WP:'saldo-wp',
        BRAND: 'brand',
        TRANS_PER_WH: 'transaction-per-warehouse'
    }
    
    $.fn.dataTable.ext.errMode = 'none';

    $('select').select2();

    google.charts.load('current', {'packages':['corechart']});

    google.charts.setOnLoadCallback(
        () => {
            chartReportBelanja();
            chartReportBrand();
            chartReportPerWH();
        }
    );

    const optGraph = {
            fontName: 'Arial',
            height: 300,
            fontSize: 12,
            animation: {
                duration: 600,
                easing: "out",
                startup: true
            },
            chartArea: {
                left: '10%',
                width: '100%',
                height: 260
            },
            backgroundColor: 'transparent',
            tooltip: {
                textStyle: {
                    fontName: 'Arial',
                    fontSize: 13
                },
                isHtml: true
            },
            vAxis: {
                title: 'Rata - Rata',
                titleTextStyle: {
                    fontSize: 12,
                    italic: false,
                    color: '#333'
                },
                textStyle: {
                    color: '#333'
                },
                baselineColor: '#ccc',
                gridlines:{
                    color: '#eee',
                    count: 10
                },
                minValue: 0,
                maxValue: 5.0,
                format: '#.##'
            },
            hAxis: {
                textStyle: {
                    color: '#333'
                }
            },
            legend: {
                position: 'top',
                alignment: 'center',
                textStyle: {
                    color: '#333'
                }
            },
    };

        
    async function chartReportBelanja() {

        try {
            
            let report  = await(await(await fetch(`{{ env('API_URL') . '/dashboard/${TYPE.DAY}' }}`, customPost({start_date:$('#start-day').val(), end_date:$('#end-day').val()}))).json()).data

            const wh = [];

            for (const item of report) {
                wh.push([item.created_at, item.wh_name, item.sum_final_total]);
            }

            let header = [];

            for (let a = 0; a < wh.length; a++) {

                for (let b = 0; b < wh[a].length; b++) {
                        header.push(wh[a][1]);
                }

            }
            
            header = [...new Set(header)];
            header.unshift('Tanggal');

            let elm = [];

            for (const item of report) {

                for (const item2 of header) {

                    if(item2 !== 'Tanggal') {

                        if(item.wh_name == item2) {
                            elm.push(parseInt(item.sum_final_total))
                        } else {
                            elm.push(0);
                        }

                    } else {
                        elm.push(item.created_at)
                    }

                }

            }

            DB = [];
            DB.push(header);

            for(const item of elm.chunk_inefficient(header.length)) {
                DB.push(item)
            } 


            let data = google.visualization.arrayToDataTable(DB);

            let chart = new google.visualization.LineChart(__getId('chart-report-belanja'));

            google.visualization.events.addListener(chart, 'error', errReportBelanja);    

            chart.draw(data, optGraph);

        } catch (e) {
            log(e)
        }
        
    }


    async function chartReportBrand() {
        try {
            
            let report  = await(await(await fetch(`{{ env('API_URL') . '/dashboard/${TYPE.BRAND}' }}`, __propsPOST)).json()).data
        
            const DB = [];

            const header = [['Brand', 'Quantity', { role: "style" } ]];

            for (const item of report) {

                let brand_name   = item.brand_name;
                let quantity     = parseInt(item.quantity)
                let random_color = Math.floor(Math.random()*16777215).toString(16);

                header.push([`${brand_name}`, quantity, `#${random_color}`]);
            }

            for (const item of header) {
                DB.push(item);
            }


            let data = google.visualization.arrayToDataTable(DB);

            let view = new google.visualization.DataView(data);

            let chart = new google.visualization.ColumnChart(__getId('chart-report-brand'));

            google.visualization.events.addListener(chart, 'error', errReportBrand);    

            chart.draw(view, Object.assign({}, optGraph, {
                legend:'none'
            }));


        } catch(e) {
            
            log(e)
        
        }
    }

    async function chartReportPerWH() {

        try {
            
            let report  = await(await(await fetch(`{{ env('API_URL') . '/dashboard/${TYPE.TRANS_PER_WH}' }}`, customPost({start_date:$('#start-day-trans-per-wh').val(), end_date:$('#end-day-trans-per-wh').val()}))).json()).data

            const wh = [];

            for (const item of report) {
                wh.push([item.created_at, item.wh_name, item.nominal_transaksi]);
            }

            // log(wh);

            let header = [];

            for (let a = 0; a < wh.length; a++) {

                for (let b = 0; b < wh[a].length; b++) {
                        header.push(wh[a][1]);
                }

            }
            

            header = [...new Set(header)];
            header.unshift('Tanggal');

            let elm = [];

            for (const item of report) {

                for (const item2 of header) {

                    if(item2 !== 'Tanggal') {

                        if(item.wh_name == item2) {
                            elm.push(parseInt(item.nominal_transaksi))
                        } else {
                            elm.push(0);
                        }

                    } else {
                        elm.push(item.created_at)
                    }

                }

            }

            DB = [];
            DB.push(header);

            for(const item of elm.chunk_inefficient(header.length)) {
                DB.push(item)
            } 


            let data = google.visualization.arrayToDataTable(DB);

            let chart = new google.visualization.LineChart(__getId('chart-report-per-cabang'));

            google.visualization.events.addListener(chart, 'error', errReportPerWH);    

            chart.draw(data, optGraph);

        } catch (e) {
            log(e)
        }
        
    }


    const managePage = () => {
        if(PAGE.isActive) {

            for(const elm of __querySelectorAll('.d-none')) {
                elm.classList.replace('d-none', 'd-block');
            }
            for(const elm of __querySelectorAll('.lazy-loading')) {
                elm.classList.add('d-none');
            }                

        } 
    }


    function errReportBelanja(errorMessage) {
        google.visualization.errors.removeError(errorMessage.id);

        __getId('chart-report-belanja').innerHTML = `
            <div class="container">
                <div class="col-12 d-flex justify-content-center">
                    <img width="35%" src="https://image.freepik.com/free-vector/search-engine-marketing-business-copywriting-service-content-management_335657-3148.jpg">  
                </div>
            </div>
            `
    }

    
    function errReportPerWH(errorMessage) {
        google.visualization.errors.removeError(errorMessage.id);

        __getId('chart-report-per-cabang').innerHTML = `
            <div class="container">
                <div class="col-12 d-flex justify-content-center">
                    <img width="35%" src="https://image.freepik.com/free-vector/search-engine-marketing-business-copywriting-service-content-management_335657-3148.jpg">  
                </div>
            </div>
            `
    }

    function errReportBrand(errorMessage) {
        google.visualization.errors.removeError(errorMessage.id);

        __getId('chart-report-brand').innerHTML = `
            <div class="container">
                <div class="col-12 d-flex justify-content-center">
                    <img width="35%" src="https://image.freepik.com/free-vector/search-engine-marketing-business-copywriting-service-content-management_335657-3148.jpg">  
                </div>
            </div>
            `
    }

    // Setup - add a text input to each footer cell
    /*
    $('#dataTable1 thead tr').clone(true).appendTo( '#dataTable1 thead' );
    
    $('#dataTable1 thead tr:eq(1) th').each( function (i) {
        var title = $(this).text();
        
        $(this).html( '<input type="text" class="form-control form-control-sm mt-2" name='+title+' placeholder="Search '+title+'" id='+title.split(' ').join('-').toLocaleLowerCase()+'>' );

    } );
    */

    function customPost(data){
        return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data))})
    }

    const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
            by : "{{ Session::get('user')->id }}"
        }
    }


    let warriors  = await(await(await fetch(`{{ env('API_URL') . '/buyers' }}`, __propsPOST)).json()).data
    let warehouse  = await(await(await fetch(`{{ env('API_URL') . '/warehouses' }}`, __propsPOST)).json()).data

    let elm_warrior_id      = __getId('warrior-id');
    let elm_warehouse_id    = __getId('warehouse-id');
  

    const propertyDB = {
        scrollX: true,
        pageLength: 5,
        processing: true,
        bLengthChange:false,
        search:false,
        bFilter:true,
        serverSide: true,
        orderCellsTop: true,
        fixedHeader: true,
    }

    var day, warrior, mutasi_wp, brand, saldo_wp, trans_per_wh;

    function Datatables(type, obj_db = null, id_warrior = null, warrior_name = null, warehouse_name = null, nominal_transaksi = null, total_transaksi = null, ongkir = null) {
    
        if(type == TYPE.DAY) {

           day =  $('#orderPerDay').DataTable({
                    ...propertyDB,
                    order: [[1, 'DESC']],
                    // rowsGroup: [2, 3, 4],
                    ajax:  {
                        url: `{{ env('API_URL') . '/dashboard/data-table/${TYPE.DAY}' }}`,
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            id_warrior : id_warrior,
                            warrior_name : warrior_name,
                            warehouse_name : warehouse_name,
                            nominal_transaksi : nominal_transaksi,
                            total_transaksi : total_transaksi,
                            start_date: $('#start-day').val(),
                            end_date : $('#end-day').val(),
                            ongkir : ongkir,
                        }
                    },
                    columns: [
                        // {
                        //     sClass: 'text-center details-control',
                        //     orderable: false,
                        //     render: function(){
                        //         return `
                        //             <button class="btn p-0a btn-default" type="button" id='edit-btn'>
                        //                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        //             </button>
                        //         `;
                        //     }
                        // },
                        {
                            data: 'no',
                            orderable: false,
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            sClass: 'text-center'
                        },
                        {
                            data: 'id_warrior',
                            sClass: 'text-center'
                        },
                        {
                            data: 'fullname',
                            sClass: 'text-left'
                        },
                        {
                            data: 'wh_name',
                        },
                        {
                            data: 'nominal_transaksi',
                            sClass: 'text-left'
                        },
                        {
                            data: 'total_ongkir',
                            sClass: 'text-left'
                        },
                        {
                            data: 'grand_total_per_user_per_wh',
                            width: '20%',
                            sClass: 'text-left'
                        },
                    ]
            });


        } else if(type == TYPE.MONTH) {

             return $('#orderPerMonth').DataTable({
                    pageLength: 6,
                    processing: true,
                    bLengthChange:false,
                    search:false,
                    bFilter:true,
                    serverSide: true,
                    orderCellsTop: true,
                    fixedHeader: true,
                    order: [[1, 'DESC']],
                    ajax:  {
                        url: "{{ env('API_URL') . '/dashboard/data-table/month' }}",
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            id_warrior : id_warrior,
                            warrior_name : warrior_name,
                            warehouse_name : warehouse_name,
                            nominal_transaksi : nominal_transaksi,
                            total_transaksi : total_transaksi,
                            ongkir : ongkir,
                            start_date: $('#start-month').val(),
                            end_date: $('#end-month').val(),
                        }
                    },
                    columns: [
                        {
                            data: 'no',
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            sClass: 'text-center'
                        },
                        {
                            data: 'id_warrior',
                            sClass: 'text-center'
                        },
                        {
                            data: 'fullname',
                            sClass: 'text-center'
                        },
                        {
                            data: 'wh_name',
                            width: '20%'
                        },
                        {
                            data: 'nominal_transaksi',
                            sClass: 'text-left'
                        },
                        {
                            data: 'total_ongkir',
                            sClass: 'text-left'
                        },
                        {
                            data: 'grand_total_per_user_per_wh',
                            width: '20%',
                            sClass: 'text-left'
                        },
                    ]
            });

        } else if(type == TYPE.YEAR) {

            return $('#orderPerYear').DataTable({
                    pageLength: 6,
                    processing: true,
                    bLengthChange:false,
                    search:false,
                    bFilter:true,
                    serverSide: true,
                    orderCellsTop: true,
                    fixedHeader: true,
                    order: [[1, 'DESC']],
                    ajax:  {
                        url: "{{ env('API_URL') . '/dashboard/data-table/year' }}",
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            id_warrior : id_warrior,
                            warrior_name : warrior_name,
                            warehouse_name : warehouse_name,
                            nominal_transaksi : nominal_transaksi,
                            total_transaksi : total_transaksi,
                            ongkir : ongkir,
                            start_date: $('#start-year').val(),
                            end_date: $('#end-year').val(),
                        }
                    },
                    columns: [
                        {
                            data: 'no',
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            sClass: 'text-center'
                        },
                        {
                            data: 'id_warrior',
                            sClass: 'text-center'
                        },
                        {
                            data: 'fullname',
                            sClass: 'text-center'
                        },
                        {
                            data: 'wh_name',
                            width: '20%'
                        },
                        {
                            data: 'nominal_transaksi',
                            sClass: 'text-left'
                        },
                        {
                            data: 'total_ongkir',
                            sClass: 'text-left'
                        },
                        {
                            data: 'grand_total_per_user_per_wh',
                            width: '20%',
                            sClass: 'text-left'
                        },
                    ]
            });

        } else if(type == TYPE.WARRIOR) {

            warrior = $('#orderPerWarrior').DataTable({
                    ...propertyDB,
                    order: [[1, 'DESC']],
                    ajax:  {
                        url: "{{ env('API_URL') . '/dashboard/data-table/warrior' }}",
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            start_date: $('#start-day-warrior').val(),
                            end_date : $('#end-day-warrior').val(),
                        }
                    },
                    // rowsGroup: [2, 3, 8, 9],
                    columns: [
                        {

                            data: 'no',
                            orderable: false,
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            sClass: 'text-center'
                        },
                        {
                            data: 'id_order',
                            sClass: 'text-center'
                        },
                        {
                            data: 'wh_name',
                            sClass: 'text-center'
                        },
                        {
                            render: function(_,__,___){
                                return `
                                    ${___.prod_number} (${___.prod_name})
                                `;
                            }
                        },
                        {
                            data: 'quantity',
                            sClass: 'text-center'
                        },
                        {
                            data: 'price',
                            sClass: 'text-left'
                        },
                        {
                            data: 'total_price',
                            width: '20%',
                            sClass: 'text-left'
                        },
                        {
                            data: 'id_warrior',
                            width: '20%',
                            sClass: 'text-left'
                        },

                        {
                            data: 'fullname',
                            width: '20%',
                            sClass: 'text-left'
                        },
                    ]
            });

        } else if(type == TYPE.MUTASI_WP) {

            mutasi_wp = $('#orderPerMutasiWP').DataTable({
                    ...propertyDB,
                    order: [[1, 'DESC']],
                    ajax:  {
                        url: `{{ env('API_URL') . '/dashboard/data-table/${TYPE.MUTASI_WP}' }}`,
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            id_warrior: elm_warrior_id.value,
                            start_date: $('#start-day-mutasi-wp').val(),
                            end_date : $('#end-day-mutasi-wp').val(),
                        }
                    },
                    columns: [
                        {

                            data: 'no',
                            orderable: false,
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            width: '30%',
                            sClass: 'text-center'
                        },
                        {
                            data: 'id_warrior',
                            sClass: 'text-center'
                        },
                        {
                            data: 'fullname',
                            width: '20%',
                            sClass: 'text-left'
                        },
                        {
                            data: 'warpay_user',
                            width: '20%',
                            sClass: 'text-center',
                            render: function(data){
                                return `
                                    <img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp;
                                    ${data}
                                `;
                            }
                        },
                        {
                            data: 'warpay_out',
                            sClass: 'text-center',
                           render: function(data){
                               if(data !== '-') {
                                return `
                                    <img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp;
                                    ${data}
                                `;
                               } else {
                                   return data;
                               }

                            }
                        },
                        {
                            data: 'warpay_in',
                            sClass: 'text-center',
                           render: function(data){
                            if(data !== '-') {
                                return `
                                    <img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp;
                                    ${data}
                                `;
                               } else {
                                   return data;
                               }
                            }
                        },
                        {
                            data: 'wh_name',
                            sClass: 'text-center'
                        },
                        {
                            data: 'id_transaksi',
                            sClass: 'text-center'
                        },
                    ],
                    // rowsGroup: [2, 3, 4],

            });


        } else if(type == TYPE.BRAND) {
            
            brand = $('#orderPerBrand').DataTable({
                    ...propertyDB,
                    order: [[1, 'DESC']],
                    ajax:  {
                        url: `{{ env('API_URL') . '/dashboard/data-table/${TYPE.BRAND}' }}`,
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            start_date: $('#start-day-brand').val(),
                            end_date : $('#end-day-brand').val(),
                        }
                    },
                    columns: [
                        {

                            data: 'no',
                            orderable: false,
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            sClass: 'text-center'
                        },
                        {
                            render: function(_,__,___){
                                // log(___)
                                return `
                                    <img draggable="false" src="${___.brand_logo}" class="mr-1" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';"> ${___.brand_name}
                                `;
                            }
                        },
                        {
                            width:'40%',
                            render: function(_,__,___){
                                return `
                                    ${___.prod_number} (${___.prod_name})
                                `;
                            }
                        },
                        {
                            data: 'quantity',
                            sClass: 'text-center'
                        },
                    ]
            });


        } else if(type == TYPE.SALDO_WP) {

            saldo_wp = $('#orderPerSaldoWP').DataTable({
                    ...propertyDB,
                    order: [[4, 'DESC']],
                    ajax:  {
                        url: `{{ env('API_URL') . '/dashboard/data-table/${TYPE.SALDO_WP}' }}`,
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            warehouse_id: elm_warehouse_id.value,
                        }
                    },
                    columns: [
                        {

                            data: 'no',
                            orderable: false,
                            sClass: 'text-center',
                        },
                        {
                            data: 'id_warrior',
                            sClass: 'text-center'
                        },
                        {
                            data: 'fullname',
                            width: '20%',
                            sClass: 'text-left'
                        },
                        {
                            data: 'wh_name',
                            sClass: 'text-center'
                        },
                        {
                            data: 'warpay_user',
                            width: '60%',
                            sClass: 'text-center',
                            render: function(data){
                                return `
                                    <img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp;
                                    ${data}
                                `;
                            }
                        },
                    ],
                    // rowsGroup: [2, 3, 4],

            });

        } else if(type == TYPE.TRANS_PER_WH) {
            
            trans_per_wh =  $('#orderPerTransWH').DataTable({
                    ...propertyDB,
                    order: [[1, 'DESC']],
                    // rowsGroup: [2, 3, 4],
                    ajax:  {
                        url: `{{ env('API_URL') . '/dashboard/data-table/${TYPE.TRANS_PER_WH}' }}`,
                        dataType: 'JSON',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            token : "{{ Session::get('token') }}",
                            email : "{{ Session::get('email') }}",
                            start_date: $('#start-day-trans-per-wh').val(),
                            end_date : $('#end-day-trans-per-wh').val(),
                        }
                    },
                    columns: [
                        {
                            data: 'no',
                            orderable: false,
                            sClass: 'text-center',
                        },
                        {
                            data: 'created_at',
                            sClass: 'text-center'
                        },
                        {
                            data: 'wh_name',
                            width: '20%',
                        },
                        {
                            data: 'ttl_warrior_transaksi',
                            sClass: 'text-center'
                        },
                        {
                            data: 'quantity',
                        },
                        {
                            data: 'nominal_transaksi',
                            width: '40%',
                            sClass: 'text-left'
                        },
                    ]
            });
  
        }
        
    }
    

    /* -------------------------- Init Multi Datatable -------------------------- */

    const RANGE_TIME = [TYPE.DAY, TYPE.WARRIOR, TYPE.MUTASI_WP, TYPE.BRAND, TYPE.SALDO_WP, TYPE.TRANS_PER_WH];
    
    for (const time of RANGE_TIME) {
        Datatables(time);
    }


    /* ------------------------ Append Data  to Select ----------------------- */
    
    elm_warrior_id.innerHTML    = elm_choose('Pilih Warrior')
    elm_warehouse_id.innerHTML  = elm_choose('Pilih Warehouse');

    for (const data of warriors) {
        let newOption = ___createOpt(data.id, `${data.id} - ${data.fullname}`);
        elm_warrior_id.appendChild(newOption);
    }

    for (const data of warehouse) {
        let newOption = ___createOpt(data.id, `${data.short} - ${data.name}`);
        elm_warehouse_id.appendChild(newOption);
    }


    /* ----------------------------- Event Listener ----------------------------- */

    eventListener(elm_warrior_id.id, evWarrior);
    $('#warrior-id').on('select2:select', evWarrior);

    eventListener(elm_warehouse_id.id, evWarehouse);
    $('#warehouse-id').on('select2:select', evWarehouse);

    function evWarrior(e) {
        e.preventDefault();
        $('#orderPerMutasiWP').DataTable().destroy();
        Datatables(TYPE.MUTASI_WP);
    }

    function evWarehouse(e) {
        e.preventDefault();
        $('#orderPerSaldoWP').DataTable().destroy();
        Datatables(TYPE.SALDO_WP);
    }



    function format(row) {
        return `
            <table style="width:100%;border:none;margin:-14px;" cellspacing="0" cellpadding="0">
                <tr>
                    <th width="10%">No Produk</th>
                    <th>Nama Produk</th>
                </tr>
                ${row}
            </table>
        `;
    }

    // $('#orderPerDay tbody').on('click', 'td.details-control', async function () {
    //     var tr = $(this).closest('tr');
    //     var row = day.row( tr );

    //     const id_order = row.data().id_order;

    //     if ( row.child.isShown() ) {
    //         row.child.hide();
    //         tr.removeClass('shown');
    //     }
    //     else {

    //         const order_item = await(await(await fetch(`{{ env('API_URL') . '/order-item/by-order-id' }}/${id_order}`, __propsPOST)).json()).data;

    //         let products          = '';

    //         for (const item of order_item.order_items) {
    //             products += `
    //                 <tr>
    //                     <td>${item.prod_number}</td>
    //                     <td>${item.prod_name}</td>
    //                 </tr>
    //             `;
    //         }

    //         row.child( format(products) ).show();
    //         tr.addClass('shown');
    //     }
    // } );
    



    // DAY

    if($('#start-day-picker').length) {

        $('#start-day-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#start-day").val(formatedValue)
                $('#orderPerDay').DataTable().destroy();
                Datatables(TYPE.DAY);
                chartReportBelanja();
            }
        });

        $("#start-day").on('keyup', function() {
            $('#orderPerDay').DataTable().destroy();
            Datatables(TYPE.DAY);
            chartReportBelanja();
        })

    }


    if($('#end-day-picker').length) {

        $('#end-day-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#end-day").val(formatedValue)
                $('#orderPerDay').DataTable().destroy();
                Datatables(TYPE.DAY);
                chartReportBelanja();
            }
        });

        $("#end-day").on('keyup', function() {
            $('#orderPerDay').DataTable().destroy();
            Datatables(TYPE.DAY);
            chartReportBelanja();
        })

    }

    // MONTH

    if($('#start-month-picker').length) {

        $('#start-month-picker').datetimepicker({
            format: 'YYYY-MM',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM');
                $("#start-month").val(formatedValue)
                $('#orderPerMonth').DataTable().destroy();
                Datatables('month');
            }
        });

        $("#start-month").on('keyup', function() {
            $('#orderPerMonth').DataTable().destroy();
            Datatables('month');
        })

    }


    if($('#end-month-picker').length) {

        $('#end-month-picker').datetimepicker({
            format: 'YYYY-MM',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM');
                $("#end-month").val(formatedValue)
                $('#orderPerMonth').DataTable().destroy();
                Datatables('month');
            }
        });

        $("#end-month").on('keyup', function() {
            $('#orderPerMonth').DataTable().destroy();
            Datatables('month');
        })

    }


    // YEAR
    if($('#start-year-picker').length) {

        $('#start-year-picker').datetimepicker({
            format: 'YYYY',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY');
                $("#start-year").val(formatedValue)
                $('#orderPerYear').DataTable().destroy();
                Datatables('year');
            }
        });

        $("#start-year").on('keyup', function() {
            $('#orderPerYear').DataTable().destroy();
            Datatables('year');
        })

    }


    if($('#end-year-picker').length) {

        $('#end-year-picker').datetimepicker({
            format: 'YYYY',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY');
                $("#end-year").val(formatedValue)
                $('#orderPerYear').DataTable().destroy();
                Datatables('year');
            }
        });

        $("#end-year").on('keyup', function() {
            $('#orderPerYear').DataTable().destroy();
            Datatables('year');
        })

    }


    // WARRIOR
    if($('#start-day-warrior-picker').length) {

        $('#start-day-warrior-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#start-day-warrior").val(formatedValue)
                $('#orderPerWarrior').DataTable().destroy();
                Datatables(TYPE.WARRIOR);
            }
        });

        $("#start-day-warrior").on('keyup', function() {
            $('#orderPerWarrior').DataTable().destroy();
            Datatables(TYPE.WARRIOR);
        })

    }


    if($('#end-day-warrior-picker').length) {

        $('#end-day-warrior-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#end-day-warrior").val(formatedValue)
                $('#orderPerWarrior').DataTable().destroy();
                Datatables(TYPE.WARRIOR);
            }
        });

        $("#end-day-warrior").on('keyup', function() {
            $('#orderPerWarrior').DataTable().destroy();
            Datatables(TYPE.WARRIOR);
        })

    }


    // MUTASI WP
    if($('#start-day-mutasi-wp-picker').length) {

        $('#start-day-mutasi-wp-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#start-day-mutasi-wp").val(formatedValue)
                $('#orderPerMutasiWP').DataTable().destroy();
                Datatables(TYPE.MUTASI_WP);
            }
        });

        $("#start-day-mutasi-wp").on('keyup', function() {
            $('#orderPerMutasiWP').DataTable().destroy();
            Datatables(TYPE.MUTASI_WP);
        })

    }


    if($('#end-day-mutasi-wp-picker').length) {

        $('#end-day-mutasi-wp-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#end-day-mutasi-wp").val(formatedValue)
                $('#orderPerMutasiWP').DataTable().destroy();
                Datatables(TYPE.MUTASI_WP);
            }
        });

        $("#end-day-mutasi-wp").on('keyup', function() {
            $('#orderPerMutasiWP').DataTable().destroy();
            Datatables(TYPE.MUTASI_WP);
        })

    }


    // PER WH

    // DAY
    if($('#start-day-trans-per-wh-picker').length) {

        $('#start-day-trans-per-wh-picker').datetimepicker({
            format: 'YYYY-MM',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM');
                const date    = new Date(formatedValue);
                const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDate();
                $("#start-day-trans-per-wh").val(`${formatedValue}-${firstDay}`)
                $('#orderPerTransWH').DataTable().destroy();
                Datatables(TYPE.TRANS_PER_WH);
                chartReportPerWH()
            }
        });

        $("#start-day-trans-per-wh").on('keyup', function() {
            $('#orderPerTransWH').DataTable().destroy();
            Datatables(TYPE.TRANS_PER_WH);
            chartReportPerWH()
        })

    }


    if($('#end-day-trans-per-wh-picker').length) {

        $('#end-day-trans-per-wh-picker').datetimepicker({
            format: 'YYYY-MM',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM');
                const date    = new Date(formatedValue);
                const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                $("#end-day-trans-per-wh").val(`${formatedValue}-${lastDay}`)
                $('#orderPerTransWH').DataTable().destroy();
                Datatables(TYPE.TRANS_PER_WH);
                chartReportPerWH()
            }
        });

        $("#end-day-trans-per-wh").on('keyup', function() {
            $('#orderPerTransWH').DataTable().destroy();
            Datatables(TYPE.TRANS_PER_WH);
            chartReportPerWH()
        })

    }



    // var id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir;

    // $(document).on('keyup', '#id-warrior', function(){
    //     id_warrior = $(this).val();
    //     $('#dataTable1').DataTable().destroy();

    //     if(id_warrior) {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     } else {
    //         Datatables(null, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     }
    // })

    // $(document).on('keyup', '#nama-warrior', function(){
    //     nama_warrior = $(this).val();
    //     $('#dataTable1').DataTable().destroy();

    //     if(nama_warrior) {
    //         log(id_warrior, 'di nama warrior')
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     } else {
    //         Datatables(id_warrior, null, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     }
    // })


    // $(document).on('keyup', '#warehouse', function(){
    //     warehouse = $(this).val();
    //     $('#dataTable1').DataTable().destroy();

    //     if(warehouse) {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     } else {
    //         Datatables(id_warrior, nama_warrior, null, nominal_transaksi, total_transaksi, ongkir);
    //     }
    // })


    // $(document).on('keyup', '#nominal-transaksi', function(){
    //     nominal_transaksi = $(this).val();
    //     $('#dataTable1').DataTable().destroy();

    //     if(nominal_transaksi) {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     } else {
    //         Datatables(id_warrior, nama_warrior, warehouse, null, total_transaksi, ongkir);
    //     }
    // })


    // $(document).on('keyup', '#total-transaksi', function(){
    //     total_transaksi = $(this).val();
    //     $('#dataTable1').DataTable().destroy();

    //     if(total_transaksi) {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi);
    //     } else {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, null, ongkir);
    //     }
    // })



    // $(document).on('keyup', '#ongkir', function(){
    //     ongkir = $(this).val();
    //     $('#dataTable1').DataTable().destroy();

    //     if(ongkir) {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, ongkir);
    //     } else {
    //         Datatables(id_warrior, nama_warrior, warehouse, nominal_transaksi, total_transaksi, null);
    //     }
    // })


    PAGE.isActive = true;
    managePage();

    })

</script>

@endsection