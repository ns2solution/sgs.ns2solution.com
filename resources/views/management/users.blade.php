@extends('layouts.app')

@section('title', '| Users')
@section('breadcrumb', 'Dashboard  /  User Management  /  Users')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" id="btnAdd" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
	Tambah User&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Users </h5>
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
							<th>Nama Lengkap</th>
							<th>Photo Profile</th>
							<th>Photo KTP</th>
							<th>Email</th>
							<th>Role</th>
							<th>Media</th>
							<th>Status</th>
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

<div class="modal fade" id="modal-view" tabindex="-1">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content" data-dismiss="modal" style="border:none;background-color:transparent;">
			<div class="modal-body">
				<div class="form-group">
					<center>
						<img src="" style="max-height:80vh;max-width:100%;" draggable="false" id="img-view">
					</center>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="border:none;">
			<form method="get" id="users-form">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Edit Detail User</h5>
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
								<label> Nama Lengkap </label>
								<input type="text" class="form-control" id="fullname" name="fullname" autocomplete="off" placeholder="Masukkan nama lengkap" required>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> Email </label>
						<input type="email" class="form-control" id="email" name="email" autocomplete="off" placeholder="Masukkan alamat email" required>
					</div>
					<div class="form-group">
						<label> Role </label>
						<select class="form-control select" id="role" data-minimum-results-for-search="Infinity" required>
							<option value=""> -- Pilih Role -- </option>
							@foreach($user_role as $a)
								<option value="{{ $a->id }}"> {{ $a->role }} </option>
							@endforeach
						</select>
					</div>
					<div class="form-group" id="div_wh">
						<label> Warehouse </label>
						<select class="form-control select" id="warehouse" required>
							<option value=""> -- Pilih Warehouse -- </option>
							@foreach($warehouse as $a)
								<option value="{{ $a->id }}"> {{ $a->code . ' - ' . $a->name . ' (' . $a->short . ')' }} </option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label> Status </label>
						<select class="form-control select" id="active" data-minimum-results-for-search="Infinity" required>
							<option value=""> -- Pilih Status -- </option>
							<option value="1"> Aktif </option>
							<option value="0"> Tidak Aktif </option>
						</select>
					</div>
					<div class="form-group mt-4" id="div_cb">
						<div class="alert alert-primary" role="alert">
							Kata sandi default adalah <b>sgswarrior2020</b>
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

@endsection

@section('js')

