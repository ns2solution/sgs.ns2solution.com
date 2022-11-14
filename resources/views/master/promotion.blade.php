@extends('layouts.app')

@section('title', '| Promosi')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Promosi')

@section('content')

<style>
    .waiting-for-fetch-data.active{
        position: absolute; background: rgb(0 0 0 / 38%); display: block; height: 100%; width: 100%; top: 0; z-index: 99; font-size: 24px; color: #fff; display: flex; justify-content: center; align-items: center;
    }
    .waiting-for-fetch-data.inactive{
        display:none;
    }
</style>

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" id="open-modal-promosi" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Promosi&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Promosi </h5>
					<button class="btn p-0" type="button" id="btn-refresh-promosi">
                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                    </button>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="data-table" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>Aksi</th>
							<th>#</th>
                            <th>ID</th>
                            <th>Nama Promosi</th>
                            <th>Gambar Promosi</th>
                            <th>Warehouse_ID</th>
                            <th>Warehouse</th>
                            <th>Tipe Promosi</th>
                            <th>Bundling_ID</th>
                            <th>Tipe Bundling</th>
                            <th>Total</th>
                            <th>Total Harga</th>
                            <th>Stock Promosi</th>
							<th>Tanggal Mulai</th>
                            <th>Tanggal Berakhir</th>
                            <th>Start_Date</th>
                            <th>End_Date</th>
                            <th>Start_Time</th>
							<th>End_Time</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="modal  fade" id="form-promosi-modal" tabindex="-1">
	<div class="modal-dialog modal-lg" role="document" style="max-width: 1200px;">
		<div class="modal-content" style="border:none;overflow-y: auto;">
			<form method="get" id="form-promosi" style="position: relative;">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" id="modal-header" style="color:#fff;">Edit Detail Warehouse</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" >
                    <div class="form-group">
                        <input type="file" name="promosi_image" id="promosi_image" class="dropify"  data-height="150" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                    </div>
                    <div class="row">
	                    <div class="form-group col-md-6">
                            <label>Nama Promosi</label>
                            <input id="promosi_name" class="form-control" name="promosi_name" type="text" autocomplete="off" minlength="3" placeholder="Masukkan nama singkat" required>

                        </div>
                        <div class="form-group col-md-6">
                            <label>Warehouse</label>
						    <select class="form-control select" name="warehouse_id" id="warehouse_id" autocomplete="off" placeholder="Pilih warehouse" required>
						    	<!-- Option warehouse -->
						    </select>
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Pilih Product</label>
						<select class="form-control select" name="stock_id[]" id="stock_id" autocomplete="off" placeholder="Pilih stock"  multiple="multiple" required>
							<!-- Option from stock attach product -->
                        </select>
                    </div>
                    <div class="form-group col-md-11">
                        <!-- Table Button Add product -->

                    </div>
                    <div class="row">
	                    <div class="form-group col-md-6">
                            <label>Tanggal Mulai</label>
                            <input id="start_date" class="form-control" name="start_date" type="text" minlength="3" required>
						</div>
	                    <div class="form-group col-md-6">
                            <label>Tanggal Berakhir</label>
                            <input id="end_date" class="form-control" name="end_date" type="text" minlength="3" required>
						</div>
                    </div>


                    <div class="row">
                        <div class="form-group mb-0 col-6">
                            <label class="col-form-label">Jam Mulai</label>
                            <input type="hidden" id="start-time-sd-tmp">
                            <input class="form-control without_ampm" type="text" class="form-control" name="start_time" id="start-time-sd"  placeholder=""/>
                        </div>
                        <div class="form-group mb-0 col-6">
                            <label class="col-form-label">Jam Berakhir</label>
                            <input type="hidden" id="end-time-sd-tmp">
                            <input class="form-control without_ampm" type="text" class="form-control" name="end_time" id="end-time-sd"  placeholder=""/>
                        </div>
                    </div>

                    
                    <!-- <div class="row">
	                    <div class="form-group col-md-6">
                            <label>Jam Mulai</label>
							<input class="form-control without_ampm" name="start_time" type="time" value="00:00" id="start_time" required>
						</div>
	                    <div class="form-group col-md-6">
                            <label>Jam Berakhir</label>
							<input class="form-control without_ampm" name="end_time" type="time" value="00:00" id="end_time" required>
						</div>
                    </div> -->
                    
                    <div class="form-group">
						<label>Tipe Promosi</label>
						<select class="form-control" id="promosi_type" name="promosi_type">
							<!-- Option Tipe Promosi -->
						</select>
					</div>
					<div class="row" id="div_type_label">
                        <!-- Label Tipe Promosi-->
                    </div>
                    <div id="div_type">
                        <!-- Value Tipe promosi with stock information -->
					</div>
                </div>
                <div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal" id="close-promosi"> Batal </button>
					<button type="submit" class="btn btn-primary btn-custom" id="save-promosi"> Simpan </button>
                </div>
                <div class="waiting-for-fetch-data inactive" id="waiting-for-fetch-data">

                    <svg width="25" viewBox="-2 -2 42 42" class="mr-3" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-3">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18" transform="rotate(114.132 18 18)">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>

                    Loading for Fetcing Promosi Item..
                </div>
            </form>
		</div>
	</div>
</div>

<!-- Modal add product -->
<div class="modal fade" id="modal-view" tabindex="-1">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content" data-dismiss="modal" style="border:none;background-color:transparent;">
			<div class="modal-body">
				<div class="form-group">
					<center>
						<img src="" style="max-height:80vh;" draggable="false" id="img-view">
					</center>
				</div>
			</div>
		</div>
	</div>
</div>


@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<script>

