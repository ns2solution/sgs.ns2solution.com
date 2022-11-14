@extends('layouts.app')

@section('title', '| Promosi')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Promosi')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float"  id="open-modal-brand" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Promosi&nbsp;

</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Promosi </h5>
					<button class="btn p-0" type="button" id="btn-refresh-brand">
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
                            <th>Nama Promo</th>
							<th>Gambar Promo</th>
							<th>Tipe Promo</th>
							<th>Tanggal Mulai</th>
							<th>Tanggal Berakhir</th>
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

<div class="modal fade bd-example-modal-lg" id="form-brand-modal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    	<div class="modal-header" id="modal-header">
    	</div>
    	<div class="modal-body">
	      	<form class="cmxform" id="form-promosi" action="#" enctype="multipart/form-data">
				<fieldset>
                    <div class="form-group">
						<input type="hidden" id="id" >
                    </div>
                    <div class="form-group">
                        <input type="file" name="logo" id="brand-logo" class="dropify"  data-height="150" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                    </div>
                    <div class="row">
	                    <div class="form-group col-md-6">
							<label>Nama Promo</label>
							<input id="promosi_name" class="form-control" name="promosi_name" type="text" minlength="3">
						</div>
	                    <div class="form-group col-md-6">
							<label>Warehouse</label>
							<select class="form-control select2" name="warehouse_id" id="warehouse_id">
								<option>1</option>
								<option>2</option>
								<option>3</option>
							</select>
						</div>
                    </div>
                    <div class="row">
	                    <div class="form-group col-md-6">
							<label>Tanggal Mulai</label>
							<input id="start_date" class="form-control" name="start_date" type="text" minlength="3">
						</div>
	                    <div class="form-group col-md-6">
							<label>Tanggal Berakhir</label>
							<input id="end_date" class="form-control" name="end_date" type="text" minlength="3">
						</div>
                    </div>
                    <div class="form-group">
						<label>Produk</label>
						<select class="form-control" id="produk_sku" name="produk_sku[]" multiple="multiple">
						</select>
					</div>
                    <div class="form-group">
						<label>Tipe Promo</label>
						<select class="form-control" id="promosi_type" name="promosi_type" onchange="changeType(this.value)">
							<option>-- Pilih Tipe Promosi --</option>
							<option value="1">Promo</option>
							<option value="2">Bundling</option>
						</select>
					</div>
					<div class="row" id="divType">
						
					</div>
					<div class="modal-footer">
						<button class="btn btn-danger" type="button" id="close-brand"> Close</button>
                        <button class="btn btn-primary" type="submit" id="save-brand"> Save</button>
					</div>
				</fieldset>
			</form>
    	</div>
    </div>
  </div>
