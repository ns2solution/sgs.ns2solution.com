@extends('layouts.app')

@section('title', '| Gudang')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Gudang')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" onclick="tambahPlace()" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
	Tambah Place&nbsp;
</button>
<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float2" onclick="tambahWarehouse()" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
	Tambah Warehouse&nbsp;
</button>

<div class="row">
	<div class="col-4">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Warehouse </h5>
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
							<th>ID</th>
							<th>Singkat</th>
							<th>Nama</th>
							<th>Kode</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-8">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Place </h5>
					<button class="btn p-0" type="button" onclick="$('#dataTable2').DataTable().ajax.reload(null, false);">
                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                    </button>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="dataTable2" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>Aksi</th>
							<th>ID</th>
							<th>WH ID</th>
							<th>Provinsi</th>
							<th>Tipe</th>
							<th>Kabupaten/Kota</th>
							<th>Kode Pos</th>
							<th>Updated At</th>
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
			<form method="get" id="warehouse-form">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Edit Detail Warehouse</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-3 col-lg-3" id="div_id">
							<div class="form-group">
								<label> ID </label>
								<input type="text" class="form-control" id="id" name="id" style="cursor:default;" readonly>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label> Nama Singkat </label>
								<input type="text" class="form-control" id="short" name="short" autocomplete="off" maxlength="5" placeholder="Masukkan nama singkat" required>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> Nama Gudang </label>
						<input type="text" class="form-control" id="name" name="name" autocomplete="off" placeholder="Masukkan nama gudang" required>
					</div>
					<div class="form-group">
						<label> Kode </label>
						<input type="text" class="form-control" id="code" name="code" autocomplete="off" data-mask="000" placeholder="Masukkan kode gudang" required>
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

<div class="modal fade" id="modalEdit2" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="border:none;">
			<form method="get" id="place-form">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Edit Detail Place</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-3 col-lg-3" id="div_id2">
							<div class="form-group">
								<label> ID </label>
								<input type="text" class="form-control" id="id2" name="id2" style="cursor:default;" readonly>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label> Warehouse </label>
								<select class="form-control select" id="warehouse" required>
									<option value=""> -- Pilih Warehouse -- </option>
									@foreach($warehouse as $a)
										<option value="{{ $a->id }}"> {{ $a->name }} </option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> Provinsi </label>
						<select class="form-control select" id="provinsi" required>
							<option value=""> -- Pilih Provinsi -- </option>
							@foreach($provinsi as $a)
								<option value="{{ $a->province_id }}"> {{ $a->province }} </option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label> Tipe </label>
						<select class="form-control select" id="type" required>
							<option value=""> -- Pilih Tipe -- </option>
							<option value="Kabupaten"> Kabupaten </option>
							<option value="Kota"> Kota </option>
						</select>
					</div>
					<div class="form-group">
						<label> Kabupaten/Kota </label>
						<input type="text" class="form-control" id="kota" name="kota" autocomplete="off" placeholder="Masukkan kabupaten/kota" required>
					</div>
					<div class="form-group">
						<label> Kode POS </label>
						<input type="text" class="form-control" id="postal_code" name="postal_code" autocomplete="off" placeholder="Masukkan kode pos" data-mask="000000" required>
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

@endsection

@section('js')

