@extends('layouts.app')

@section('title', '| Transaksi per Cabang')
@section('breadcrumb', 'Dashboard / Reports / Transaksi per Cabang')

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
            Export Data Report Transaksi per Cabang
        </button>



        <div class="col-12">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Transaksi per Cabang</h6>
                            </div>
                            <hr>                    
                            <div class="row justify-content-end">
                                <div class="col-4 col-md-3">
                                    <div class="input-group date datepicker" id="start-day-trans-per-wh-picker">
                                        <input name="start_date" placeholder="Start date" type="text" class="form-control form-control-sm" id="start-day-trans-per-wh" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="input-group date datepicker" id="end-day-trans-per-wh-picker">
                                        <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-day-trans-per-wh" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-4  col-md-3">
                                    <div class="row d-flex align-items-baseline">
                                        <div class="col">
                                            <select name="warehouse_id" id="warehouse-id" class="form-control" style="width:100%">
						                    </select>
                                        </div>  -
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
                                    <th class="pt-0">Tanggal (Bulan-Tahun)</th>
                                    <th class="pt-0">Warehouse</th>
                                    <th class="pt-0">Total Warrior Transaksi</th>
                                    <th class="pt-0">Quantity</th>
                                    <th class="pt-0">Nominal Transaksi (Rp)</th>
                                    <th class="pt-0">Total Ongkir (Rp)</th>
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


    function customPost(data){
        return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data))})
    }

    const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

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
            chartReportPerWH();
        }
    );

    let elm_warehouse_id    = __getId('warehouse-id');
    let elm_export = __getId('export');

    let warehouse  = await(await(await fetch(`{{ env('API_URL') . '/warehouses' }}`, __propsPOST)).json()).data


    /* ------------------------ Append Data  to Select ----------------------- */
    
    elm_warehouse_id.innerHTML  = elm_choose('Pilih Warehouse');
    
    for (const data of warehouse) {
        let newOption = ___createOpt(data.id, `${data.short} - ${data.name}`);
        elm_warehouse_id.appendChild(newOption);
    }


    eventListener(elm_warehouse_id.id, evWarehouse);
    $('#warehouse-id').on('select2:select', evWarehouse);

  
    function evWarehouse(e) {
        e.preventDefault();
        $('#orderPerTransWH').DataTable().destroy();
        Datatables();
        chartReportPerWH();
    }
  

    elm_export.addEventListener('click', exportData);

    async function chartReportPerWH() {


        log($('#start-day-trans-per-wh').val());

        try {
            
            let report  = await(await(await fetch(`{{ env('API_URL') . '/dashboard/${TYPE.TRANS_PER_WH}' }}`, 
                customPost(
                    {
                        start_date:$('#start-day-trans-per-wh').val(), 
                        end_date:$('#end-day-trans-per-wh').val(), 
                        warehouse_id: $('#warehouse-id').val()
                }))).json()).data
                
            log(report, $('#warehouse-id').val());
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
                            elm.push(parseInt(item.total_transaksi))
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
                if(item.length) {
                    DB.push(item)
                }    
            }
            
            
            const DB2 = DB.slice();


            let data = google.visualization.arrayToDataTable(my_group(DB));

            let chart = new google.visualization.LineChart(__getId('chart-report-per-cabang'));

            google.visualization.events.addListener(chart, 'error', errReportPerWH);    

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
        data.append('start_date', $('#start-day-trans-per-wh').val())
        data.append('end_date', $('#end-day-trans-per-wh').val())
        data.append('warehouse_id', $('#warehouse-id').val())

        $.ajax({
            url:`{{ env('API_URL') . '/dashboard/export/${TYPE.TRANS_PER_WH}' }}`,
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
                    elm_export.innerHTML = __iconPlus() + ' Export Data Report Transaksi per Cabang';
                    elm_export.disabled = false;

                    window.open(data, '_blank');

                } else {
                    elm_export.disabled = false;
                    elm_export.innerHTML = __iconPlus() + ' Export Data Report Transaksi per Cabang';

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


    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
            by : "{{ Session::get('user')->id }}"
        }
    }

    function Datatables(type, obj_db = null, id_warrior = null, warrior_name = null, warehouse_name = null, nominal_transaksi = null, total_transaksi = null, ongkir = null) {
        
        $('#orderPerTransWH').DataTable({
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
                            orderable: false,
                        },
                        {
                            data: 'nominal_transaksi',
                            width: '40%',
                            sClass: 'text-left'
                        },
                        {
                            data: 'total_ongkir',
                            width: '40%',
                            sClass: 'text-left'
                        },
                        {
                            data: 'total_transaksi',
                            width: '40%',
                            sClass: 'text-left'
                        },
                    ]
            });      
        
    }
    

    /* -------------------------- Init Datatable -------------------------- */
    Datatables();


    /* ------------------------------- Start Date ------------------------------- */

    if($('#start-day-trans-per-wh-picker').length) {

    $('#start-day-trans-per-wh-picker').datetimepicker({
        format: 'YYYY-MM-DD',
        viewMode: 'months',
        sideBySide: true
    }).on('dp.change', function (e) {
        if(e && e.date) {
            let formatedValue = e.date.format('YYYY-MM-DD');
            $("#start-day-trans-per-wh").val(`${formatedValue}`)
            $('#orderPerTransWH').DataTable().destroy();
            Datatables();
            chartReportPerWH()
        }
        // if(e && e.date) {
        //     let formatedValue = e.date.format('YYYY-MM');
        //     const date    = new Date(formatedValue);
        //     const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDate();
        //     $("#start-day-trans-per-wh").val(`${formatedValue}-0${firstDay}`)
        //     $('#orderPerTransWH').DataTable().destroy();
        //     Datatables();
        //     chartReportPerWH()
        // }
    });

    $("#start-day-trans-per-wh").on('keyup', function() {
        $('#orderPerTransWH').DataTable().destroy();
        Datatables();
        chartReportPerWH()
    })

    }


    if($('#end-day-trans-per-wh-picker').length) {

        $('#end-day-trans-per-wh-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            viewMode: 'months',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
                $("#end-day-trans-per-wh").val(`${formatedValue}`)
                $('#orderPerTransWH').DataTable().destroy();
                Datatables();
                chartReportPerWH()
            }
            // KODE DULU
            // if(e && e.date) {
            //     let formatedValue = e.date.format('YYYY-MM');
            //     const date    = new Date(formatedValue);
            //     const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            //     $("#end-day-trans-per-wh").val(`${formatedValue}-${lastDay}`)
            //     $('#orderPerTransWH').DataTable().destroy();
            //     Datatables();
            //     chartReportPerWH()
            // }
        });

        $("#end-day-trans-per-wh").on('keyup', function() {
            $('#orderPerTransWH').DataTable().destroy();
            Datatables();
            chartReportPerWH()
        })

    }


  

    setTimeout(() => {
        PAGE.isActive = true;
        managePageDashboard();        
    });


    })

</script>

@endsection