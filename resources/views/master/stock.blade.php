@extends('layouts.app')

@section('title', '| Stok Produk')
@section('breadcrumb', 'Dashboard  /  Produk  /  Stok Produk')

@section('content')


<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float3" onclick="openModalBulk()">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>
    Update Stock Bulk
</button>

<button class="btn btn-success btn-icon-text px-3 px-lg-4 float3" style="right: 40px" id="download-bulk">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>
    Export Stock Produk
</button>


<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" @if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat') style="padding:1.35rem 1.5rem 0rem 1.5rem;" @else style="padding:1rem 1.5rem .3rem 1.5rem;" @endif>
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Stok Produk </h5>
					<div style="display:inline-flex;">
						<div @if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat') style="display:none;" @endif>
							<select class="form-control select" id="warehouse">
								@foreach($warehouse as $a)
									<option value="{{ $a->id }}"> {{ $a->code . ' - ' . $a->name . ' (' . $a->short . ')' }} </option>
								@endforeach
							</select>
							&nbsp;&nbsp;&nbsp;&nbsp;
						</div>
						<button class="btn p-0" type="button" onclick="$('#dataTable').DataTable().ajax.reload(null, false);">
	                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
	                    </button>
	                </div>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="dataTable" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>Aksi</th>
							<th>#</th>
							<th>ID</th>
							<th>Warehouse</th>
							<th>No. Produk</th>
							<th>Nama Produk</th>
							<th>Stok</th>
							<th>Principle</th>
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

<div class="modal fade" id="modalEdit" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="border:none;">
			<form class="cmxform" id="stock-form" method="get" action="#" enctype="multipart/form-data">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Tambah Stok Produk</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group" style="margin-bottom:0px;">
						<input type="hidden" name="warehouse_id" id="warehouse_id">
					</div>
					<div class="row">
						<div class="col-3 col-lg-3" id="div_id">
							<div class="form-group">
								<label> ID </label>
								<input type="text" class="form-control" id="id" name="id" style="cursor:default;" readonly>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label> Nama Produk </label>
								<input type="text" class="form-control" id="name" name="name" style="cursor:default;" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> Stok </label>
						<div style="display:flex;">
							<button type="button" class="btn btn-primary btn-custom mr-3" id="stock_minus"> - </button>
							<input type="text" class="form-control" id="stock" name="stock" autocomplete="off" placeholder="0" data-mask="000.000.000" data-mask-reverse="true" style="text-align:center;" title="">
							<button type="button" class="btn btn-primary btn-custom ml-3" id="stock_plus"> + </button>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal"> Batal </button>
					<button type="submit" class="btn btn-primary btn-custom"> Simpan </button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade bd-example-modal-lg" id="form-bulk-modal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header" id="modal-header">

          </div>
          <div class="modal-body">
              <form class="cmxform" id="form-bulk">
                  <fieldset>
                      {{-- <div class="form-group">
                          <a href="{{ asset('template.xlsx') }}" target="_blank" type="button" class="btn btn-primary">Download Template BULK</a>
                      </div> --}}
                      <div class="form-group">
                          <input type="file" name="bulk_file" id="bulk_file" class="form-control" data-height="150" data-errors-position="outside" data-allowed-file-extensions="xlsx">
                      </div>
                      <div class="modal-footer">
                          <button class="btn btn-danger" type="button" id="close-mdl"> Close</button>
                          <button class="btn btn-primary" type="submit" id="save-bulk"> Upload</button>
                      </div>
                  </fieldset>
              </form>
          </div>
      </div>
    </div>
  </div>

<!-- <a href="" id="target-blank-bulk-file" target="_blank">Download Bulk</a> -->

@endsection

@section('js')