<script>

	{{-- Data Table Gudang --}}

	$.fn.dataTable.ext.errMode = 'none';
	var table = $('#dataTable').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		searching: false,
		// language: { lengthMenu: "Show _MENU_" },
		order: [[1, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/warehouse/data-table' }}",
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
                render: function(data, type, row, meta){
                	let btnApprove  = '';
                	if (row.status == 1) {
                		btnApprove = `<button class="btn p-0" type="button" onclick="disableWr(${row.id},'disable')" title="Nonakrifkan Gudang">
		                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
		                            </button>
		                            &nbsp;&nbsp;`
                	}else{
                		btnApprove = `<button class="btn p-0" type="button" onclick="disableWr(${row.id},'enable')" title="Aktifkan Gudang">
			                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#4CAF54" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
			                        </button>
			                        &nbsp;&nbsp;`
                	}

                	return `
                		${btnApprove}
	        			<button class="btn p-0" type="button" id='btnEdit' title="Edit Gudang">
                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    	</button>
                    	&nbsp;&nbsp;
	        			<button class="btn p-0" type="button" id='btnDelete' title="Hapus Gudang">
                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#FF3366" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    	</button>
	        		`;
                }
			},
			{
				data: 'id',
				sClass: 'text-center'
			},
			{
				data: 'short',
				sClass: 'text-center'
			},
			{
				data: 'name',
				width: '50%'
			},
			{
				data: 'code',
				sClass: 'text-center'
			}
		]
	});

	{{-- Disable --}}

	function disableWr(id,type){
		console.log(type)
		let mode = text = ''
		if (type == 'enable') {
			text = 'Yakin akan mengaktifkan warehouse ini?'
			mode = false;
		}else{
			text = 'Yakin akan menonaktifkan warehouse ini?'
			mode = true;
		}
		swal({
		  title: "Apakah anda yakin?",
		  text: text,
		  icon: "warning",
		  buttons: true,
		  dangerMode: mode,
		})
		.then((willDelete) => {
		  if (willDelete) {

		  	$.ajax({
		  		type: 'post',
		  		url: "{{ env('API_URL') . '/warehouse/updateStatus' }}",
		  		data:{
		  			id:id,
		  			mode:mode,
		  			token : "{{ Session::get('token') }}",
					email : "{{ Session::get('email') }}"
		  		},
		  		success:function(res){
		  			console.log(res)
		  			$('#dataTable').DataTable().ajax.reload(null, false);
		  		},
		  		error:function(err){
		  			
		  		}
		  	})

		    swal("Status Berhasil dirubah!", {
		      icon: "success",
		    });
		  }
		});
	}

	{{-- Delete --}}

	$('#dataTable').on('click', '#btnDelete', function(){

		var data = table.row($(this).parents('tr')).data();

		DATA_MODAL_YES_NO = {
			id: data.id,
			by: "{{ Session::get('user')->id }}"
		};

		DATA_MODAL_URL = 'warehouse/delete';

		_modalYesNo('Hapus Gudang', 'Apakah Anda yakin gudang ' + data.name + ' dengan id ' + data.id + ' akan dihapus?');

	});

	{{-- Edit --}}

	$('#dataTable').on('click', '#btnEdit', function(){

		var data = table.row($(this).parents('tr')).data();

		$('#div_id').show();
		$('#modalEdit').find('.modal-title').empty().append('Edit Detail Warehouse');

		$('#id').val(data.id);
		$('#short').val(data.short);
		$('#name').val(data.name);
		$('#code').val(data.code);

		$('#modalEdit').modal('show');

	});

	{{-- Add --}}

	function tambahWarehouse(){

		$('#div_id').hide();
		$('#modalEdit').find('.modal-title').empty().append('Tambah Warehouse');

		$('#id').val('');
		$('#short').val('');
		$('#name').val('');
		$('#code').val('');

		$('#modalEdit').modal('show');

	}

	{{-- Submit Form --}}

	$('#warehouse-form').on('submit', function(e){

		e.preventDefault();
		$('#modalEdit').modal('toggle');

		_sendRequest('warehouse', 'POST', {

			id      : $('#id').val(),
			short	: $('#short').val(),
			name   	: $('#name').val(),
			code    : $('#code').val()

		}, false, 'dataTable');

	});

	{{-- Data Table Place --}}

	var table2 = $('#dataTable2').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		order: [[1, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/place/data-table' }}",
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
				data: 'id',
				sClass: 'text-center'
			},
			{
				data: 'warehouse_id',
				sClass: 'text-center'
			},
			{
				data: 'provinsi',
				width: '20%'
			},
			{
				data: 'type'
			},
			{
				data: 'city_name',
				width: '20%'
			},
			{
				data: 'postal_code',
				sClass: 'text-center'
			},
			{
				data: 'updated_at',
				sClass: 'text-center'
			}
		]
	});

	{{-- Delete --}}

	$('#dataTable2').on('click', '#btnDelete', function(){

		var data = table2.row($(this).parents('tr')).data();

		DATA_MODAL_YES_NO = {
			id: data.id,
			by: "{{ Session::get('user')->id }}"
		};

		DATA_MODAL_URL = 'place/delete';

		_modalYesNo('Hapus Place', 'Apakah Anda yakin place dengan id ' + data.id + ' akan dihapus?');

	});

	{{-- Edit --}}

	$('#dataTable2').on('click', '#btnEdit', function(){

		var data = table2.row($(this).parents('tr')).data();

		$('#div_id2').show();
		$('#modalEdit2').find('.modal-title').empty().append('Edit Detail Place');

		$('#id2').val(data.id);
		$('#warehouse').val(data.warehouse_id).change();
		$('#type').val(data.type).change();
		$('#kota').val(data.city_name);
		$('#postal_code').val(data.postal_code);

		$("select#provinsi option").each(function(){
			this.selected = (this.text == data.provinsi);
		}).change();

		$('#modalEdit2').modal('show');

	});

	{{-- Add --}}

	function tambahPlace(){

		$('#div_id2').hide();
		$('#modalEdit2').find('.modal-title').empty().append('Tambah Place');

		$('#id2').val('');
		$('#warehouse').val('').change();
		$('#type').val('').change();
		$('#provinsi').val('').change();
		$('#kota').val('');
		$('#postal_code').val('');

		$('#modalEdit2').modal('show');

	}

	{{-- Submit Form --}}

	$('#place-form').on('submit', function(e){

		e.preventDefault();
		$('#modalEdit2').modal('toggle');

		_sendRequest('place', 'POST', {

			id         : $('#id2').val(),
			prov_id	   : $('#provinsi').val(),
			place  	   : $('#kota').val(),
			wh_id      : $('#warehouse').val(),
			type       : $('#type').val(),
			postal_code: $('#postal_code').val()

		}, false, 'dataTable2');

	});

	{{-- Utility Delete --}}

	var DATA_MODAL_URL = null;

	$('#modalYesNo-form').on('submit', function(e){

		e.preventDefault();
		$('#modalYesNo').modal('toggle');

		_sendRequest(DATA_MODAL_URL, 'POST', DATA_MODAL_YES_NO, false, 'dataTable', 1);

	});

</script>

@endsection