</div>


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


        // ----------- variable ---------------------

        $('.dropify').dropify();
        $('.select2').select2();
        let table = null
        let elm_open_modal = __getId('open-modal-brand')

        // ----------- fetch data ------------------

        $.fn.dataTable.ext.errMode = 'none';

        table = $('#data-table').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            autoWidth: true,
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
                    data: 'id_brand'
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
                    data: 'promosi_name',
                    width: '40%'
                },
                {
                    data: 'promosi_type',
                    sClass: 'text-center'
                },
                {
                    data: 'start_date',
                    sClass: 'text-center'
                },
                {
                    data: 'end_date',
                    sClass: 'text-center'
                },
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


        // ----------- init ---------------------

        const rulesForm = {
            rules: {
                promosi_name : 'required',
                promosi_type : 'required',
                start_date : 'required',
                end_date : 'required',
            },
            ...rulesValidateGlobal,
            submitHandler:(form, e) => {
                e.preventDefault();

                const id =  $('#id').val() ? $('#id').val() : null;

                if(id) {
                    updatePromosi(e, id)
                } else {
                    savePromosi(e)
                }

                return false;
            }
        }

        $('#form-brand').submit((e) => {
            e.preventDefault();
        }).validate(rulesForm);


         // ----------- function ---------------------




         async function savePromosi(e) {
            if(e) {
                e.preventDefault();
            }


            let formData = new FormData(elm_form_brand);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')

            $.ajax({
                url:`{{ env('API_URL') . '/promosi/create' }}`,
                method:"POST",
                data: formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(result){
                    const {status, message, data} = result;


                    if(status) {

                        refreshBrandDT();

                        toastr.success(message, { fadeAway: 10000 });
                        elm_save_brand.innerHTML = 'Save';
                        elm_save_brand.disabled = false;

                        $('#form-brand-modal').modal('hide')
                    } else {
                        elm_save_brand.disabled = false;
                        elm_save_brand.innerHTML = 'Save';

                        error(message);

                        $('#form-brand-modal').modal('hide')
                    }
                },
                error: function(err) {
                    log(err);
                    const msg = err.responseJSON.message;

                    toastr.error(msg,  { fadeAway: 10000 });

                    elm_save_brand.innerHTML = 'Save';

                }
            })


        }



        function getCurrentToken() {
            return {
                email : `{{ Session::get("email")}}`,
                token: `{{ Session::get("token")}}`,
                by : "{{ Session::get('user')->id }}"
            }
        }

        async function openModalBrand() {

            // elm_modal_header.innerHTML = '<h6>Tambah Brand</h6>'

            $('#form-brand-modal').modal(Object.assign({}, propModalPreventClick, {
                show: true
            }))

            $.ajax({
            	type:'get',
            	url:"{{ env('API_URL') .'/product' }}",
            	data:getCurrentToken(),
            	success:function(res){
            		console.log(res)
        			let html = '';
            		if (res.data != '') {
            			$.each(res.data, function(key, value){
            				html += `<option value="${value.id}">${value.prod_name}</option>`
            			})
            			$('#produk_sku').empty().append(html)
            			$('#produk_sku').select2()
            		}else{
            			$('#produk_sku').empty().append(`<option>No Data</option>`)

            		}
            	},
            	error:function(err){
            		console.log(err)
            	}
            })


            // manage error

            $('.dropify-error').empty();
            //$('.dropify-errors-container').empty();

            $('#start_date').datepicker({format: "dd/mm/yyyy", todayHighlight: true, autoclose: true});
            $('#end_date').datepicker({format: "dd/mm/yyyy", todayHighlight: true, autoclose: true});

            elm_save_brand.disabled = false;
            elm_save_brand.removeAttribute('disabled', '')
        }


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

                        let res = await fetch(`{{ env('API_URL') . '/brand/delete/${id}' }}`, Object.assign({}, __propsPOST, {
                            method: 'DELETE'
                        }))

                        let result = await res.json();

                        const {status, message} = result;

                        if(status) {
                            refreshBrandDT();
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

        function refreshBrandDT(e) {
            if(e) {
                e.preventDefault()
            }
            table.ajax.reload();
        }

        // ----------- event listener----------------

        // elm_btn_refresh_brand.addEventListener('click', refreshBrandDT)
        elm_open_modal.addEventListener('click', openModalBrand);
        // elm_close_brand.addEventListener('click', closeModalBrand);


        $('#data-table tbody').on('click', '#delete-btn', function () {
            const data = table.row( $(this).parents('tr') ).data();

            __swalConfirmation('Apakah anda yakin ?', 'Apakah anda yakin ingin menghapusnya ?', data.id_brand)

        });


        $('#data-table tbody').on('click', '#zoom-foto', function () {
            const data = table.row( $(this).parents('tr') ).data();
            let img = data.brand_logo

            __querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

            $('#modal-view').modal('show');
        });

        $('#data-table tbody').on('click', '#edit-btn', function () {
            // manage error
            $('.dropify-error').empty();
            $('.dropify-errors-container').empty();

            elm_modal_header.innerHTML = '<h6>Edit Brand</h6>'

            resetForm();

            const data = table.row( $(this).parents('tr') ).data();

            elm_brand_id.value = data.id_brand;

            for (const key in data) {
                for(const elm of elm_form_brand) {
                    if(key == elm.name) {
                        if(elm.type == 'text' || elm.type == 'hidden' || elm.type == 'textarea') {
                            elm.value = data[key];
                        }
                    }
                    if(key == 'brand_logo' && elm.name == 'logo') {

                        var drEvent = $('#brand-logo').dropify({
                            defaultFile: `{{ env('API_URL') . '/' }}${data[key]}`,
                        });

                        drEvent = drEvent.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = `{{ env('API_URL') . '/' }}${data[key]}`;
                        drEvent.destroy();
                        drEvent.init();

                        $('.dropify-render > img').attr('src', `{{ env('API_URL') . '/' }}${data[key]}`);

                    }
                }
            }


            elm_save_brand.disabled = false;


            $('#form-brand-modal').modal(Object.assign({}, propModalPreventClick, {
                show: true
            }))



        });



    });

	function changeType(val){
        let html = '';
     	if (val == 1) {
            
     		html += `<div class="form-group col-md-4">
							<label>Promo Harga Produk</label>
						</div>
						<div class="form-group col-md-4">
							<label>Type</label>
						</div>
						<div class="form-group col-md-4">
							<label>Harga Setelah Promosi</label>
						</div>`
     		$.each($('#produk_sku').val(), function(key, value){

                //ajax request product
                
         		html += `<div class="form-group col-md-4">
							${value}
						</div>
						<div class="form-group col-md-4">
							<select class="form-control">
								<option value="1">Persentase</option>
								<option value="2">Fix</option>
							</select>
						</div>
						<div class="form-group col-md-4">
							<input id="value" class="form-control" name="value" type="text" minlength="3">
						</div>`;
     		})
     		$('#divType').empty().append(html)
     	}else{
            html = '';
            html += `<div class="form-group col-md-4">
						<label>Bundeling Harga Produk</label>
					</div>`
            html += `
			        <div class="form-group col-md-4">
			        	<input id="value" class="form-control" name="value" type="text" minlength="3">
			        </div>`;
                    
            $('#divType').empty().append(html)
     	}
     }


</script>

@endsection
