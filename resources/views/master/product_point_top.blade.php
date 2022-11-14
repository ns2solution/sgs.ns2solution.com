@extends('layouts.app')

@section('title', '| Produk Terlaris')
@section('breadcrumb', 'Dashboard  /  Produk Point /  Produk Point Terlaris')

@section('content')
<style>
    input{
        text-align: left !important;
    }
    .waiting-for-fetch-data{
        position: fixed;
        background: rgb(0 0 0 / 0.53);
        height: 100%;
        width: 100vw;
        top: 0;
        font-weight: 500;
        z-index: 999 !important;
        font-size: 24px;
        color: #fff;
        justify-content: center;
        align-items: center;
        left: 0;
    }
    .waiting-for-fetch-data.active{
        display: flex;
    }
    .waiting-for-fetch-data.inactive{
        display:none;
    }
    table#wrap-order-items-2 tbody:first-child tr:first-child {
        display: none;
    }
</style>

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

    Sedang memuat data ...
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Produk Point Terlaris</h5>
					<button class="btn p-0" type="button" id="btn-refresh-product">
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
							<th>No. Produk</th>
							<th>Nama Produk</th>
                            <th>Sub Kategori</th>
                            <!-- <th>HPD</th> -->
							<th>Point</th>
							<th>Berat</th>
                            <th>Principle</th>
                            <!-- <th>Diskon</th> -->
                            <!-- <th>Harga Diskon</th> -->
                            <th>Brand</th>
                            <!-- <th>Type</th> -->
							<th>Created At</th>
							<th>Created By</th>
							<th>Updated At</th>
							<th>Updated By</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<script>
    function bulkModal() {
        $('.modal-header').html(`
            <h6>Tambah Bulk Produk Point</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="">
                <span aria-hidden="true">&times;</span>
            </button>
        `)

        $('.dropify-error').empty();
        
        $('#form-bulk-modal').modal('show')
    }


    let elm_waiting_for_fetch_data = __getId('waiting-for-fetch-data')




    $(document).ready(async function () {
        
        
            var drEvent = $('#bulk_file').dropify({
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
                    elm_btn_upload_bulk.disabled = true;
                } else {
                    elm_btn_upload_bulk.disabled = false;
                }
            });

            drEvent.on('dropify.afterClear', function(event, element){
                elm_btn_upload_bulk.disabled = true;
            });

            drEvent.on('dropify.fileReady', function(event, element){
                elm_btn_upload_bulk.disabled = false;
            });

            drEvent.on('dropify.errors', function(event, element){
                elm_btn_upload_bulk.disabled = true;
                log(element)
                log(event)
                setTimeout(() => {
                    __querySelector('.dropify-errors-container').lastElementChild.firstElementChild.innerText = 'File tidak sesuai, format didukung ( xls, xlsx).'
                });
            });


        $('#form-bulk').on('submit', function(e){
            e.preventDefault();
            
            let data = new FormData(document.getElementById('form-bulk'));
            data.append('token', '{{ Session::get("_token")}}')
            data.append('email', '{{ Session::get("email")}}')
            elm_btn_upload_bulk.innerHTML = 'Uploading ' + ___iconLoading();
            elm_btn_upload_bulk.disabled = true;

            $.ajax({
                type:'post',
                url:"{{ env('API_URL') }}/product-point/excel",
                contentType: false,
                processData: false,
                data:data,
                success:function(res){
                    console.log(res)
                    $('#form-bulk-modal').modal('hide')
                    if (res.status) {
                        elm_btn_upload_bulk.innerHTML = 'Upload';
                        elm_btn_upload_bulk.disabled = false;
                        toastr.success(res.message, { fadeAway: 10000 });
                    }else{
                        elm_btn_upload_bulk.innerHTML = 'Upload';
                        elm_btn_upload_bulk.disabled = false;
                        toastr.error(res.message, { fadeAway: 10000 });

                    }

                    $('#data-table').DataTable().ajax.reload();
                },
                error:function(err){
                    console.log(err)
                    $('#form-bulk-modal').modal('hide')
                    toastr.error(err.responseJSON.message, { fadeAway: 10000 });
                    elm_btn_upload_bulk.innerHTML = 'Upload';
                    elm_btn_upload_bulk.disabled = false;
                }
            })
        })


        function tambahGambar() {
            const rmvbtn = document.querySelectorAll('.dropify') ?  document.querySelectorAll('.dropify') : null ;

            if(rmvbtn.length <= 5) {
                $('.save').removeAttr('disabled')
                
                $('#clone-tag-dropify').append(`<div class="col-md-4">
                                                        <button type="button" class="btn btn-icon remove-btn" onclick="removeDiv()" style="position: absolute; top: 10px; z-index: 999; left: 20px; background: red; display: flex; color: #fff; align-items: center; justify-content: center;">✘</button>
                                                        <input type="file" name="path[]" class="myfrm form-control dropify" data-height="" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                                                </div>`)

            } else {
                $('.save').attr('disabled','disabled')
            }
            $('.dropify').dropify()
        }


        // ----------- variable ---------------------

        let elm_btn_upload_bulk = __getId('btn-upload-bulk')

        let elm_prod_id = __getId('id')
        let elm_prod_type_id = __getId('prod-type-id')
        let elm_prod_status_id = __getId('prod-status-id')
        let elm_brand_id = __getId('brand-id')
        let elm_principle_id = __getId('principle-id')
        let elm_category_id = __getId('category-id')
        let elm_sub_category_id = __getId('sub-category-id')
        let elm_prod_modal_price = __getId('prod-modal-price')
        let elm_prod_base_price = __getId('prod-base-price')
        let elm_prod_gram = __getId('prod-gram')
        let elm_diskon = __getId('diskon')
        let elm_prod_description = __getId('prod_description')
        let elm_form_product = __getId('form-product')
        let elm_save_product = __getId('save-product')
        let elm_close_product = __getId('close-product')
        let elm_close_upload_bulk = __getId('close-upload-bulk')
        let elm_btn_refresh_product = __getId('btn-refresh-product')
        let elm_modal_header = __getId('modal-header')
        let elm_wrap_product_image = __getId('wrap-product-image')
        let elm_warehouse_id = __getId('warehouse-id')
        let elm_warehouse_id_pending = __getId('warehouse-id-pending')
        let elm_stock = __getId('stock')
        let elm_warehouse_field = __getId('warehouse-field')
        let elm_stock_field = __getId('stock-field')

        $("#prod-base-price" ).inputmask();
        $("#prod-modal-price" ).inputmask();

        const isSuccessfullyGettingData = {
            product: false
        }


        let table = null
        let elm_open_modal = __getId('open-modal-product')
        let product_type = [], product_status = [], brand = [], principle = [], category = [], sub_category  = [], warehouse = [], stock = []


        var drEvent = $('.dropify').dropify({
            messages: {
                default: 'Drag atau drop untuk memilih gambar',
                replace: 'Ganti',
                remove:  'Hapus',
                error:   'error'
            }
        });

        drEvent.on('dropify.fileReady', function(event, element){
            elm_save_product.disabled = false;
        });

        drEvent.on('dropify.errors', function(event, element){
            elm_save_product.disabled = true;
        });

        drEvent.on('dropify.beforeClear', function(event, element){
            return alert("Do you really want to delete \"" + element.filename + "\" ?");
        });

        drEvent.on('dropify.afterClear', function(event, element){
            elm_save_product.disabled = true;
        });

        // ----------- fetch data ------------------


        $.fn.dataTable.ext.errMode = 'none';

        table = $('#data-table').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [[2, 'DESC']],
            ajax:{
                url: "{{ env('API_URL') . '/product-point/data-table-top-product-point' }}",
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
                    render: function(_,__,___){
                        log(_,__,___);
                      
                        return `
                        <label class="pure-material-checkbox">
                            <input type="checkbox" ${___.is_top_product_point ? 'checked' : ''} class="checked"/>
                        </label>

                        `;
                    }
                },
                {
                    data: 'no',
                    orderable: false,
                },
                {
                    data: 'id',
                },
                {
                    data: 'prod_number'
                },
                {
                    data: 'prod_name',
                    width: '25%'
                },
                {
                    data: 'sub_category_name'
                },
                // {
                //     data: 'prod_modal_price'
                // },
                {
                    data: 'prod_base_point'
                },
                {
                    data: 'prod_gram'
                },
                {
                    data: 'c_principle_name'
                },
                // {
                //     data: 'diskon'
                // },
                // {
                //     data: 'harga_diskon'
                // },
                {
                    data: 'd_brand_name'
                },
                // {
                //     data: 'prod_type_id',
                //     sClass: 'text-center',
                //     render: function(data){
                //         return (data == 1) ? 'Reguler' : 'Promo';
                //     }
                // },
                {
                    data: 'created_at',
                    sClass: 'text-center'
                },
                {
                    data: 'created_by',
                    sClass: 'text-center'
                },
                {
                    data: 'updated_at',
                    sClass: 'text-center'
                },
                {
                    data: 'updated_by',
                    sClass: 'text-center'
                }
            ]
        });




        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), {
            by : "{{ Session::get('user')->id }}"
        }
        ))})
        const __propsGET = Object.assign({}, {
            headers: __headers(),
            method: 'GET'
        })

        function customPost(data){
            return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data
                ))})
        }


        // category  = await(await(await fetch(`{{ env('API_URL') . '/category/all' }}`, __propsPOST)).json()).data
        // product_type  = await(await(await fetch(`{{ env('API_URL') . '/product-type' }}`, __propsPOST)).json()).data
        // product_status = await(await(await fetch(`{{ env('API_URL') . '/product-status' }}`, __propsPOST)).json()).data
        // brand = await(await(await fetch(`{{ env('API_URL') . '/brand' }}`, __propsPOST)).json()).data
        // principle = await(await(await fetch(`{{ env('API_URL') . '/principle' }}`, __propsPOST)).json()).data
        // warehouse  = await(await(await fetch(`{{ env('API_URL') . '/warehouse' }}`, __propsGET)).json()).data;

        //debugger;

        // ----------- init ---------------------

        $('select').select2();

        $('select').on('select2:close', function (e) {
            $(this).valid();
        });


        const msg_dropify_default = {
            messages: {
                default: 'Drag atau drop untuk memilih gambar',
                replace: 'Ganti',
                remove:  'Hapus',
                error:   'error'
            }
        }

        const rulesForm = {
            rules: {
                //prod_number : 'required',
                prod_name : 'required',
                prod_modal_price : 'required',
                prod_base_price : 'required',
                prod_gram : 'required',
                principle_id : 'required',
                category_id : 'required',
                sub_category_id : 'required',
                prod_description : 'required',
                prod_type_id : 'required',
                prod_status_id : 'required',
                brand_id : 'required',
                min_poin : 'required',
                elm_diskon : 'required',
            },
            ...rulesValidateGlobal,
            submitHandler:(form, e) => {
                e.preventDefault();

                const id =  elm_prod_id.value ? elm_prod_id.value : null;

                if(id) {
                    updateProduct(e, id)
                } else {
                    saveProduct(e)
                }

                return false;
            }
        }

        $('#form-product').submit((e) => {
            e.preventDefault();
        }).validate(rulesForm);


        // ----------- function ---------------------


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

        function onProdStock(e) {
            let value = parseInt(e.target.value);
            if(value < 0){
                elm_stock.value = 0;
            }
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
                    case 'prod_stock' :
                        if(elm_stock.value < 0) {
                            elm_stock.value = 0;
                        }
                    break;
                }
            }
        }

        async function saveProduct(e) {
            if(e) {
                e.preventDefault();
            }
            // json stringify
            // let formData = __serializeForm(elm_form_product);
            // const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())
            let formData = new FormData(elm_form_product);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')
            // process save to server
            elm_save_product.innerHTML = 'Saving ' + ___iconLoading();
            elm_save_product.disabled = true;

            try {

                // const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(formData) })

                // let res = await fetch(`{{ env('API_URL') . '/product/store' }}`, __propsPOST)

                // let result = await res.json();

                // const {status, message, data} = result;

                // if(status) {

                //     refreshProductDT();

                //     toastr.success(message, { fadeAway: 10000 });
                //     elm_save_product.innerHTML = 'Save';
                //     elm_save_product.disabled = false;

                //     $('#form-product-modal').modal('hide')

                // } else {
                //     elm_save_product.disabled = false;
                //     elm_save_product.innerHTML = 'Save';

                //     error(message);

                //     $('#form-product-modal').modal('hide')
                // }
                $.ajax({
                    url:`{{ env('API_URL') . '/product-point/store' }}`,
                    method:"POST",
                    data: formData,
                    dataType:'JSON',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(result){
                        const {status, message, data} = result;


                        if(status) {

                            refreshProductDT();

                            toastr.success(message, { fadeAway: 10000 });
                            elm_save_product.innerHTML = 'Simpan';
                            elm_save_product.disabled = false;

                            $('#form-product-modal').modal('hide')
                        } else {
                            elm_save_product.disabled = false;
                            elm_save_product.innerHTML = 'Simpan';

                            error(message);

                            $('#form-product-modal').modal('hide')
                        }
                    },
                    error: function(err) {
                        log(err);
                        const msg = err.responseJSON.message;

                        elm_save_product.disabled = false;

                        toastr.error(msg,  { fadeAway: 10000 });

                        elm_save_product.innerHTML = 'Simpan';

                    }
                })
            } catch (error) {
                error(error);
                toastr.error(error,  { fadeAway: 10000 });

                elm_save_product.disabled = false;

                $('#form-product-modal').modal('hide')

                elm_save_product.innerHTML = 'Save';
            }

        }

        async function updateProduct(e, id) {
            if(e) {
                e.preventDefault();
            }
            // json stringify
            // let formData = __serializeForm(elm_form_product);
            // const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())
            let formData = new FormData(elm_form_product);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')
            // process save to server
            elm_save_product.innerHTML = 'Saving ' + ___iconLoading();
            elm_save_product.disabled = true;

            try {

                // const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(formData) })

                // let res = await fetch(`{{ env('API_URL') . '/product/update/${id}' }}`, __propsPOST)

                // let result = await res.json();

                // const {status, message, data} = result;

                // if(status) {

                //     refreshProductDT();

                //     toastr.success(message, { fadeAway: 10000 });
                //     elm_save_product.innerHTML = 'Save';
                //     elm_save_product.disabled = false;


                //     $('#form-product-modal').modal('hide')


                // } else {
                //     elm_save_product.disabled = false;
                //     elm_save_product.innerHTML = 'Save';

                //     error(message);

                //     $('#form-product-modal').modal('hide')
                // }
                $.ajax({
                    url:`{{ env('API_URL') . '/product-point/update/${id}' }}`,
                    method:"POST",
                    data: formData,
                    dataType:'JSON',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(result){
                        const {status, message, data} = result;


                        if(status) {

                            refreshProductDT();

                            toastr.success(message, { fadeAway: 10000 });
                            elm_save_product.innerHTML = 'Simpan';
                            elm_save_product.disabled = false;

                            $('#form-product-modal').modal('hide')
                        } else {
                            elm_save_product.disabled = false;
                            elm_save_product.innerHTML = 'Simpan';

                            error(message);

                            $('#form-product-modal').modal('hide')
                        }
                    },
                    error: function(err) {
                        log(err);
                        const msg = err.responseJSON.message;

                        toastr.error(msg,  { fadeAway: 10000 });

                        elm_save_product.innerHTML = 'Simpan';

                    }
                })
            } catch (error) {
                error(error);
                toastr.error(error,  { fadeAway: 10000 });

                $('#form-product-modal').modal('hide')

                elm_save_product.innerHTML = 'Save';
            }

        }

        function refreshProductDT(e) {
            if(e) {
                e.preventDefault()
            }
            table.ajax.reload();
        }

        async function evType(e) {
            e.preventDefault()

            const id = e.params ? e.params.data.id : e.target.value;
            const id_prod = elm_prod_id.value;
            const id_warehouse = elm_warehouse_id.value;

            if(id == 2) {
                //reset option
                elm_warehouse_field.hidden = false;
                elm_stock_field.hidden = false;
                elm_warehouse_id.innerHTML = elm_choose('Pilih');
                
                for (const data of warehouse) {
                    let newOption = ___createOpt(data.id, `${data.short} - ${data.name}`);
                    elm_warehouse_id.appendChild(newOption);
                    //data stock
                }
            }else{
                elm_warehouse_field.hidden = true;
                elm_stock_field.hidden = true;
                elm_stock.value = 0;
                elm_warehouse_id.value = null;
                elm_warehouse_id.dispatchEvent(new Event("change", {bubbles: true,}));
            }
        }

        function getProductData(product_id){
            
            
            var res = new Promise(async function(resolve, reject){
                const product = await fetch(`{{ env('API_URL') . '/product-point/get-data' }}`, customPost({id:product_id}))
                let product_data = await product.json();

                resolve(product_data);
            })

            let response = res.then(function(result) {
                     return result // "resolve"
                });
            return response;
        }

        function fillWarehouse(product_data){
            
        }

        function evCategory(e) {
            e.preventDefault()

            const id = e.params ? e.params.data.id : e.target.value;

            if(id) {
                elm_sub_category_id.disabled = false;
                elm_sub_category_id.innerHTML = elm_choose('Pilih');
                for (const data of category) {
                    if(data.parent_id == id) {
                        let newOption = ___createOpt(data.id, data.category_name);
                        elm_sub_category_id.appendChild(newOption);
                        elm_sub_category_id.dispatchEvent(new Event("change", {bubbles: true,}));
                    } else {
                    }
                }
            } else {
                elm_sub_category_id.disabled = true;
                elm_sub_category_id.innerHTML = elm_choose('Pilih')
            }
        }

        function evPrinciple(e) {
            e.preventDefault()

            const id = e.params ? e.params.data.id : e.target.value;

            if(id) {
                elm_brand_id.disabled = false;
                elm_brand_id.innerHTML = elm_choose('Pilih');
                for (const data of brand) {
                    if(data.principle_id == id) {
                        let newOption = ___createOpt(data.id_brand, data.brand_name);
                        elm_brand_id.appendChild(newOption);
                        elm_brand_id.dispatchEvent(new Event("change", {bubbles: true,}));
                    }
                }

                // $.ajax({
                //     type:'post',
                //     url:"{{ env('API_URL') }}/brand",
                //     data:{
                //         email : `{{ Session::get("email")}}`,
                //         token: `{{ Session::get("token")}}`,
                //         by : "{{ Session::get('user')->id }}",
                //         principle:id
                //     },
                //     success:function(res){
                //         if (res.data != '') {
                //             let html = ''
                //             $.each(res.data, function(key, value){
                //                 html += `<option value="${value.id_brand}">${value.brand_name}</option>`
                //             })
                //             $('#brand-id').empty().append(html)
                //         }
                //     },
                //     error:function(err){
                //         console.log(err)
                //     }
                // })
            } else {
                elm_brand_id.disabled = true;
                elm_brand_id.innerHTML = elm_choose('Pilih')
            }
        }


        function getCurrentToken() {
            return {
                email : `{{ Session::get("email")}}`,
                token: `{{ Session::get("token")}}`,
                by : "{{ Session::get('user')->id }}"
            }
        }

        function resetForm() {
            for(const elm of elm_form_product) {
                elm.value = '';
                if(elm.type == 'select-one') {
                    elm.dispatchEvent(new Event("change", {bubbles: true,}));
                }
            }
        }


        async function closeModalProduct(e) {
            if(e) {
                e.preventDefault();
                $('#form-product-modal').modal('hide')
            }
        }

        async function closeModalUploadBulk(e) {
            if(e) {
                e.preventDefault();
                $('#form-bulk-modal').modal('hide')
            }
        }
        

        async function openModalProduct() {

            elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

            elm_modal_header.innerHTML = `<h6>Tambah Produk</h6>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="">
                <span aria-hidden="true">&times;</span>
            </button>`;

            elm_wrap_product_image.innerHTML = `
                <label>Upload File</label>
                <div class="row" id="clone-tag-dropify">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button class="btn btn-icon" type="button" id="btn-add-file" style="position: absolute; top: 10px; z-index: 999; left: 20px; background: #03a9f4; color: #fff;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        </button>
                    </div>
                    <div class="col-md-4">
                        <input type="file" name="path[]" class="myfrm form-control dropify" data-height="" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M" id="init-dropify"/ >
                    </div>
                </div>
            `

            $('.dropify-error').empty();

            var drEvent = $('.dropify').dropify({
                messages: {
                    default: 'Drag atau drop untuk memilih gambar',
                    replace: 'Ganti',
                    remove:  'Hapus',
                    error:   'error'
                }
            });

            const init_dropify = __getId('init-dropify')
            const elm_btn_add_file = __getId('btn-add-file')
            const clone_tag_dropify = __getId('clone-tag-dropify')

            // ---- event listener for handling add / delete image -----

            let no_btn = 0;
            elm_btn_add_file.addEventListener('click', (e) => {
                e.preventDefault();

                const rmvbtn = document.querySelectorAll('.remove-btn') ?  document.querySelectorAll('.remove-btn') : null ;

                if(document.querySelectorAll('.remove-btn')?.length <= 4) {

                    elm_btn_add_file.disabled = false;
                    
                    clone_tag_dropify.insertAdjacentHTML('beforeend', `
                        <div class="col-md-4">
                                <button type="button" class="btn btn-icon remove-btn" title="Button ${no_btn++} " style="position: absolute; top: 10px; z-index: 999; left: 20px; background: red; display: flex; color: #fff; align-items: center; justify-content: center;">✘</button>
                                <input type="file" name="path[]" class="myfrm form-control dropify" data-height="" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                        </div>
                    `);

                    for(const elm_remove of document.querySelectorAll('.remove-btn')) {
                        elm_remove.addEventListener('click', newHandle, true)
                    }

                } else {
                    elm_btn_add_file.disabled = true;
                }

                var drEvent = $('.dropify').dropify(Object.assign({},msg_dropify_default));


                function newHandle(e) {
                    if(e) {
                        e.preventDefault();
                        e.target.parentElement.remove();
                        elm_btn_add_file.disabled = false;
                    }
                }

            }) 
            // end event listen button add file

            const rmvbtn = document.querySelectorAll('#clone-tag-dropify .remove-btn') ?  document.querySelectorAll('#clone-tag-dropify .remove-btn') : null ;

            if(rmvbtn) {
                for(const elm of rmvbtn) {
                    elm.parentElement.remove();
                }
                __getId('btn-add-file').disabled = false;
            }

            resetForm();

            // manage error
            $('#form-product input.has-error').removeClass('has-error');
            $('#form-product textarea.has-error').removeClass('has-error');
            $('#form-product select.has-error').removeClass('has-error');
            $('#form-product .help-inline.text-danger').remove()


            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken()) })
            const get = Object.assign({}, porpertyPOST(), {
                method: 'GET'
            })

            try{

                elm_prod_type_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_prod_status_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_brand_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_principle_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_category_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_sub_category_id.innerHTML = elm_choose('Tunggu Sebentar')

                elm_prod_type_id.innerHTML = elm_choose('Pilih')
                elm_prod_status_id.innerHTML = elm_choose('Pilih')
                elm_brand_id.innerHTML = elm_choose('Pilih')
                elm_principle_id.innerHTML = elm_choose('Pilih')
                elm_category_id.innerHTML = elm_choose('Pilih')
                elm_sub_category_id.innerHTML = elm_choose('Pilih')


                for (const data of product_type) {
                    let newOption = ___createOpt(data.id, data.product_type);
                    elm_prod_type_id.appendChild(newOption);
                    elm_prod_type_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }

                for (const data of product_status) {
                    let newOption = ___createOpt(data.id, data.status_name);
                    elm_prod_status_id.appendChild(newOption);
                    elm_prod_status_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }

                for (const data of brand) {
                    let newOption = ___createOpt(data.id_brand, data.brand_name);
                    elm_brand_id.appendChild(newOption);
                    elm_brand_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }

                for (const data of principle) {
                    let newOption = ___createOpt(data.id, data.name);
                    elm_principle_id.appendChild(newOption);
                    elm_principle_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }


                for (const data of category) {
                    if(data.parent_id == 0) {
                        let newOption = ___createOpt(data.id, data.category_name);
                        elm_category_id.appendChild(newOption);
                        elm_category_id.dispatchEvent(new Event("change", {bubbles: true,}));
                    }
                }


                elm_sub_category_id.disabled = true

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

                $('#form-product-modal').modal(Object.assign({}, propModalPreventClick, {
                    show: true
                }))

            } catch(err) {
                console.error(err)
            }



        }



        // event listener

        log(__querySelectorAll('.checked'));

        for(i=0; i < __querySelectorAll('.checked').length; i++ ) {

        __querySelectorAll('.checked')[i].addEventListener('change', function() {
            if (this.checked) {
                console.log("Checkbox is checked..");
            } else {
                console.log("Checkbox is not checked..");
            }
        });

        }

        $('#data-table tbody').on('click', '.checked', function () {

            const data          = table.row( $(this).parents('tr') ).data();
            const product_id    = data.id; 

            this.addEventListener('change', async function() {

            if (this.checked) {

                const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), {
                        product_point_id : product_id,
                        active: 1,
                    }
                ))});

                const product_top  = await(await fetch(`{{ env('API_URL') . '/top-product-point/update' }}`, __propsPOST)).json();
            
            } else {
            
                const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), {
                        product_point_id : data.id,
                        active: 2,
                    }
                ))});
            
                const product_top  = await(await fetch(`{{ env('API_URL') . '/top-product-point/update' }}`, __propsPOST)).json();
            }
        });
 

            // __swalConfirmation('Apakah anda yakin ?', 'Apakah anda yakin ingin menghapusnya ?', data.id)

        });

        $('#data-table tbody').on('click', '#edit-btn', async function () {

            elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

            const data = table.row( $(this).parents('tr') ).data();

            //debugger;

            elm_modal_header.innerHTML = `<h6>Edit Produk</h6>
            
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="">
                    <span aria-hidden="true">&times;</span>
                </button>`;

            elm_wrap_product_image.innerHTML = `
                    <label>Upload File</label>
                    <div class="col-md-12 d-flex justify-content-end">
                        <button class="btn btn-icon" type="button" id="btn-add-file" style="position: absolute; top: 10px; z-index: 999; left: 20px; background: #03a9f4; color: #fff;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        </button>
                    </div>
                    <div id="clone-tag-dropify" class="clone-tag-dropify row">
                    </div>
                `

            $('.dropify-error').empty();

            var drEvent = $('.dropify').dropify({
            messages: {
                default: 'Drag atau drop untuk memilih gambar',
                replace: 'Ganti',
                remove:  'Hapus',
                error:   'error'
                }
            });

            drEvent.on('dropify.fileReady', function(event, element){
                elm_save_product.disabled = false;
            });

            drEvent.on('dropify.errors', function(event, element){
                elm_save_product.disabled = true;
            });

            drEvent.on('dropify.beforeClear', function(event, element){
                return alert("Do you really want to delete \"" + element.filename + "\" ?");
            });

            drEvent.on('dropify.afterClear', function(event, element){
                elm_save_product.disabled = true;
            });



            const init_dropify = __getId('init-dropify')
            const elm_btn_add_file = __getId('btn-add-file')
            const clone_tag_dropify = __getId('clone-tag-dropify')

            // ---- event listener for handling add / delete image -----

            let no_btn = 0;
            
            elm_btn_add_file.addEventListener('click', (e) => {
                e.preventDefault();

                const rmvbtn = document.querySelectorAll('.remove-btn') ?   document.querySelectorAll('.remove-btn') : null;
                const drpwrp = document.querySelectorAll('.dropify-wrapper.has-preview') ? document.querySelectorAll('.dropify-wrapper.has-preview') : null;

                let total = 0;
                if(rmvbtn?.length && drpwrp?.length) {
                    total = ( rmvbtn?.length + drpwrp?.length )
                } else if(rmvbtn?.length) {
                    total = ( rmvbtn?.length )
                } else if(drpwrp?.length) {
                    total = ( drpwrp?.length )
                }
                
                log(total);

                if(total <= 5 ) {

                    elm_btn_add_file.disabled = false;
                    
                    clone_tag_dropify.insertAdjacentHTML('beforeend', `
                        <div class="col-md-4">
                                <button type="button" class="btn btn-icon remove-btn" title="Button ${no_btn++} " style="position: absolute; top: 10px; z-index: 999; left: 20px; background: red; display: flex; color: #fff; align-items: center; justify-content: center;">✘</button>
                                <input type="file" name="path[]" class="myfrm form-control dropify" data-height="" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                        </div>
                    `);

                    for(const elm_remove of document.querySelectorAll('.remove-btn')) {
                        elm_remove.addEventListener('click', newHandle, true)
                    }

                } else {
                    elm_btn_add_file.disabled = true;
                }

                var drEvent = $('.dropify').dropify(Object.assign({},msg_dropify_default));


                function newHandle(e) {
                    if(e) {
                        e.preventDefault();
                        e.target.parentElement.remove();
                        elm_btn_add_file.disabled = false;
                    }
                }

            })
            // end event listen button add file

            const rmvbtn = document.querySelectorAll('#clone-tag-dropify .remove-btn') ?  document.querySelectorAll('#clone-tag-dropify .remove-btn') : null ;

            if(rmvbtn) {
                for(const elm of rmvbtn) {
                    elm.parentElement.remove();
                }
                __getId('btn-add-file').disabled = false;
            }

            resetForm();

            // manage error
            $('#form-product input.has-error').removeClass('has-error');
            $('#form-product textarea.has-error').removeClass('has-error');
            $('#form-product select.has-error').removeClass('has-error');
            $('#form-product .help-inline.text-danger').remove()


            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken()) })
            const get = Object.assign({}, porpertyPOST(), {
                method: 'GET'
            })
            
            try {
                
                elm_prod_type_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_prod_status_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_brand_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_principle_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_category_id.innerHTML = elm_choose('Tunggu Sebentar')
                elm_sub_category_id.innerHTML = elm_choose('Tunggu Sebentar')


                elm_prod_status_id.innerHTML = elm_choose('Pilih')
                elm_brand_id.innerHTML = elm_choose('Pilih')
                elm_principle_id.innerHTML = elm_choose('Pilih')
                elm_category_id.innerHTML = elm_choose('Pilih')
                elm_sub_category_id.innerHTML = elm_choose('Pilih')
                elm_prod_type_id.innerHTML = elm_choose('Pilih');


                for (const data of product_type) {
                    let newOption = ___createOpt(data.id, data.product_type);
                    elm_prod_type_id.appendChild(newOption);
                    elm_prod_type_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }

                for (const data of product_status) {
                    let newOption = ___createOpt(data.id, data.status_name);
                    elm_prod_status_id.appendChild(newOption);
                    elm_prod_status_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }
                
                for (const data of brand) {
                    let newOption = ___createOpt(data.id_brand, data.brand_name);
                    elm_brand_id.appendChild(newOption);
                    elm_brand_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }

                for (const data of principle) {
                    let newOption = ___createOpt(data.id, data.name);
                    elm_principle_id.appendChild(newOption);
                    elm_principle_id.dispatchEvent(new Event("change", {bubbles: true,}));
                }

                for (const data of category) {
                    if(data.parent_id == 0) {
                        let newOption = ___createOpt(data.id, data.category_name);
                        elm_category_id.appendChild(newOption);
                        elm_category_id.dispatchEvent(new Event("change", {bubbles: true,}));
                    }
                }

                // find image product
                
                elm_prod_id.value = data.id;

                const product_id = data.id;

                const type_id = data.prod_type_id;

                const product_image  = await(await(await fetch(`{{ env('API_URL') . '/product-point/image/${product_id}' }}`, __propsPOST)).json()).data

                let wrap_elm;
                if (product_image != '') {


                    for(const path of product_image) {
                        __getId('clone-tag-dropify').insertAdjacentHTML('beforeend', `
                            <div class="col-md-4">
                                <input type="file" class="myfrm form-control dropify" data-height="" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M" id="init-dropify-${path.id}"/ >
                            </div>
                        `)

                        var drEvent = $(`#init-dropify-${path.id}`).dropify({
                            defaultFile: `{{ env('API_URL') }}${path.path}`,
                        });

                        drEvent = drEvent.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = `{{ env('API_URL') }}/${path.path}`;
                        drEvent.id = `${path.id}`;
                        drEvent.destroy();
                        drEvent.init();


                        var drEventNew = $('#init-dropify-'+path.id).dropify();

                        drEventNew.on('dropify.beforeClear', function(ev, elm){
                            return confirm("Apakah anda yakin ingin hapus gambar ini ?");
                        });

                        drEventNew.on('dropify.afterClear', function(ev, elm){
                            log(elm)
                            $.ajax({
                                type:'delete',
                                url:"{{ env('API_URL') . '/product-point/image/' }}"+path.id,
                                data:{
                                    token: "{{ Session::get('token') }}",
                                    email: "{{ Session::get('email') }}",
                                },
                                success:function(res){
                                    console.log(res)
                                    $('#init-dropify-'+path.id).parent().parent().remove();
                                    elm_btn_add_file.disabled = false;
                                },
                                error:function(err){
                                    console.log(err)
                                }
                            })
                        });

                    }
                }else{
                    __getId('clone-tag-dropify').insertAdjacentHTML('beforeend', `
                        <div class="col-md-4">
                            <input type="file" name="path[]" class="myfrm form-control dropify" data-height="" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M" id="init-dropify-0"/ >
                        </div>
                    `)

                    $('.dropify').dropify()
                }

                //elm_stock_id
                

                for (const key in data) {
                    for(const elm of elm_form_product) {
                        if(key == elm.name) {
                            if(elm.type === 'select-one') {
                                log(key, elm.name, data[key]);
                                elm.value = data[key];
                                elm.dispatchEvent(new Event("change", {bubbles: true,}));
                            }
                            if(elm.type == 'text' || elm.type == 'hidden' || elm.type == 'textarea') {
                                elm.value = data[key];
                            }
                            if(elm.type == 'number') {
                                elm.value = data[key] ? parseInt(data[key]) : data[key];
                            }
                        }   
                    }
                }

                
                let product_data = await getProductData(product_id)
                for(const data of product_data.data.stock){
                    log(data.warehouse_id)
                    elm_warehouse_id.value = data.warehouse_id;
                    elm_warehouse_id.dispatchEvent(new Event("change"));
                    elm_warehouse_id_pending.value = data.warehouse_id;
                    elm_stock.value = data.stock;
                    break;
                }

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

                $('#form-product-modal').modal(Object.assign({}, propModalPreventClick, {
                    show: true
                }))



            } catch (err) {
                console.error(err);
            }


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

                        let res = await fetch(`{{ env('API_URL') . '/product-point/delete/${id}' }}`, Object.assign({}, __propsPOST, {
                            method: 'DELETE'
                        }))

                        let result = await res.json();

                        const {status, message} = result;

                        if(status) {
                            refreshProductDT();
                            toastr.success(message, { fadeAway: 10000 });
                        } else {
                            toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
                            console.error(message)
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }
            })
        }

    })



</script>

@endsection