@extends('layouts.app')

@section('title', '| Mutasi Warriors')
@section('breadcrumb', 'Dashboard / Reports / Mutasi Warriors')

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
            Export Data Report Mutasi Warriors
        </button>



        <div class="col-12 d-none">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Report Mutasi Warriors</h6>
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
                                    <th class="pt-0">BY</th>
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
        data.append('id_warrior', elm_warrior_id.value)
        data.append('start_date', $('#start-day-mutasi-wp').val());
        data.append('end_date', $('#end-day-mutasi-wp').val());

        $.ajax({
            url:`{{ env('API_URL') . '/dashboard/export/${TYPE.MUTASI_WP}' }}`,
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


    let warriors  = await(await(await fetch(`{{ env('API_URL') . '/buyers' }}`, __propsPOST)).json()).data

    let elm_warrior_id      = __getId('warrior-id');
    let elm_export = __getId('export');



    /* ------------------------ Append Data  to Select ----------------------- */
    
    elm_warrior_id.innerHTML    = elm_choose('Pilih Warrior')

    for (const data of warriors) {
        let newOption = ___createOpt(data.id, `${data.id} - ${data.fullname}`);
        elm_warrior_id.appendChild(newOption);
    }


    /* ----------------------------- Event Listener ----------------------------- */

    eventListener(elm_export.id, exportData);

    eventListener(elm_warrior_id.id, evWarrior);
    $('#warrior-id').on('select2:select', evWarrior);


    function evWarrior(e) {
        e.preventDefault();
        $('#orderPerMutasiWP').DataTable().destroy();
        Datatables(TYPE.MUTASI_WP);
    }

    function Datatables(type, obj_db = null, id_warrior = null, warrior_name = null, warehouse_name = null, nominal_transaksi = null, total_transaksi = null, ongkir = null) {
        
        $('#orderPerMutasiWP').DataTable({
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
                {
                    orderable: false,
                    data: 'assign_by',
                    sClass: 'text-center'
                },
            ],
            // rowsGroup: [2, 3, 4],

    });

    }
    

    /* -------------------------- Init Datatable -------------------------- */
    Datatables();


    /* ------------------------------- Start Date ------------------------------- */
   
    // MUTASI WP
    if($('#start-day-mutasi-wp-picker').length) {

        $('#start-day-mutasi-wp-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
                $("#start-day-mutasi-wp").val(formatedValue)
                $('#orderPerMutasiWP').DataTable().destroy();
                Datatables();
            }
        });

        $("#start-day-mutasi-wp").on('keyup', function() {
            $('#orderPerMutasiWP').DataTable().destroy();
            Datatables();
        })

    }


    if($('#end-day-mutasi-wp-picker').length) {

        $('#end-day-mutasi-wp-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD');
                $("#end-day-mutasi-wp").val(formatedValue)
                $('#orderPerMutasiWP').DataTable().destroy();
                Datatables();
            }
        });

        $("#end-day-mutasi-wp").on('keyup', function() {
            $('#orderPerMutasiWP').DataTable().destroy();
            Datatables();
        })

    }




  

    setTimeout(() => {
        PAGE.isActive = true;
        managePageDashboard();        
    });


    })

</script>

@endsection