<script>

	$.fn.dataTable.ext.errMode = 'none';
	var table = $('#dataTable').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		order: [[2, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/users/data-table' }}",
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
				data: 'fullname',
				width: '25%'
			},
			{
				data: 'photo',
				sClass: 'text-center',
				orderable: false,
				render: function(data){
					if(data != '-'){
						return `<img src="{{ env('API_URL') . '/' }}${data}" id="zoom-foto" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();">`;
					}else{
						return '-';
					}
				}
			},
			{
				data: 'photo_ktp',
				sClass: 'text-center',
				orderable: false,
				render: function(data){
					if(data != '-'){
						return `<img src="{{ env('API_URL') . '/' }}${data}" id="zoom-foto-ktp" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();">`;
					}else{
						return '-';
					}
				}
			},
			{
				data: 'email',
				width: '25%'
			},
			{
				data: 'role',
				sClass: 'text-center'
			},
			{
				data: 'media',
				sClass: 'text-center',
                orderable: false,
                width: '60px',
                render: function(data){
                	switch(data){
                		case 1:
                			return ico_monitor + ' ' + ico_phone;
                			break;
                		case 2:
                			return ico_monitor;
                			break;
                		case 3:
                			return ico_phone;
                			break;
                		case 4:
                			return '-';
                			break;
                	}
                }
			},
			{
				data: 'status',
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

	var ico_monitor = `<svg xmlns='http://www.w3.org/2000/svg' width='21' height='21' viewBox='0 0 24 24' fill='none' stroke='#1a76ad' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-monitor'><rect x='2' y='3' width='20' height='14' rx='2' ry='2'></rect><line x1='8' y1='21' x2='16' y2='21'></line><line x1='2' y1='17' x2='12' y2='21'></line></svg>`, ico_phone = `<svg xmlns='http://www.w3.org/2000/svg' width='21' height='21' viewBox='0 0 24 24' fill='none' stroke='#1a76ad' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-smartphone'><rect x='5' y='2' width='14' height='20' rx='2' ry='2'></rect><line x1='12' y1='18' x2='12.01' y2='18'></line></svg>`;

	{{-- Delete --}}

	$('#dataTable').on('click', '#btnDelete', function(){

		var data = table.row($(this).closest('tr')).data();
		var id   = data[Object.keys(data)[1]],
			email = data[Object.keys(data)[3]];

		DATA_MODAL_YES_NO = {
			id: id,
			by: "{{ Session::get('user')->id }}"
		};

		_modalYesNo('Hapus User', 'Apakah Anda yakin user dengan email ' + email + ' akan dihapus?');

	});

	$('#modalYesNo-form').on('submit', function(e){

		e.preventDefault();
		$('#modalYesNo').modal('toggle');

		_sendRequest('users/delete', 'POST', DATA_MODAL_YES_NO, false, 'dataTable');

	});


	$('#dataTable tbody').on('click', '#zoom-foto', function () {
		const data = table.row( $(this).parents('tr') ).data();
		let img = data.photo

		__querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

		$('#modal-view').modal('show');
	});

	$('#dataTable tbody').on('click', '#zoom-foto-ktp', function () {
		const data = table.row( $(this).parents('tr') ).data();
		let img = data.photo_ktp

		__querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

		$('#modal-view').modal('show');
	});

	{{-- Edit --}}

	$('#dataTable').on('click', '#btnEdit', function(){

		var data  = table.row($(this).closest('tr')).data();

		var id    = data[Object.keys(data)[1]],
			nama  = data[Object.keys(data)[2]],
			email = data[Object.keys(data)[3]],
			role  = data[Object.keys(data)[4]],
			stts  = data[Object.keys(data)[6]],
			wh    = data[Object.keys(data)[11]];

		if(stts.includes('Tidak Aktif') === false){
			stts = '1';
		}else{
			stts = '0';
		}

		$('#div_id').show();
		$('#div_cb').hide();
		$('#div_wh').hide();
		$('#modalEdit').find('.modal-title').empty().append('Edit Detail User');

		$("select#role option").each(function(){
			this.selected = (this.text == role);
		}).change();

		$('#id').val(id);
		$('#fullname').val(nama);
		$('#email').val(email);
		$('#active').val(stts).change();

		$('#warehouse').val(wh).change();

		$('#modalEdit').modal('show');

	});

	{{-- Add --}}

	$('#btnAdd').click(function(){

		$('#div_id').hide();
		$('#div_cb').hide();
		$('#div_wh').hide();
		$('#modalEdit').find('.modal-title').empty().append('Tambah User');

		$('#id').val('');
		$('#fullname').val('');
		$('#email').val('');
		$('#role').val('').change();
		$('#warehouse').val('').change();
		$('#active').val('').change();

		$('#modalEdit').modal('show');

	});

	{{-- Submit Form --}}

	$('#users-form').on('submit', function(e){

		e.preventDefault();
		$('#modalEdit').modal('toggle');

		_sendRequest('users', 'POST', {

			id      : $('#id').val(),
			fullname: $('#fullname').val(),
			email   : $('#email').val(),
			role    : $('#role').val(),
			active  : $('#active').val(),
			by 		: "{{ Session::get('user')->id }}",
			wh_id	: $('#warehouse').val()

		}, false, 'dataTable');

	});

	$('#role').on('change', function(){
		if($('#id').val() == ''){
			if(this.value != '4' && this.value != ''){
				$('#div_cb').show('fade');
			}else{
				$('#div_cb').hide('fade');
			}
		}
		if(this.value != '4' && this.value != '' && this.value != '1'){
			if(this.value == '2'){
				$('#warehouse option').prop('disabled', true);
				$('#warehouse option[value="0"]').prop('disabled', false);
				$('#warehouse option[value=""]').prop('disabled', false);
				$('#warehouse').val('0').change();
			}else{
				$('#warehouse option').prop('disabled', false);
				$('#warehouse option[value="0"]').prop('disabled', true);
				$('#warehouse').val('').change();
			}
			$('#div_wh').show('fade');
			$('#warehouse').attr('required', true);
		}else{
			$('#div_wh').hide('fade');
			$('#warehouse').removeAttr('required');
		}
	});

</script>

@endsection