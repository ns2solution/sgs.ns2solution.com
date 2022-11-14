@extends('layouts.app')

@section('title', '| Kurir')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Kurir')

@section('style')

<style>
.card.active{
	border: 1px solid #2a8fcc;
}
	.switch {
		position: relative;
		display: inline-block;
		width: 40px;
		height: 24px;
	}
	.switch input {
		opacity: 0;
		width: 0;
		height: 0;
	}
	.slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		-webkit-transition: .3s;
		transition: .3s;
	}
	.slider:before {
		position: absolute;
		content: "";
		height: 16px;
		width: 16px;
		left: 4px;
		bottom: 4px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
	}
	input:checked + .slider {
		background-color: #2A8FCC;
	}
	input:focus + .slider {
		box-shadow: 0 0 1px #2A8FCC;
	}
	input:checked + .slider:before {
		-webkit-transform: translateX(16px);
		-ms-transform: translateX(16px);
		transform: translateX(16px);
	}
	.slider.round {
		border-radius: 34px;
	}
	.slider.round:before {
		border-radius: 50%;
	}
	.card:active, .card:focus{
		BORDER: 1PX SOLID #2a8fcc;
	}
	.card{
		MARGIN-BOTTOM:10px;
	}

</style>

@endsection

@section('content')

@if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat')
@else
	<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" style="font-size:17px;" onclick="tambahCourier()">
	    &nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
	    Tambah Kurir&nbsp;
	</button>
@endif

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient @if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat') float @else float2 @endif" style="font-size:17px;" onclick="editJasa()">
	&nbsp;<i class="link-icon" data-feather="toggle-left"></i>&nbsp;
	Pengaturan Kurir&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Kurir </h5>
					<button class="btn p-0" type="button" onclick="$('#dataTable').DataTable().ajax.reload(null, false);">
                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                    </button>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="dataTable" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>Aksi</th>
							<th>#</th>
							<th>ID</th>
							<th>Logo</th>
							<th>Kode</th>
							<th>Nama</th>
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
			<form class="cmxform" id="courier-form" method="get" action="#" enctype="multipart/form-data">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Edit Detail Kurir</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label> Logo </label>
						<input type="file" name="p_logo" id="logo" class="dropify" data-height="150" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg" data-max-file-size="2M" accept=".png, .jpeg, .jpg">
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
								<label> Kode </label>
								<input type="text" class="form-control" id="code" name="code" autocomplete="off" maxlength="20" placeholder="Masukkan kode kurir" required>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> Nama Kurir </label>
						<input type="text" class="form-control" id="name" name="name" autocomplete="off" placeholder="Masukkan nama kurir" maxlength="45" required>
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

<div class="modal fade" id="modalSetting" tabindex="-1">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content" style="border:none;">
			<form class="cmxform" id="setting-form" method="get" action="#" enctype="multipart/form-data">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Pengaturan Kurir</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div @if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat') style="display:none;" @endif>
						<input type="hidden" name="warehouse_id" id="warehouse_id">
						<select class="form-control select" id="warehouse" style="width:100%">
							@foreach($warehouse as $a)
								<option value="{{ $a->id }}"> {{ $a->code . ' - ' . $a->name . ' (' . $a->short . ')' }} </option>
							@endforeach
						</select>
						&nbsp;&nbsp;&nbsp;&nbsp;
					</div>
					<div class="row">
						<div class="col-7">
							<div class="modal-body-2">
								{{-- JS --}}
							</div>
						</div>
						<div class="col">
							<div class="modal-body-3" id="modal-body-3">
								{{-- JS --}}
							</div>
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

<div class="modal fade" id="modalView" tabindex="-1">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content" data-dismiss="modal" style="border:none;background-color:transparent;">
			<div class="modal-body">
				<div class="form-group">
					<center>
						<img src="" style="max-height:80vh;max-width:100%;" draggable="false" id="imgView">
					</center>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')

