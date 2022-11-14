@extends('layouts.app')

@section('title', '| Report Brand')
@section('breadcrumb', 'Dashboard / Reports / Brand')

@section('content')

<div class="container-fluid">

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


        <button class="btn btn-success btn-icon-text px-3 px-lg-4 float3" style="right: 40px" id="export">
            &nbsp;<i class="link-icon" data-feather="plus-square"></i>
            Export Data Report Brand
        </button>


        <div class="col-12">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Brand (selama Transaksi)</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
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
                                    <th class="pt-0">Kode Principle</th>
                                    <th class="pt-0">Nama Produk</th>
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

    </div>
</div>



@endsection


@section('js')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script>

let PAGE = {
    isActive:false
}

$(document).ready(async function() {

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

    google.charts.load('50', {'packages':['corechart']});

    google.charts.setOnLoadCallback(
        () => {
            chartReportBrand();
        }
    );

    let elm_export = __getId('export');

elm_export.addEventListener('click', exportData);


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


async function exportData(e) {
    if(e) {
        e.preventDefault();
    }

    elm_export.innerHTML = 'Proses ' + ___iconLoading();
    elm_export.disabled = true;

    let data = new FormData();
    data.append('token', '{{ Session::get("token")}}')
    data.append('email', '{{ Session::get("email")}}')

    $.ajax({
        url:`{{ env('API_URL') . '/dashboard/export/${TYPE.BRAND}' }}`,
        method:"POST",
        data: data,
        dataType:'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success:function(result){
            const {status, message, data} = result;


            if(status) {

                toastr.success(message, { fadeAway: 10000 });
                elm_export.innerHTML = __iconPlus() + ' Export Data Report Brand';
                elm_export.disabled = false;

                window.open(data, '_blank');

            } else {
                elm_export.disabled = false;
                elm_export.innerHTML = __iconPlus() + ' Export Data Report Brand';

                log(message);
            }
        },
        error: function(err) {
            log(err);
            const msg = err.responseJSON.message;
            elm_export.disabled = false;
            toastr.error(msg,  { fadeAway: 10000 });

        }
    })


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

            let n = DB;
            debugger;

            let data = google.visualization.arrayToDataTable(DB);

            let view = new google.visualization.DataView(data);

            let chart = new google.visualization.ColumnChart(__getId('chart-report-brand'));

            google.visualization.events.addListener(chart, 'error', errReportBrand);    

            chart.draw(view, Object.assign({}, optGraph, {
                legend:'none',
                // height:260,
                chartArea: {
                    left: '10%',
                    width: '100%',
                    // height: 240
                },
                vAxis: {
                    title: 'Quantity',
                }
            }));


        } catch(e) {
            
            log(e)
        
        }
    }


    function errReportBrand(errorMessage) {

        debugger
        google.visualization.errors.removeError(errorMessage.id);

        __getId('chart-report-brand').innerHTML = `
            <div class="container">
                <div class="col-12 d-flex justify-content-center">
                    <img width="35%" src="https://image.freepik.com/free-vector/search-engine-marketing-business-copywriting-service-content-management_335657-3148.jpg">  
                </div>
            </div>
            `
    }


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

    function Datatables(type, obj_db = null, id_warrior = null, warrior_name = null, warehouse_name = null, nominal_transaksi = null, total_transaksi = null, ongkir = null) {
        
            $('#orderPerBrand').DataTable({
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
                            data: 'brand_name',
                        },
                        {
                            data: 'principle_kode',
                        },
                        {
                            data: 'prod_name',
                            width:'40%',
                        },
                        {
                            data: 'quantity',
                            sClass: 'text-center'
                        },
                    ]
            });       
        
    }
    

    /* -------------------------- Init Multi Datatable -------------------------- */
    Datatables();

  

    setTimeout(() => {
        PAGE.isActive = true;
        managePageDashboard();        
    });


    })

</script>

@endsection