<script>



	$(document).ready(function(){
		@if(Session::get('user')->wh_id)
			$('#warehouse').val("{{ Session::get('user')->wh_id }}").change();
			$('#warehouse_id').val("{{ Session::get('user')->wh_id }}");
		@else
			$('#warehouse').val('0').change();
			$('#warehouse_id').val('0');
        @endif

        var drEvent = $('#bulk_file').dropify({
            messages: {
                default: 'Drag atau drop untuk memilih file',
                replace: 'Ganti',
                remove:  'Hapus',
                error:   'error'
            }
        });
        drEvent.on('change', function(event, element){
            log('changeg');
            dir(__querySelectorAll('.dropify-wrapper.has-error'));
            if(__querySelectorAll('.dropify-wrapper.has-error').length) {
                elm_save_bulk.disabled = true;
            } else {
                elm_save_bulk.disabled = false;
            }
        });

        drEvent.on('dropify.afterClear', function(event, element){
            elm_save_bulk.disabled = true;
        });

        drEvent.on('dropify.fileReady', function(event, element){
            log('file ready')
            elm_save_bulk.disabled = false;
        });

        drEvent.on('dropify.errors', function(event, element){
            log('file error')
            elm_save_bulk.disabled = true;
        });


    });








    let elm_target_blank_bulk_file = __getId('target-blank-bulk-file');
    let elm_close_mdl = __getId('close-mdl')
    let elm_save_bulk = __getId('save-bulk')
    let elm_form_bulk = __getId('form-bulk')

    let elm_download_bulk = __getId('download-bulk')

    elm_close_mdl.addEventListener('click', closeModalBulk);
    elm_download_bulk.addEventListener('click', exportData);

    async function closeModalBulk(e) {
        if(e) {
            e.preventDefault();
            $('#form-bulk-modal').modal('hide')
        }
    }


	$('#warehouse').on('change', function(){
		$('#warehouse_id').val(this.value);
		if(this.value != ''){
			$('#dataTable').DataTable().destroy();
			fDataTable();
		}
    });

    function openModalBulk(){
        $('.modal-header').html('Update Stock Bulk')
        // manage error
        $('#form-bulk input.has-error').removeClass('has-error');
        $('#form-bulk textarea.has-error').removeClass('has-error');
        $('#form-bulk select.has-error').removeClass('has-error');
        $('#form-bulk .help-inline.text-danger').remove()


        $('.dropify-error').empty();
        $('.dropify-errors-container').empty();

        resetForm();

        elm_save_bulk.disabled = true;

        $('#form-bulk-modal').modal('show')
    }



    function resetForm() {
        var drEvent = $('#bulk_file').dropify();
        drEvent = drEvent.data('dropify');
        drEvent.resetPreview();
        drEvent.clearElement();

        for(const elm of elm_form_bulk) {
            elm.value = '';

        }
    }

    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
            by : "{{ Session::get('user')->id }}",
        }
    }

    $('#form-bulk').on('submit', function(e){
        e.preventDefault();


        elm_save_bulk.innerHTML = 'Mengupload ' + ___iconLoading();
        elm_save_bulk.disabled = true;

        let data = new FormData(document.getElementById('form-bulk'));
        data.append('token', '{{ Session::get("token")}}')
        data.append('email', '{{ Session::get("email")}}')
        data.append('by', '{{ Session::get("user")->id }}')
        data.append('warehouse_id', $('#warehouse').val())

        $.ajax({
            type:'post',
            url:"{{ env('API_URL') }}/stock/import",
            contentType: false,
            processData: false,
            data:data,
            success:function(result){

                const {status, message, data} = result;

                if (status) {

                    refreshStockProductDT();

                    toastr.success(message, { fadeAway: 10000 });
                    elm_save_bulk.innerHTML = 'Upload';
                    elm_save_bulk.disabled = false;

                    $('#form-bulk-modal').modal('hide')

                }else{
                    elm_save_bulk.disabled = false;
                    elm_save_bulk.innerHTML = 'Upload';

                    toastr.error(message, { fadeAway: 10000 });

                }

            },
            error:function(err){
                const msg = err && err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'File yang lama terganti dengan yang baru, harap upload kembali';


                toastr.error(msg,  { fadeAway: 10000 });

                elm_save_bulk.innerHTML = 'Upload';
                elm_save_bulk.disabled = false;
            }
        })
    })


    async function exportData(e) {
        if(e) {
            e.preventDefault();
        }

        elm_download_bulk.innerHTML = 'Proses ' + ___iconLoading();
        elm_download_bulk.disabled = true;

        let data = new FormData();
        data.append('token', '{{ Session::get("token")}}')
        data.append('email', '{{ Session::get("email")}}')
        data.append('by', '{{ Session::get("user")->id }}')
        data.append('warehouse_id', $('#warehouse').val())

        $.ajax({
            url:`{{ env('API_URL') . '/stock/export' }}`,
            method:"POST",
            data: data,
            dataType:'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success:function(result){
                const {status, message, data} = result;


                if(status) {

                    refreshStockProductDT();

                    toastr.success(message, { fadeAway: 10000 });
                    elm_download_bulk.innerHTML = __iconPlus() + ' Export Stock Product';
                    elm_download_bulk.disabled = false;

                    // elm_target_blank_bulk_file.href = data;
                    // elm_target_blank_bulk_file

                    window.open(data, '_blank');

                } else {
                    elm_download_bulk.disabled = false;
                    elm_download_bulk.innerHTML = __iconPlus() + ' Export Stock Product';

                    log(message);
                }
            },
            error: function(err) {
                log(err);
                const msg = err.responseJSON.message;

                toastr.error(msg,  { fadeAway: 10000 });

                elm_save_brand.innerHTML = 'Simpan';

            }
        })


    }


    function refreshStockProductDT(e) {
        if(e) {
            e.preventDefault()
        }
        table.ajax.reload(null, false);
    }


	var table = null;

	function fDataTable(){

		$.fn.dataTable.ext.errMode = 'none';
		table = $('#dataTable').DataTable({
			pageLength: 10,
			processing: true,
			serverSide: true,
			order: [[2, 'ASC']],
			ajax:{
				url: "{{ env('API_URL') . '/stock/data-table' }}",
				dataType: 'JSON',
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					token : "{{ Session::get('token') }}",
					email : "{{ Session::get('email') }}",
					_wh   : $('#warehouse').val()
				}
			},
			columns: [
				{
					sClass: 'text-center',
	                orderable: false,
	                render: function(){
	                	return `
	                		&nbsp;
		        			<button class="btn p-0" type="button" id='btnTambah'>
	                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
	                    	</button>
	                    	&nbsp;
		        		`;
	                }
				},
				{
					data: 'no',
					sClass: 'text-center',
	                orderable: false,
	                width: '25px'
				},
				{
					data: 'id',
					width: '35px',
					sClass: 'text-center'
				},
				{
					data: 'warehouse',
					sClass: 'text-center',
					orderable: false
				},
				{
					data: 'prod_num'
				},
				{
					data: 'produk',
					width: '40%',
					render: function(data, _, __){
						let color = '', type;

						if(__.prod_type_id == 1){
							color = 'badge-secondary';
							type = 'R';
						}else{
							color = 'badge-warning';
							type = 'P';
						}

						return `${data} <span class="badge badge-sm ${color}" style="color:white">${type}</span>`;
					}
				},
				{
					data: 'stock',
					width: '10%',
					sClass: 'text-center',
					orderable: false,
					render: function(data){
						var color = '';
						if(data != 0){
							color = 'badge-primary';
						}else{
							color = 'badge-secondary';
						}

						return `<span class="badge badge-pill ${color}" style="padding:5px 8px 5px 8px;font-size:12px;"> ${_dotFormat(data)} </span>`;
					}
				},
				{
					data: 'principle'
				},
				{
					data: 'created_at',
					sClass: 'text-center',
					orderable: false
				},
				{
					data: 'created_by',
					sClass: 'text-center',
					orderable: false
				},
				{
					data: 'updated_at',
					sClass: 'text-center',
					orderable: false
				},
				{
					data: 'updated_by',
					sClass: 'text-center',
					orderable: false
				}
			]
		});
	}

	{{-- Edit --}}

	$('#dataTable').on('click', '#btnTambah', function(){

		var data = table.row($(this).parents('tr')).data();

		$('#id').val(data.id);
		$('#name').val(data.produk);
		$('#stock').val(data.stock).trigger('input');

		checkStock(data.stock);

		$('#modalEdit').modal('show');

	});

	{{-- Submit Form --}}

	$('#stock-form').on('submit', function(e){

		e.preventDefault();

		_sendRequest('stock/update', 'POST', null, false, 'dataTable', null, 'stock-form');

		$('#modalEdit').modal('hide');

	});

	{{-- Button Plus Minus --}}

	$('#stock_minus').click(function(){
		calcStock('-');
	});

	$('#stock_plus').click(function(){
		calcStock('+');
	});

	$('#stock').on('input', function(){

		if(this.value.startsWith('0') === true){
			this.value = this.value.substring(1);
		}

		checkStock(this.value.replace(/\./g,''));

	});

	function calcStock(type){

		let GET_STOCK = $('#stock').val().replace(/\./g,'');
		let NOW_STOCK = null;

		GET_STOCK == '' ? GET_STOCK = 0 : '';

		switch(type){

			case '-':
				NOW_STOCK = parseInt(GET_STOCK) - 1;
				break;

			case '+':
				NOW_STOCK = parseInt(GET_STOCK) + 1;
				break;

		}

		checkStock(NOW_STOCK);

		return $('#stock').val(NOW_STOCK).trigger('input');

	}

	function checkStock(stock){

		stock = parseInt(stock);

		if(stock == 0 || isNaN(stock)){
			$('#stock_minus').prop('disabled', true);
		}else{
			$('#stock_minus').removeAttr('disabled');
		}

		if(stock == 999999999){
			$('#stock_plus').prop('disabled', true);
		}else{
			$('#stock_plus').removeAttr('disabled');
		}

	}

</script>

@endsection