<script>

	@if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat')
		const _BTN_ACTION = false;
	@else
		const _BTN_ACTION = true;
	@endif



	function getCurrentToken() {
			return {
				email : `{{ Session::get("email")}}`,
				token: `{{ Session::get("token")}}`,
				by : "{{ Session::get('user')->id }}"
			}
		}


	const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})


	function customPost(data){
		return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data))})
	}

	$.fn.dataTable.ext.errMode = 'none';
	var table = $('#dataTable').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		order: [[2, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/courier/data-table' }}",
			dataType: 'JSON',
			type: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				token : "{{ Session::get('token') }}",
				email : "{{ Session::get('email') }}"
			}
		},
		columns: [
			{
				sClass: 'text-center',
                orderable: false,
                visible: _BTN_ACTION,
                render: function(){
                	return `
	        			<button class="btn p-0" type="button" id='btnEdit'>
                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    	</button>
                    	&nbsp;&nbsp;
	        			<button class="btn p-0" type="button" id='btnDelete'>
                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#FF3366" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    	</button>
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
				data: 'logo',
				sClass: 'text-center',
                orderable: false,
				width: '20%',
				render: function(data){
					if(data != '-'){
						return `<img src="{{ env('API_URL') . '/' }}${data}" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();" onclick="zoomFoto(1, this);">`;
					}else{
						return '-';
					}
				}
			},
			{
				data: 'code',
				sClass: 'text-center'
			},
			{
				data: 'name',
				width: '50%'
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

	{{-- Edit --}}

	$('#dataTable').on('click', '#btnEdit', function(){

		var data = table.row($(this).parents('tr')).data();

		$('#div_id').show();
		$('#modalEdit').find('.modal-title').empty().append('Edit Detail Kurir');

		$('#id').val(data.id);
		$('#name').val(data.name);
		$('#code').val(data.code);

		$('.dropify-error').empty();
        $('.dropify-errors-container').empty();

		changeImageDropify('#logo', "{{ env('API_URL') . '/' }}" + data.logo);

		$('#modalEdit').modal('show');

	});

	{{-- Add --}}

	function tambahCourier(){

		$('#div_id').hide();
		$('#div_cb').hide();
		$('#modalEdit').find('.modal-title').empty().append('Tambah Kurir');

		$('#id').val('');
		$('#code').val('');
		$('#name').val('');

		removeImageDropify('#logo');

		$('#modalEdit').modal('show');

	}

	{{-- Submit Form --}}

	$('#courier-form').on('submit', function(e){

		e.preventDefault();

		var url = '';
		if($('#id').val() != ''){
			url = 'courier/update/' + $('#id').val();
		}else{
			url = 'courier/create';
		}

		_sendRequest(url, 'POST', null, false, 'dataTable', null, 'courier-form');

	});

	{{-- Delete --}}

	$('#dataTable').on('click', '#btnDelete', function(){

		var data = table.row($(this).closest('tr')).data();
		var id   = data[Object.keys(data)[1]],
			name = data[Object.keys(data)[4]];

		DATA_MODAL_YES_NO = {
			id: id,
			by: "{{ Session::get('user')->id }}"
		};

		_modalYesNo('Hapus Kurir', 'Apakah Anda yakin kurir ' + name + ' dengan id ' + id + ' akan dihapus?');

	});

	$('#modalYesNo-form').on('submit', function(e){

		e.preventDefault();
		$('#modalYesNo').modal('toggle');

		_sendRequest('courier/delete', 'POST', DATA_MODAL_YES_NO, false, 'dataTable');

	});

	{{-- Zoom Image --}}

	function zoomFoto(type, sel){

		var data = table.row($(sel).closest('tr')).data();

		var title = 'Logo';
		var img  = data[Object.keys(data)[2]];

		$('#modalView').find('.modal-title').empty().append(title);

		$('#imgView').attr('src', "{{ env('API_URL') . '/' }}" + img);

		$('#modalView').modal('show');

	}

	{{-- Define --}}

	$('#logo').dropify({
		messages: {
			default: 'Drag atau drop untuk memilih gambar',
			replace: 'Ganti',
			remove:  'Hapus',
			error:   ''
		}
	});

	function changeImageDropify(sel, url){
		var dr = $(sel).dropify();
		dr = dr.data('dropify');
		dr.resetPreview();
		dr.clearElement();
		dr.settings.defaultFile = url;
		dr.destroy();

		return dr.init();
	}

	function removeImageDropify(sel){
		var dr = $(sel).dropify();
		dr = dr.data('dropify');
		dr.resetPreview();

		return dr.clearElement();
	}

	{{-- Pengaturan Kurir --}}

	$(document).ready(function(){
		@if(Session::get('user')->wh_id)
			$('#warehouse').val("{{ Session::get('user')->wh_id }}").change();
			$('#warehouse_id').val("{{ Session::get('user')->wh_id }}");
		@else
			$('#warehouse').val('0').change();
			$('#warehouse_id').val('0');
        @endif
    });

    function editJasa(){
		__getId('modal-body-3').innerHTML = '';
    	$('#modalSetting').modal('show');
    	getDataSetting();
    }
	var currentId = null;

	function getDataSetting(){

		__getId('modal-body-3').innerHTML = '';

		$('#modalSetting').find('.modal-body-2').empty();

		$.get("{{ env('API_URL') }}/setting_courier/get/" + $('#warehouse_id').val(), function(data){

			$.each(data.data, function(index, item){

				var _checked = '';

				if(item.value === 'on'){
					_checked = 'checked';
				}

				var _html = `
					<div class="card" id="card_${index}" style="display:none;" value="${item.courier_id}">
						<div class="card-body" id="card_body_${index}" style="pointer-events:none;padding:10px">
							<table style="pointer-events:none;">
								<tr>
									<td>
										<img src="{{ env('API_URL') }}/${item.courier_logo}" style="width:50px;" draggable="false">
									</td>
									<td style="width:100%;">
										<c class="ml-4">
											${item.courier_name}
										</c>
									</td>
									<td style="pointer-events:auto;">
										<label class="switch" style="margin-bottom:0px;">
											<input type="checkbox" name="cb_${item.courier_id}" ${_checked}>
											<span class="slider round"></span>
										</label>
									</td>
								</tr>
							</table>
						</div>
					</div>
				`;

				$('#modalSetting').find('.modal-body-2').append(_html);

				eventListener(`card_${index}`, async (e) => {
					
					currentId = e.target.id;

					for(const elm of __querySelectorAll('.card')) {
						if(elm.id !== currentId) {
							elm.classList.remove('active');			
						} else {
							elm.classList.add('active')
						}
					}

					const courier_id = e.target.id == `card_${index}` ? e.target.attributes[3].value : null;
					__getId('modal-body-3').innerHTML = `<div class='container d-flex justify-content-center'>${___iconLoading('black')}</div>`;
					
					if(courier_id) {

						try {

							const wh_id = __getId("warehouse_id").value;
							const service_courier = await(await(await fetch(`{{ env('API_URL') . '/setting_courier/service/get/${courier_id}/${wh_id}' }}`)).json()).data		
							
							
							if(service_courier) {

								__getId('modal-body-3').innerHTML = '';

								for (const item of service_courier) {
						

									let _serviceAktif = '';

									if(item.value === 'on'){
										_serviceAktif = 'checked';
									}

									
									insertAdjHTML('modal-body-3', 'afterbegin', `
										<table style="pointer-events:none;">
											<tr>
												<td style="width:100%;">
													<c class="ml-4">
														${item.courier_service_code} (${item.courier_service_name})
													</c>
												</td>
												<td style="pointer-events:auto;">
													<label class="switch" style="margin-bottom:0px;">
														<input type="checkbox" name="cb_courier_service_${item.courier_service_id}" ${_serviceAktif}>
														<span class="slider round"></span>
													</label>
												</td>
											</tr>
										</table>
									`)



									__querySelector(`input[name="cb_courier_service_${item.courier_service_id}"]`).addEventListener('change', async (ee) => {

										const tagName = `${ee.target.name}`;
										const isChecked = `${ee.target.checked ? 'on' : 'off'}`;

										const service = {value:isChecked, courier_service_id : item.courier_service_id}

										const __propsPOST = customPost({warehouse_id:wh_id,courier_id:courier_id, ...service});

										let update_courier_servince = await(await(await fetch(`{{ env('API_URL') . '/setting_courier/service/update' }}`, __propsPOST)).json()).data

									})

									
										
								}
							}
		
						} catch (e) {

							log(e)
							
						}


					}



				})


				$("#card_" + index).show('fade');

			});





		});

	}

	$('#warehouse').on('change', function(){
		$('#warehouse_id').val(this.value);
		if(this.value != ''){
			getDataSetting();
		}
    });

    {{-- Submit Form 2 --}}

	$('#setting-form').on('submit', function(e){

		e.preventDefault();

		$('#modalSetting').modal('toggle');

		var url = 'setting_courier/update';

		_sendRequest(url, 'POST', null, true, null, null, 'setting-form');

	});

</script>

@endsection