@extends('layouts.app')

@section('title', '| Report Transaksi per Warriors')
@section('breadcrumb', 'Dashboard / Reports / Transaksi per Warriors')

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
            Export Data Report Transaksi per Warriors
        </button>



        <div class="col-12 d-none">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Transaksi per Warriors</h6>
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
                                    <th class="pt-0">Kode Principle</th>
                                    <th class="pt-0">Nama Produk</th>
                                    <th class="pt-0">Quantity</th>
                                    <th class="pt-0">HPD</th>
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



    let elm_export = __getId('export');

    elm_export.addEventListener('click', exportData);


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


    async function exportData(e) {
        if(e) {
            e.preventDefault();
        }

        elm_export.innerHTML = 'Proses ' + ___iconLoading();
        elm_export.disabled = true;

        let data = new FormData();
        data.append('token', '{{ Session::get("token")}}')
        data.append('email', '{{ Session::get("email")}}')
        data.append('start_date', $('#start-day-warrior').val())
        data.append('end_date', $('#end-day-warrior').val())
        

        $.ajax({
            url:`{{ env('API_URL') . '/dashboard/export/${TYPE.WARRIOR}' }}`,
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
                    elm_export.innerHTML = __iconPlus() + ' Export Data Report Transaksi per Warriors';
                    elm_export.disabled = false;

                    window.open(data, '_blank');

                } else {
                    elm_export.disabled = false;
                    elm_export.innerHTML = __iconPlus() + ' Export Data Report Transaksi per Warriors';

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

    function Datatables(type, obj_db = null, id_warrior = null, warrior_name = null, warehouse_name = null, nominal_transaksi = null, total_transaksi = null, ongkir = null) {
        
        $('#orderPerWarrior').DataTable({
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
                        orderable: false,
                        sClass: 'text-center'
                    },
                    {
                        data: 'principle_kode',
                    },           
                    {
                        data: 'prod_name',
                    },
                    {
                        data: 'quantity',
                        sClass: 'text-center'
                    },
                    {
                        data: 'prod_modal_price',
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
        
    }
    

    /* -------------------------- Init Datatable -------------------------- */
    Datatables();


    /* ------------------------------- Start Date ------------------------------- */
    if($('#start-day-warrior-picker').length) {

        $('#start-day-warrior-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
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
            format: 'YYYY-MM-DD',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
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




  

    setTimeout(() => {
        PAGE.isActive = true;
        managePageDashboard();        
    });


    })

</script>

@endsection