@extends('layouts.app')

@section('title', '| Report Belanja')
@section('breadcrumb', 'Dashboard / Reports / Belanja')

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
            Export Data Report Belanja
        </button>


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
                                    <th class="pt-0">Kurir Service</th>
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

    google.charts.load('current', {'packages':['corechart']});

    google.charts.setOnLoadCallback(
        () => {
            chartReportBelanja();
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


            let data = google.visualization.arrayToDataTable(my_group(DB));

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
        data.append('start_date', $('#start-day').val())
        data.append('end_date', $('#end-day').val())

        $.ajax({
            url:`{{ env('API_URL') . '/dashboard/export/${TYPE.DAY}' }}`,
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
                    elm_export.innerHTML = __iconPlus() + ' Export Data Report Belanja';
                    elm_export.disabled = false;

                    window.open(data, '_blank');

                } else {
                    elm_export.disabled = false;
                    elm_export.innerHTML = __iconPlus() + ' Export Data Report Belanja';

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

    function Datatables() {

           return $('#orderPerDay').DataTable({
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
                            start_date: $('#start-day').val(),
                            end_date : $('#end-day').val(),
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
                            data: 'courier_name',
                            sClass: 'text-left'
                        },
                        {
                            data: 'grand_total_per_user_per_wh',
                            width: '20%',
                            sClass: 'text-left'
                        },
                    ]
            });

    }
    

    /* -------------------------- Init Datatable -------------------------- */
    
    Datatables();


    /* ------------------------------- Start Date ------------------------------- */

    if($('#start-day-picker').length) {

        $('#start-day-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
                $("#start-day").val(formatedValue)
                $('#orderPerDay').DataTable().destroy();
                Datatables();
                chartReportBelanja();
            }
        });

        $("#start-day").on('keyup', function() {
            $('#orderPerDay').DataTable().destroy();
            Datatables();
            chartReportBelanja();
        })

    }


    /* -------------------------------- End Date -------------------------------- */


    if($('#end-day-picker').length) {

        $('#end-day-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
                $("#end-day").val(formatedValue)
                $('#orderPerDay').DataTable().destroy();
                Datatables();
                chartReportBelanja();
            }
        });

        $("#end-day").on('keyup', function() {
            $('#orderPerDay').DataTable().destroy();
            Datatables();
            chartReportBelanja();
        })

    }
    

    setTimeout(() => {
        PAGE.isActive = true;
        managePageDashboard();        
    });


});

</script>

@endsection