$(document).ready(async function () {
    $('.modal-backdrop').css('position','relative')

    // ----------- variable ---------------------


    let elm_promosi_id = __getId('id')
    // let elm_prod_type_id = __getId('prod-type-id') promosi_image
    let elm_promosi_image = __getId('promosi_image')
    let elm_promosi_name = __getId('promosi_name')
    let elm_warehouse_id = __getId('warehouse_id')
    let elm_stock_id = __getId('stock_id')
    let elm_promosi_type = __getId('promosi_type')
    let elm_start_date = __getId('start_date')
    let elm_end_date = __getId('end_date')
    let elm_start_time = __getId('start_time')
    let elm_end_time = __getId('end_time')
    let elm_div_type_label = __getId('div_type_label')
    let elm_div_type = __getId('div_type')
    let elm_form_promosi = __getId('form-promosi')
    let elm_save_promosi = __getId('save-promosi')
    let elm_close_promosi = __getId('close-promosi')
    let elm_btn_refresh_promosi = __getId('btn-refresh-promosi')
    let elm_modal_header = __getId('modal-header')
    let elm_waiting_for_fetch_data = __getId('waiting-for-fetch-data')
    let elm_open_product_promo = __getId('open-product-promo')

    const isSuccessfullyGettingData = {
        promosi: false
    }


    let table = null
    let elm_open_modal = __getId('open-modal-promosi')
    let url_post = ''
    let selected_stock_length = null
    let input_bundle = ''
    let newOption = null
    let init_bundle = 0;
    let WAREHOUSES = [], PRODUCTS = [], stock = [], bundle_stock_id = [], promosi_item = [];


    // ----------- fetch data ------------------

    const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), {
        by : "{{ Session::get('user')->id }}"
    }
    ))})
    const __propsGET = Object.assign({}, {
        headers: __headers(),
        method: 'GET'
    })


    $.fn.dataTable.ext.errMode = 'none';

    table = await $('#data-table').DataTable({
        pageLength: 10,
        processing: true,
        serverSide: true,
        order: [[0, 'ASC']],
        ajax:{
            url: "{{ env('API_URL') . '/promosi/data-table' }}",
            dataType: 'JSON',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                token: "{{ Session::get('token') }}",
                email: "{{ Session::get('email') }}"
            }
        },
        columns: [
            {
                sClass: 'text-center',
                orderable: false,
                render: function(){
                    return `
                        <button class="btn p-0" type="button" id='edit-btn'>
                            <svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        &nbsp;&nbsp;
                        <button class="btn p-0" type="button" id='delete-btn'>
                            <svg viewBox="0 0 24 24" width="19" height="19" stroke="#FF3366" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    `;
                }
            },
            {
                data: 'no'
            },
            {
                data: 'id'
            },
            {
                data: 'promosi_name',
                width: '50%'
            },
            {
                data: 'promosi_image',
                sClass: 'text-center',
                orderable: false,
                render: function(data){
                    if(data != '-'){
                        return `<img src="{{ env('API_URL') . '/' }}${data}" id="zoom-foto" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();">`;
                    }else{
                        return '-';
                    }
                },
                width: '20%'
            },
            {
                data: 'warehouse_id',
                visible: false,
                width: 'text-center'
            },
            {
                data: 'warehouse_name',
                width: 'text-center',
                orderable: false
            },
            {
                data: 'promosi_type',
                sClass: 'text-center',
                render: function(data){
                    if(data == 1){
                        return `<span class="badge badge-pill badge-primary" style="padding:5px 8px 5px 8px;font-size:12px;">Bundling</span>`;
                    }else{
                        return '<span class="badge badge-pill badge-secondary" style="padding:5px 8px 5px 8px;font-size:12px;">Diskon</span>';
                    }
                }
            },
            {
                data: 'info_bundle_id',
                sClass: 'text-center',
                visible: false
            },
            {
                data: 'info_bundle',
                sClass: 'text-center',
                render: function(data, type, row, meta){
                    if(row['info_bundle_id'] == 1){
                        return `<span class="badge badge-pill badge-success" style="padding:5px 8px 5px 8px;font-size:12px;">${data}</span>`;
                    }if(row['info_bundle_id'] == 2){
                        return `<span class="badge badge-pill badge-danger" style="padding:5px 8px 5px 8px;font-size:12px;">${data}</span>`;
                    }
                }
            },
            {
                data: 'total_value',
                sClass: 'text-center',
                visible: false
            },
            {
                data: 'total_value',
                sClass: 'text-center',
                render: function(data){
                    return convertCurrency(data);
                }
            },
            {
                data: 'stock_promosi',
                sClass: 'text-center',
                orderable: false
            },
            {
                data: 'start_date_view',
                sClass: 'text-center'
            },
            {
                data: 'end_date_view',
                sClass: 'text-center'
            },
            {
                data: 'start_date',
                visible: false,
                sClass: 'text-center'
            },
            {
                data: 'end_date',
                visible: false,
                sClass: 'text-center'
            },
            {
                data: 'start_time',
                visible: false,
                sClass: 'text-center'
            },
            {
                data: 'end_time',
                visible: false,
                sClass: 'text-center'
            }
        ]
    });

    WAREHOUSES  = await(await(await fetch(`{{ env('API_URL') . '/warehouse' }}`, __propsGET)).json()).data;
    PRODUCTS = await(await(await fetch(`{{ env('API_URL') . '/product/get-data' }}`, customPost({prod_type_id:2}))).json()).data;
    
    // warn(WAREHOUSES)    
    // warn(PRODUCTS)
    
    function __serializeFormUpload(form) {
        let formData = new FormData(form)
        return formData
    // return new URLSearchParams(new FormData(form)).toString()
    }

    function porpertyPOSTUpload(body) {
        return {
            headers: __headersupload(),
            method: 'POST',
            body: body
        }
    }


    function __headersupload() {
        return {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': '*/*'
        }
    }

    function __uploadFile(url, method, data) {
            data.append('_token', '{{ csrf_token() }}');
            data.append('token',  "{{ Session::get('token')}}");
            data.append('email',  "{{ Session::get('email')}}");
            data.append('by', "{{ Session::get('user')->id }}");
            var res = new Promise(function (resolve, reject) {
                $.ajax({
                    type: method,
                    enctype: 'multipart/form-data',
                    url: url,
                    data: data,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function (result) {
                        resolve(result);
                    },
                    error: async function (err) {
                        reject(err);
                    }
                });
            });

            let response = res.then(function(result) {
                     return result // "resolve"
                }).then(function(result) {
                    return result // "normalReturn"
                });
            return response;

        }


    // ----------- init ---------------------

    $('select').select2();

    $('select').on('select2:close', function (e) {
        $(this).valid();
    });

    //upload image handler

    var drEvent = $('.dropify').dropify({
        error: {
            'fileSize': 'Ukuran file terlalu besar ( maks).',
            'minWidth': 'Lebar gambar terlalu kecil (px min).',
            'maxWidth': 'Lebar gambar terlalu besar (px maks).',
            'minHeight': 'Tinggi gambar terlalu kecil ( px min).',
            'maxHeight': 'Tinggi gambar terlalu besar ( px maks).',
            'imageFormat': 'File tidak sesuai, format didukung ( xls, xlsx).'
        },  
        messages: {
            default: 'Drag atau drop untuk memilih file',
            replace: 'Ganti',
            remove:  'Hapus',
            error:   'error'
        }
    });

    drEvent.on('change', function(event, element){
        if(__querySelectorAll('.dropify-wrapper.has-error').length) {
            elm_save_promosi.disabled = true;
        } else {
            elm_save_promosi.disabled = false;
        }
    });

    drEvent.on('dropify.afterClear', function(event, element){
        elm_save_promosi.disabled = true;
    });

    drEvent.on('dropify.fileReady', function(event, element){
        elm_save_promosi.disabled = false;
    });

    drEvent.on('dropify.errors', function(event, element){
        elm_save_promosi.disabled = true;
    });

    $('.dropify-error').empty();



    //set date
    var dateToday = new Date();
    dateToday.setDate(dateToday.getDate());
    $('#start_date').datepicker({format: "dd/mm/yyyy", todayHighlight: true, autoclose: true, startDate: dateToday , update:  new Date()});
    $('#end_date').datepicker({format: "dd/mm/yyyy", todayHighlight: true, autoclose: true, startDate: dateToday , update:  new Date()});

    newOption = ___createOpt("", "-- Pilih Type --");
    elm_promosi_type.appendChild(newOption);
    newOption = ___createOpt(1, "Bundling");
    elm_promosi_type.appendChild(newOption);
    newOption = ___createOpt(2, "Diskon");
    elm_promosi_type.appendChild(newOption);
    elm_promosi_type.value = "";
    elm_promosi_type.dispatchEvent(new Event("change", {bubbles: true,}));

    const rulesForm = {
        rules: {
            //prod_number : 'required',
            //promosi_image: elm_promosi_id.value ? null : 'required',
            promosi_name : 'required',
            warehouse_id : 'required',
            stock_id : 'required',
            promosi_type : 'required',
            start_date : 'required',
            end_date : 'required',
            start_time : 'required',
            end_time : 'required'
        },
        ...rulesValidateGlobal,
        submitHandler:(form, e) => {
            e.preventDefault();

            const id =  elm_promosi_id.value ? elm_promosi_id.value : null;

            if(id) {
                updatePromosi(e, id)
            } else {
                savePromosi(e)
            }

            return false;
        }
    }

    $('#form-promosi').submit((e) => {
        e.preventDefault();
    }).validate(rulesForm);


    // ----------- function ---------------------

    function customPost(data){
        return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data
            ))})
    }

    //modify input
    function onProdBasePrice(e) {
        let value = parseInt(e.target.value);
        if(value < 0){
            elm_prod_base_price.value = 0;
        }

        if (new String(e.target.value) > 10) {
            elm_prod_base_price.value = parseInt(new String(e.target.value).slice(0,10).toString())
        }
    }

    function onProdGram(e) {
        let value = parseInt(e.target.value);
        if(value < 0){
            elm_prod_gram.value = 0;
        }

        // if (new String(e.target.value) > 3) {
        //     elm_prod_gram.value = parseInt(new String(e.target.value).slice(0,3).toString())
        // }
    }

    function preventPositiveNumber(type) {
        return function(e) {
            e.preventDefault()
            switch(type) {
                case 'prod_base_price':
                    if(elm_prod_base_price.value < 0) {
                        elm_prod_base_price.value = 0;
                    }
                break;
                case 'prod_gram' :
                    if(elm_prod_gram.value < 0) {
                        elm_prod_gram.value = 0;
                    }
                break;
            }
        }
    }

    function convertCurrency(val){
        return 'Rp ' + val.format(2, 3, '.', ',')
    }

    async function savePromosi(e) {
        if(e) {
            e.preventDefault();
        }
        // json stringify
        //let formData = __serializeFormUpload(elm_form_promosi);
        //const newFormData =   Object.assign({}, formData, getCurrentToken())
        // process save to server
        elm_save_promosi.innerHTML = 'Saving ' + ___iconLoading();
        elm_save_promosi.disabled = true;

        try {
            let formData = new FormData(elm_form_promosi);

            let res = await __uploadFile(`{{ env('API_URL') . '/promosi/create' }}`, 'POST', formData);

            const {status, message} = res;

            if(status) {

                refreshPromosiDT();

                toastr.success(message, { fadeAway: 10000 });
                elm_save_promosi.innerHTML = 'Save';
                elm_save_promosi.disabled = false;

                $('#form-promosi-modal').modal('hide')

            } else {
                elm_save_promosi.disabled = false;
                elm_save_promosi.innerHTML = 'Save';

                console.error(message);

                $('#form-promosi-modal').modal('hide')
            }
        } catch (error) {
            //error(error);
            toastr.error(error,  { fadeAway: 10000 });

            $('#form-promosi-modal').modal('hide')

            elm_save_promosi.innerHTML = 'Save';
        }

    }

    async function updatePromosi(e, id) {
        if(e) {
            e.preventDefault();
        }

        // process save to server
        elm_save_promosi.innerHTML = 'Saving ' + ___iconLoading();
        elm_save_promosi.disabled = true;
        // debugger;


        try {

            let formData = new FormData(elm_form_promosi);

            let res = await __uploadFile(`{{ env('API_URL') . '/promosi/update/${id}' }}`, 'POST', formData);

            const {status, message} = res;

            if(status) {

                refreshPromosiDT();

                toastr.success(message, { fadeAway: 10000 });
                elm_save_promosi.innerHTML = 'Save';
                elm_save_promosi.disabled = false;

                $('#form-promosi-modal').modal('hide')

            } else {
                elm_save_promosi.disabled = false;
                elm_save_promosi.innerHTML = 'Save';

                console.error(message);

                $('#form-promosi-modal').modal('hide')
            }
        } catch (error) {
            console.error(error);
            toastr.error(error,  { fadeAway: 10000 });

            $('#form-promosi-modal').modal('hide')

            elm_save_promosi.innerHTML = 'Save';
        }

    }

    function refreshPromosiDT(e) {
        if(e) {
            e.preventDefault()
        }
        table.ajax.reload();
    }


    async function evWarehouse(e) {
        e.preventDefault()

        const id = e.params ? e.params.data.id : e.target.value;

        /* ---------------------------- ambil data stock ---------------------------- */

        try {
            
            elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

            if(id) {

                elm_stock_id.disabled = false;
                elm_stock_id.innerHTML = elm_choose('Pilih');
                
                for (const data of PRODUCTS) {
                    
                    if(data.stock && data.stock.length > 0){
                        let i = 1;
                        let ii = 1;

                        // console.error(data.stock);
                        for (const stock of data.stock) {
                            
                            if(stock.warehouse_id == id){

                                let newOption = ___createOpt(stock.id, data.prod_name + ' | Stock : ' + stock.stock);
                                elm_stock_id.appendChild(newOption);
                                elm_stock_id.dispatchEvent(new Event("change", {bubbles: true,}));
                            
                            }
                            
                            /*else{

                                let newOption = ___createOpt(1, data.prod_name + ' | Stock : 0 ');
                                elm_stock_id.appendChild(newOption);
                                elm_stock_id.dispatchEvent(new Event("change", {bubbles: true,}));
                            }
                            */
                           
                        }
                    }else{
                        
                        let newOption = ___createOpt(1, data.prod_name + ' | Stock : 0 ');
                        elm_stock_id.appendChild(newOption);
                        elm_stock_id.dispatchEvent(new Event("change", {bubbles: true,}));
                    }
                }

                //handle if id exist

                if(elm_promosi_id.value){

                    promosi = await(await(await fetch(`{{ env('API_URL') . '/promosi' }}`, customPost({id:elm_promosi_id.value}))).json()).data
                    promosi_item = await(await(await fetch(`{{ env('API_URL') . '/promosi-item' }}`, customPost({promosi_id:elm_promosi_id.value}))).json()).data


                    //handle if type promo has exist
                    if(elm_promosi_type.value){
                        _createLabelPromo(elm_promosi_type.value)
                        _createPromo(elm_promosi_type.value,promosi,promosi_item)
                    }

                     //trigger change for select2 to set values/styles
                    bundle_stock_id = []
                    for (const data of promosi_item) {
                        bundle_stock_id.push(data.stock_id)
                    }
                    $('#stock_id').val(bundle_stock_id).trigger('change');
                }

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');
            } else {
                elm_stock_id.disabled = true;
                elm_stock_id.innerHTML = elm_choose('Pilih')
                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

            }


            // debugger;
        } catch (error) {

            // debugger
            //error(error);
            toastr.error(error,  { fadeAway: 10000 });

            // $('#form-promosi-modal').modal('hide')

            elm_save_promosi.innerHTML = 'Save';
        }

    }

    function evStock(e) {
        e.preventDefault()

        const id = e.params ? e.params.data.id : e.target.value;

        if(id){

            if($('#stock_id').val().length !== selected_stock_length && $('#stock_id').val().length){
                
                selected_stock_length = $('#stock_id').val().length
                
                const promosi_type = elm_promosi_type.value

                if(promosi_type){
                    _createLabelPromo(promosi_type)
                    _createPromo(promosi_type)
                }
            }else{
                selected_stock_length = null
            }
        }

        // dispatch promosi type - untuk generate promo label dll
        if(elm_promosi_type.value) { 
            elm_promosi_type.dispatchEvent(new Event("change", {bubbles: true,}));
        }
    }

    function evPromo(e) {
        e.preventDefault()

        const id = e.params ? e.params.data.id : e.target.value;

        if(id){
            _createLabelPromo(id)
            _createPromo(id)
        }
    }

    function _createLabelPromo(id){
        if(id){
            if(id == 1){
                elm_div_type_label.innerHTML = `
                        <div class="form-group col-md-4">
    	    				<label>Product</label>
    	    			</div>
                        <div class="form-group col-md-2">
    	    				<label>Stock</label>
                        </div>
                        <div class="form-group col-md-2">
    	    				<label>Harga/Unit</label>
                        </div>
                        <div class="form-group col-md-2">
    	    				<label>Stock Promosi</label>
    	    			</div>
                        `;
            }else{
                elm_div_type_label.innerHTML =`
                        <div class="form-group col-md-2">
    	    				<label>Product</label>
    	    			</div>
                        <div class="form-group col-md-1">
    	    				<label>Stock</label>
                        </div>
                        <div class="form-group col-md-1">
    	    				<label>Harga/Unit</label>
                        </div>
                        <div class="form-group col-md-2">
    	    				<label>Stock Promosi</label>
    	    			</div>
                        <div class="form-group col-md-2">
    	    				<label>Type</label>
    	    			</div>
                        <div class="form-group col-md-2">
    	    				<label>Besar Diskon (%) / Harga Setelah Diskon</label>
                        </div>
                        <div class="form-group col-md-2">
    	    				<label>Harga</label>
    	    			</div>
                        `;
            }
        }
    }

    async function _createPromo(id, promosi=null, promosi_item=null){

        //get data stock
        //ambil data stock bundle jadi 1 id

        //log(bundle_stock_id)
        bundle_stock_id = $("#stock_id").val();
        try {
            elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

            const __propsPOST = customPost({bundle_id:bundle_stock_id});
            stock = await(await(await fetch(`{{ env('API_URL') . '/stock/get-data' }}`, __propsPOST)).json()).data
            
            // log(bundle_stock_id, stock, 'createPromo')
            // log(promosi_item, 'promosi_item')
            if(id){
                let html = ``;
                if(promosi_item){
                    for(const data of promosi_item){
                        //check if stock == 0
                        if(data.stock) {
                            if (data.stock.id === 1 || data.stock.stock === 0) { continue; }
                            let product_price = data.stock.product.prod_base_price
                            product_price = parseFloat(product_price).format(2, 3, '.', ',');
                            if(id == 1){
                                //write product name
                                //write stock
                                //write stock_promo input []
                                html +=  `
                                <div class="row">
                                    <input type="hidden" name="promosi_item_id" value="${data.id}">
                                    <input type="hidden" value="${data.stock_id}">
                                    <div class="form-group col-md-4">
                                        ${data.stock.product.prod_name}
                                    </div>
                                    <div class="form-group col-md-2">
                                        ${data.stock.stock}
                                    </div>
                                    <div class="form-group col-md-2">
                                        Rp. ${ product_price}
                                    </div>
                                    <div class="form-group col-md-2">
                                        <input class="form-control promosi-stock" name="stock[]" data-stock="${data.stock.stock}" type="text" minlength="1" value="${data.stock_promosi}">
                                    </div>
                                </div>
                                `;


                            }else{
                                //write product name
                                //write stock
                                //write stock_promo input []
                                //write type select []
                                //write nilai input []



                                html +=`
                                <div class="row">
                                    <input type="hidden" name="promosi_item_id" value="${data.id}">
                                    <div class="form-group col-md-2">
                                        ${data.stock.product.prod_name}
                                    </div>
                                    <div class="form-group col-md-1">
                                        ${data.stock.stock}
                                    </div>
                                    <div class="form-group col-md-1">
                                        Rp. ${ product_price}
                                    </div>
                                    <div class="form-group col-md-2">
                                        <input class="form-control promosi-stock" name="stock[]" data-stock="${data.stock.stock}" type="text" minlength="1" value="${data.stock_promosi}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <select class="form-control type" name="type[]">
                                    `
                                html +=  data.type == 1 ? `<option value="1" selected>Persentase</option>` : `<option value="1">Persentase</option>`
                                html +=  data.type == 2 ? `<option value="2" selected>Fix</option>` : `<option value="2">Fix</option>`
                                html +=`
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <input class="form-control promosi-base-percent" name="value[]" type="text" minlength="1" value="${data.value ? data.value : 0 }" data-price="${data.stock.product.prod_base_price}" disabled="false">
                                        <input class="form-control promosi-base-price" data-inputmask="'alias': 'currency'" inputmode="numeric" name="value[]" type="text" minlength="1" value="${data.value ? data.value : 0 }" data-price="${data.stock.product.prod_base_price}" disabled="false">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <input class="form-control promosi-fix-price" name="fix_value[]" type="text" minlength="1" value="Rp ${data.fix_value ? data.fix_value.format(2, 3, '.', ',') : 0}">
                                    </div>
                                </div>
                                `;
                            }
                        }
                    }
                }else{
                    for (const data of stock) {
                        if (data.stock === 0) { continue; }
                        let product_price = data.product.prod_base_price
                        product_price = parseFloat(product_price).format(2, 3, '.', ',');
                        if(id == 1){
                            //write product name
                            //write stock
                            //write stock_promo input []
                            html +=  `
                            <div class="row">
                                <input type="hidden" value="${data.id}">
                                <div class="form-group col-md-4">
			        		    	${data.product.prod_name}
                                </div>
                                <div class="form-group col-md-2">
			        		    	${data.stock}
                                </div>
                                <div class="form-group col-md-2">
			        		    	Rp. ${product_price}
                                </div>
                                <div class="form-group col-md-2">
			        		    	<input class="form-control promosi-stock" name="stock[]" data-stock="${data.stock}" type="text" minlength="1">
                                </div>
                            </div>
                            `;

                        }else{
                            //write product name
                            //write stock
                            //write stock_promo input []
                            //write type select []
                            //write nilai input []
                            //write harga akhir []

                            html +=  `
                                <div class="row">
                                    <div class="form-group col-md-2">
			        		        	${data.product.prod_name}
                                    </div>
                                    <div class="form-group col-md-1">
			        		        	${data.stock}
                                    </div>
                                    <div class="form-group col-md-1">
			        		        	Rp. ${product_price}
                                    </div>
                                    <div class="form-group col-md-2">
			        		        	<input class="form-control promosi-stock" name="stock[]" data-stock="${data.stock}" type="text" minlength="1">
                                    </div>
			        		        <div class="form-group col-md-2">
                                        <select class="form-control type" name="type[]">
                                            <option value="1">Persentase</option>
                                            <option value="2">Fix</option>
			        		            </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <input class="form-control promosi-base-percent" name="value[]" type="text" minlength="1" data-price="${data.product.prod_base_price}" disabled="false">
                                        <input class="form-control promosi-base-price" data-inputmask="'alias': 'currency'" inputmode="numeric" name="value[]" type="text" minlength="1" data-price="${data.product.prod_base_price}" disabled="false">
                                    </div>
                                    <div class="form-group col-md-2">
			        		        	<input class="form-control promosi-fix-price" name="fix_value[]" type="text" minlength="1">
                                    </div>
                                </div>
                            `;

                        }
                    }
                }
                //get total value
                if(id == 1){
                    if(elm_promosi_id.value && promosi){
                        input_bundle = `
                            ${html}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Harga Bundling</label>
			        			    <input class="form-control value-promo" data-inputmask="'alias': 'currency'" inputmode="numeric" name="value[]" type="text" minlength="1" value="${promosi.total_value}">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Total Bundle</label>
			        			    <input class="form-control total-bundle" data-inputmask="'alias': 'currency'" inputmode="numeric" name="total_bundle" type="text" minlength="1" value="${promosi.total_bundle}">
                                </div>
                                <div class="form-group col-md-2">
    	    			        	<label>Info Bundle</label>
                                    <select class="form-control info_bundle" name="info_bundle_id">
                                    ${promosi.info_bundle_id == 1  ? '<option value="1" selected>Normal Bundle</option>' : '<option value="1">Normal Bundle</option>'}
                                    ${promosi.info_bundle_id == 2  ? '<option value="2" selected>BOGOF</option>' : '<option value="2">BOGOF</option>'}
			        		        </select>
                                </div>
                            </div>
                            `;
                    }else{
                        input_bundle = `
                            ${html}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Harga Bundling</label>
			        			    <input class="form-control value-promo" data-inputmask="'alias': 'currency'" inputmode="numeric" name="value[]" type="text" minlength="1">
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Total Bundle</label>
			        			    <input class="form-control total-bundle" data-inputmask="'alias': 'currency'" inputmode="numeric" name="total_bundle" type="text" minlength="1">
                                </div>
                                <div class="form-group col-md-2">
    	    			        	<label>Info Bundle</label>
                                    <select class="form-control info_bundle" name="info_bundle_id">
                                        <option value="1">Normal Bundle</option>
                                        <option value="2">BOGOF</option>
			        		        </select>
                                </div>
                            </div>
                            `;
                    }
                }

                elm_div_type.innerHTML = id == 1 ? input_bundle : html;
                $( "select.type" ).each(function() {
                    val = $( this ).val()
                    let input_field_percent = $(this).parent().parent().find(".promosi-base-percent");
                    let input_field_price = $(this).parent().parent().find(".promosi-base-price");

                    if(val){
                      if(val == 1){
                          input_field_percent.show().removeAttr('disabled')
                          input_field_price.hide().attr('disabled')
                      }else{
                          input_field_percent.hide().attr('disabled')
                          input_field_price.show().removeAttr('disabled')
                      }
                    }
                    //event change type
                    $(this).on("change", function(){
                        let input_field_percent = $(this).parent().parent().find(".promosi-base-percent");
                        let input_field_price = $(this).parent().parent().find(".promosi-base-price");
                        let stock_elm = $(this).parent().parent().find('.promosi-stock');
                        let stock_promosi = parseFloat(stock_elm.val());
                        let type = $(this).val();
                        let total = 0;
                        let input_field;
                        if(type == 1){
                            input_field_percent.show().removeAttr('disabled')
                            input_field_price.hide().attr('disabled')
                            input_field = input_field_percent;
                        }else{
                            input_field_percent.hide().attr('disabled')
                            input_field_price.show().removeAttr('disabled')
                            input_field = input_field_price;
                        }
                        let dInput = input_field.val();
                        let prod_base_price = parseFloat($(input_field).data('price'));
                        total = type == 1 && dInput > 100 ? 0 : evBasePrice(dInput,prod_base_price,type,stock_promosi)
                        $(this).parent().parent().find(".promosi-fix-price").val(total);
                    });
                });

                //event promosi isi total stock
                $('.promosi-stock').on("keyup", function(e) {
                    //$(".dDimension:contains('" + dInput + "')").css("display","block");
                    let input_field_percent = $(this).parent().parent().find(".promosi-base-percent");
                    let input_field_price = $(this).parent().parent().find(".promosi-base-price");
                    let type = $(this).parent().parent().find("select").children("option:selected").val();
                    let target = $(this).parent().parent().find(".promosi-fix-price")
                    let stock_elm = $(this);
                    let stock_promosi = parseFloat(stock_elm.val());
                    let stock = parseFloat($(this).data('stock'));
                    let total = 0;
                    //error init
                    $(this).siblings('span').html('')
                    if(type == 1){
                        input_field = input_field_percent;
                    }else{
                        input_field = input_field_price;
                    }
                    let dInput = input_field.val();
                    if(stock_promosi){
                        $(this).siblings('span').html('')
                        if (stock_promosi < 0) {
                            $(this).val(0);
                            $(this).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input min 0</span>`);
                            stock_promosi = 0;
                        }else if(stock_promosi > stock){
                            $(this).val(stock);
                            $(this).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input max ${stock}. </span>`);
                            stock_promosi = stock;
                        }
                    }

                    //error init
                    let prod_base_price = parseFloat($(input_field).data('price'));
                    total = evBasePrice(dInput,prod_base_price,type,stock_promosi)
                    target.val(total);
                });

                //event promosi isi harga
                $('.promosi-base-percent').on("keyup", function() {
                    let dInput = parseInt(this.value);
                    let prod_base_price = parseFloat($(this).data('price'));
                    let type = $(this).parent().parent().find("select").children("option:selected").val();
                    let target = $(this).parent().parent().find(".promosi-fix-price")
                    let stock_elm = $(this).parent().parent().find('.promosi-stock');
                    let stock_promosi = parseFloat(stock_elm.val());
                    let total = 0;
                    //error init
                    $(this).siblings('span').html('')
                    //dInput = resolvePrice(dInput);
                    if(dInput){
                        evErrBase(dInput,$(this),type)
                        if (dInput < 0) {
                            dInput = 0;
                            target.val('Rp 0');
                        }else if(dInput > 100){
                            dInput = 100;
                        }
                        total = evBasePrice(dInput,prod_base_price,type,stock_promosi)
                        target.val(total);
                    }

                    //$(".dDimension:contains('" + dInput + "')").css("display","block");
                });

                //event promosi isi harga
                $('.promosi-base-price').on("keyup", function() {
                    let dInput = parseFloat(this.value);
                    let prod_base_price = parseFloat($(this).data('price'));
                    let type = $(this).parent().parent().find("select").children("option:selected").val();
                    let target = $(this).parent().parent().find(".promosi-fix-price")
                    let stock_elm = $(this).parent().parent().find('.promosi-stock');
                    let stock_promosi = parseFloat(stock_elm.val());
                    let total = 0;
                    //error init
                    $(this).siblings('span').html('')
                    if (dInput < 0) {
                        evErrBase(dInput,$(this),type)
                        target.val('Rp 0');
                        //$(this).val(0);
                        //$(this).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input min 0</span>`);
                    }else{
                        total = evBasePrice(dInput,prod_base_price,type,stock_promosi)
                        target.val(total);
                    }
                    //$(".dDimension:contains('" + dInput + "')").css("display","block");
                });

                //event promosi isi harga
                $('.value-promo').on("keyup", function() {
                    let dInput = parseFloat(this.value);
                    $(this).siblings('span').html('')
                    if (dInput < 0) {
                        evErrBase(dInput,$(this),2)
                    }
                });

                //event promosi isi harga .numeric();
                $('.total-bundle').on("keyup", function() {
                    let dInput = parseFloat(this.value);
                    $(this).siblings('span').html('')
                    evErrBase(dInput,$(this),3)
                });

                function evErrBase(val,selector,type){
                    if(type == 1){
                        if (val < 0) {
                            $(selector).val(0);
                            $(selector).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input min 0</span>`);
                        }else if(val > 100){
                            $(selector).val(100);
                            $(selector).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input max 100. </span>`);
                        }
                    }else if(type == 2){
                        if (val < 0) {
                            $(selector).val(0);
                            $(selector).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input min 0</span>`);
                        }
                    }else{
                        let elm_stock_promosi = $('#form-promosi-modal').find('.promosi-stock');
                        let max_stock = 0;
                        let val_stock = 0;
                        let val_floor = 0;
                        let total_max_stock = [];
                        $(elm_stock_promosi).each(function( index ) {
                          //console.log( index + ": " + $( this ).text() );
                          max_stock = parseInt($(this).data('stock'));
                          val_stock = parseInt($(this).val());
                          val_floor = max_stock/val_stock;

                          total_max_stock.push(Math.floor(val_floor));
                          if(val_floor > init_bundle){
                            init_bundle = val_floor;
                          }
                        });

                        for (var i = 0; i < total_max_stock.length; i++) {
                            if (init_bundle > total_max_stock[i]) {
                                init_bundle = total_max_stock[i];
                            }
                        }

                        if (val < 0) {
                            $(selector).val(0);
                            $(selector).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input min 0</span>`);
                            total_max_stock = [];
                        }else if(val > init_bundle){
                            $(selector).val(init_bundle);
                            $(selector).addClass('has-error').after(`<span class="help-inline text-danger">Hanya boleh input max ${init_bundle}. </span>`);
                            total_max_stock = [];
                        }
                    }
                }

                function evBasePrice(val,price,type_val,stock_total){
                    let total = 0;
                    val = parseFloat(val)
                    stock_total = parseInt(stock_total)
                    if(type_val == 1){
                        total = price - (val*price)/100;
                        total = total;
                    }else{
                        total = val;
                    }
                    return 'Rp ' + total.format(2, 3, '.', ',');
                }

                $(".value-promo").inputmask("decimal",{
                    radixPoint:",",
                    groupSeparator: ".",
                    digits: 2,
                    autoGroup: true,
                    negative : false,
                    prefix: 'Rp ',
                    autoUnmask: true
                });
                $(".promosi-stock").inputmask("decimal",{
                    groupSeparator: ".",
                    digits: 0,
                    autoGroup: true,
                    negative : false,
                    prefix: '',
                    autoUnmask: true
                });
                $(".total-bundle").inputmask("decimal",{
                    groupSeparator: ".",
                    digits: 0,
                    autoGroup: true,
                    negative : false,
                    prefix: '',
                    autoUnmask: true
                });
                $(".promosi-base-price").inputmask("decimal",{
                    radixPoint:",",
                    groupSeparator: ".",
                    digits: 2,
                    autoGroup: true,
                    negative : false,
                    prefix: 'Rp ',
                    autoUnmask: true
                });
            }

        elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

        } catch (error) {
            console.error(error);


            toastr.error(error,  { fadeAway: 10000 });

            // $('#form-promosi-modal').modal('hide')

            elm_save_promosi.innerHTML = 'Save';
        }
    }

    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
        }
    }

    function resetForm() {
        $(".dropify-clear").trigger("click");
        for(const elm of elm_form_promosi) {
            elm.value = '';
            if(elm.type == 'select-one') {
                elm.dispatchEvent(new Event("change", {bubbles: true,}));
            }
        }
    }

    async function closeModalPromosi(e) {
        if(e) {
            e.preventDefault();
            $('#form-promosi-modal').modal('hide')
        }
    }

    async function openModalPromosi() {

        elm_modal_header.innerHTML = `<h6>Tambah Promosi</h6>`;

        $('#form-promosi-modal').modal(Object.assign({}, propModalPreventClick, {
            show: true
        }))

        // manage error
        $('#form-promosi input.has-error').removeClass('has-error');
        $('#form-promosi textarea.has-error').removeClass('has-error');
        $('#form-promosi select.has-error').removeClass('has-error');
        $('#form-promosi .help-inline.text-danger').remove()


        resetForm();
        elm_div_type_label.innerHTML = '';
        elm_div_type .innerHTML = '';
        $('#start_date').datepicker('update', new Date());
        $('#end_date').datepicker('update', new Date());
        //date input handler


        if(!isSuccessfullyGettingData.promosi) {
            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken()) })
               const get = Object.assign({}, porpertyPOST(), {
                   method: 'GET'
               })

            try{

                // elm_prod_type_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_warehouse_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_stock_id .innerHTML = elm_choose('Tunggu Sebentar')

                /*
                category  = await(await(await fetch(`{{ env('API_URL') . '/category' }}`, __propsPOST)).json()).data
                product_type  = await(await(await fetch(`{{ env('API_URL') . '/product-type' }}`, __propsPOST)).json()).data
                product_status = await(await(await fetch(`{{ env('API_URL') . '/product-status' }}`, __propsPOST)).json()).data
                brand = await(await(await fetch(`{{ env('API_URL') . '/brand' }}`, __propsPOST)).json()).data
                principle = await(await(await fetch(`{{ env('API_URL') . '/principle' }}`, __propsPOST)).json()).data
                */

                // elm_prod_type_id.innerHTML = elm_choose('Pilih')
                elm_warehouse_id.innerHTML = elm_choose('Pilih')
                elm_stock_id.innerHTML = elm_choose('Pilih')


                // for (const data of product_type) {
                //     let newOption = ___createOpt(data.id, data.product_type);
                //     elm_prod_type_id.appendChild(newOption);
                //     elm_prod_type_id.dispatchEvent(new Event("change", {bubbles: true,}));
                // }

                for (const data of WAREHOUSES) {
                    let newOption = ___createOpt(data.id, data.short + '-' + data.name);
                    elm_warehouse_id.appendChild(newOption);
                    elm_warehouse_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }


                //for (const data of stock) {
                //    let newOption = ___createOpt(data.id, data.product.prod_name);
                //    elm_stock_id.appendChild(newOption);
                //    elm_stock_id.dispatchEvent(new Event("change", {bubbles: true,}));
                //}

                elm_stock_id.disabled = true

            } catch(err) {
                console.error(err)
            }

            isSuccessfullyGettingData.promosi = true
        }
    }


    // ----------- event listener----------------

    elm_btn_refresh_promosi.addEventListener('click', refreshPromosiDT)
    elm_open_modal.addEventListener('click', openModalPromosi);


    //elm_prod_base_price.addEventListener('keyup', onProdBasePrice)
    //elm_prod_base_price.addEventListener('click', preventPositiveNumber('prod_base_price'))

    elm_warehouse_id.addEventListener('change', evWarehouse);
    $('#warehouse_id').on('select2:select', evWarehouse);

    elm_stock_id.addEventListener('change', evStock);
    $('#stock_id').on('select2:select', evStock);

    elm_promosi_type.addEventListener('change', evPromo);
    $('#promosi_type').on('select2:select', evPromo);

    elm_close_promosi.addEventListener('click', closeModalPromosi);

    $('.select2-selection__choice__remove').on('click', function(){
        evStock();
    });


    $('#data-table tbody').on('click', '#delete-btn', function () {
        const data = table.row( $(this).parents('tr') ).data();

        __swalConfirmation('Apakah anda yakin ?', 'Apakah anda yakin ingin menghapusnya ?', data.id)

    });

    $('#data-table tbody').on('click', '#edit-btn', function () {

        elm_waiting_for_fetch_data.classList.replace('inactive', 'active');
        elm_modal_header.innerHTML = `<h6>Edit Promosi</h6>`;

        // elm_prod_type_id.innerHTML = elm_choose('Pilih')
        elm_warehouse_id.innerHTML = elm_choose('Pilih')
        elm_stock_id.innerHTML = elm_choose('Pilih')


        // for (const data of product_type) {
        //     let newOption = ___createOpt(data.id, data.product_type);
        //     elm_prod_type_id.appendChild(newOption);
        //     elm_prod_type_id.dispatchEvent(new Event("change", {bubbles: true,}));
        // }


        for (const data of WAREHOUSES) {
            let newOption = ___createOpt(data.id, data.short + '-' + data.name);
            elm_warehouse_id.appendChild(newOption);
            elm_warehouse_id.dispatchEvent(new Event("change", {bubbles: true,}));
        }

        const dataTable = table.row( $(this).parents('tr') ).data();
        elm_promosi_id.value = dataTable.id;


        for (const key in dataTable) {
            for(const elm of elm_form_promosi) {
                if(key == elm.name) {
                    if(elm.type === 'select-one') {
                        elm.value = dataTable[key];
                        elm.dispatchEvent(new Event("change", {bubbles: true,}));
                    }
                    if(elm.type == 'text' || elm.type == 'hidden' || elm.type == 'textarea') {
                        elm.value = dataTable[key];
                    }
                    if(elm.type == 'number') {
                        elm.value = dataTable[key] ? parseInt(dataTable[key]) : dataTable[key];
                    }
                    if(elm.type == 'time') {
                        elm.value = dataTable[key];
                    }
                    if(elm.type == 'file') {
                        var imagenUrl = `{{ env('API_URL') . '/' }}${dataTable[key]}`;
                        // var drEvent = $('#promosi_image').dropify(
                        // {
                        //   defaultFile: imagenUrl
                        // });
                        // drEvent = drEvent.data('dropify');
                        // drEvent.resetPreview();
                        // drEvent.clearElement();
                        // drEvent.settings.defaultFile = imagenUrl;
                        // drEvent.destroy();
                        // drEvent.init();

                        $("#promosi_image").attr("data-default-file", imagenUrl);
                        // $('.dropify').dropify();

                        var drEvent = $('.dropify').dropify({
                        messages: {
                            default: 'Drag atau drop untuk memilih gambar',
                            replace: 'Ganti',
                            remove:  'Hapus',
                            error:   'error'
                            },
                            defaultFile: imagenUrl
                        });

                        drEvent.on('change', function(event, element){
                            if(__querySelectorAll('.dropify-wrapper.has-error').length) {
                                elm_save_promosi.disabled = true;
                            } else {
                                elm_save_promosi.disabled = false;
                            }
                        });


                        drEvent.on('dropify.fileReady', function(event, element){
                            elm_save_promosi.disabled = false;
                        });

                        drEvent.on('dropify.errors', function(event, element){
                            elm_save_promosi.disabled = true;
                        });


                        drEvent = drEvent.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = imagenUrl;
                        drEvent.destroy();
                        drEvent.init();


                        elm_save_promosi.disabled = false;


                    }
                }
            }
        }

        // manage error
        $('#form-promosi input.has-error').removeClass('has-error');
        $('#form-promosi textarea.has-error').removeClass('has-error');
        $('#form-promosi select.has-error').removeClass('has-error');
        $('#form-promosi .help-inline.text-danger').remove()

        $('#form-promosi-modal').modal(Object.assign({}, propModalPreventClick, {
            show: true
        }))

        elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

    });

    async function __swalConfirmation(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin menghapusnya ?', id) {
        return swal({
            title: title,
            text: text,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then(async (willDelete) => {
            if (willDelete) {

                try {
                    let res = await fetch(`{{ env('API_URL') . '/promosi/delete/${id}' }}`, Object.assign({}, customPost({method: 'DELETE',id:elm_promosi_id.value}), {
                        method: 'DELETE'
                    }))

                    if(res.status) {
                        refreshPromosiDT();
                        toastr.success("Berhasil delete promosi", { fadeAway: 10000 });
                    } else {
                        toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
                        //console.error(message)
                    }
                } catch (error) {
                    //console.error(error);

                }

            }
        })
    }

    if($('#start-time-sd').length) {
        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());


        $('#start-time-sd').datetimepicker({
            format: 'H:mm',
            sideBySide: true,
            icons:
            {
                up: 'fa fa-angle-up',
                down: 'fa fa-angle-down'
            },
            }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('H:mm');
                //log(moment(e.date).format('H:mm').valueOf())
                const ms = moment(e.date,'YYYY-MM-DD').valueOf()
                $('#start-time-sd-tmp').val(ms);

                let ttl_start = $('#start-time-sd-tmp').val();
                let ttl_end = $('#end-time-sd-tmp').val();

                $("#start-time-sd").val(formatedValue)
            }
        });
    }


    $('#end-time-sd').datetimepicker({
            format: 'H:mm',
            sideBySide: true,
            icons:
            {
                up: 'fa fa-angle-up',
                down: 'fa fa-angle-down'
            },
            }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('H:mm');
                $("#end-time-sd").val(formatedValue)
            }
    });


})
</script>

@endsection
