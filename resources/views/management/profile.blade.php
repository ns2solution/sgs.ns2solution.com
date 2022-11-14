@extends('layouts.app')

@section('title', '| Profile')
@section('breadcrumb', 'Dashboard  /  User Management  /  Profile')

@section('content')

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Profile </h5>
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
							<th>User ID</th>
							<th>No. HP</th>
							<th>Alamat</th>
							<th>Place ID</th>
							<th>Kode Pos</th>
							<th>JK</th>
							<th>Tgl. Lahir</th>
							<th>Warpay</th>
							<th>Poin</th>
							<th>Foto</th>
							<th>Foto KTP</th>
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
			<form method="get" id="profile-form">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Edit Detail Profile</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-6 col-lg-6">
							<div class="form-group">
								<label> ID </label>
								<input type="text" class="form-control" id="id" name="id" style="cursor:default;" readonly>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label> User ID </label>
								<input type="text" class="form-control" id="user_id" name="user_id" style="cursor:default;" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> No. Handphone </label>
						<input type="text" class="form-control" id="no_hp" name="no_hp" autocomplete="off" data-mask="000000000000" placeholder="Masukkan no. handphone">
					</div>
					<div class="form-group">
						<label> Alamat </label>
						<textarea class="form-control" id="address" name="address" rows="3" placeholder="Masukkan alamat"></textarea>
					</div>
					<div class="row">
						<div class="col-6 col-lg-6">
							<div class="form-group">
								<label> Place </label>
								<select class="select" id="place" data-placeholder="-- Pilih Place --">
									<option></option>
									@foreach($place as $a)
										<option value="{{ $a->city_id }}"> {{ $a->type . ' ' . $a->city_name }} </option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-6 col-lg-6">
							<div class="form-group">
								<label> Kode POS </label>
								<input type="text" class="form-control" id="postal_code" name="postal_code" autocomplete="off" data-mask="00000" placeholder="Masukkan kode pos">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-6 col-lg-6">
							<div class="form-group">
								<label> Jenis Kelamin </label>
								<select class="select" id="gender" data-placeholder="-- Pilih Jenis Kelamin --" data-minimum-results-for-search="Infinity">
									<option></option>
									<option value="P"> Perempuan </option>
									<option value="L"> Laki-laki </option>
								</select>
							</div>
						</div>
						<div class="col-6 col-lg-6">
							<div class="form-group">
								<label> Tanggal Lahir </label>
								<div class="input-group date datepicker" id="date_birth_dp">
									<input type="text" class="form-control" id="date_birth" name="date_birth" autocomplete="off" style="background-color:#fff;cursor:pointer;" placeholder="dd/mm/yyyy" readonly><span class="input-group-addon"><i data-feather="calendar"></i></span>
								</div>
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
	//set warehouse by user role
	let user_role = `{{ Session::get('role') }}`;
    let user_wh = `{{ Session::get('warehouse_id') }}`;
    let table_wh = ``;
    switch(user_role){
        case  'Admin Gudang' : table_wh = user_wh;
        break;
        default : table_wh = '';
    }
	$.fn.dataTable.ext.errMode = 'none';
	var table = $('#dataTable').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		order: [[2, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/profile/data-table' }}",
			dataType: 'JSON',
			type: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				token : "{{ Session::get('token') }}",
				email : "{{ Session::get('email') }}",
				warehouse_id   : table_wh
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
				data: 'user_id',
				width: '35px',
				sClass: 'text-center'
			},
			{
				data: 'phone',
                orderable: false
			},
			{
				data: 'address',
                orderable: false
			},
			{
				data: 'place_id',
                orderable: false,
				sClass: 'text-center'
			},
			{
				data: 'postal_code',
                orderable: false,
				sClass: 'text-center'
			},
			{
				data: 'gender',
				sClass: 'text-center'
			},
			{
				data: 'date_birth',
				sClass: 'text-center'
			},
			{
				data: 'warpay',
				sClass: 'text-center',
				render: function(data){
					if(data != '-'){
						return `<span class="badge badge-pill badge-dark" style="padding:5px 8px 5px 8px;font-size:12px;"><img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:15px;height:15px;">&nbsp; ${_dotFormat(data)} </span>`;
					}else{
						return data;
					}
				}
			},
			{
				data: 'point',
				sClass: 'text-center',
				render: function(data){
					if(data != '-'){
						return `<span class="badge badge-pill badge-warning" style="padding:5px 8px 5px 8px;font-size:12px;color:#fff;"><img draggable="false" src="{{ asset('assets/main/img/icon_point.png') }}" style="width:15px;height:15px;">&nbsp; ${_dotFormat(data)} </span>`;
					}else{
						return data;
					}
				}
			},
			{
				data: 'photo',
				sClass: 'text-center',
                orderable: false,
				render: function(data){
					if(data != '-'){
						return `<img src="{{ env('API_URL') . '/' }}${data}" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();" onclick="zoomFoto(1, this);">`;
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
						return `<img src="{{ env('API_URL') . '/' }}${data}" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();" onclick="zoomFoto(2, this);">`;
					}else{
						return '-';
					}
				}
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

	{{-- Delete --}}

	$('#dataTable').on('click', '#btnDelete', function(){

		var data = table.row($(this).closest('tr')).data();
		var id   = data[Object.keys(data)[1]];

		DATA_MODAL_YES_NO = {
			id: id,
			by: "{{ Session::get('user')->id }}"
		};

		_modalYesNo('Hapus Profile', 'Apakah Anda yakin profile dengan id ' + id + ' akan dihapus?');

	});

	$('#modalYesNo-form').on('submit', function(e){

		e.preventDefault();
		$('#modalYesNo').modal('toggle');

		_sendRequest('profile/delete', 'POST', DATA_MODAL_YES_NO, false, 'dataTable');

	});

	{{-- Edit --}}

	$('#dataTable').on('click', '#btnEdit', function(){

		var data  = table.row($(this).closest('tr')).data();

		var id    	= data[Object.keys(data)[1]],
			uid   	= data[Object.keys(data)[2]],
			nohp  	= data[Object.keys(data)[3]]  != '-' ? data[Object.keys(data)[3]] : '',
			address = data[Object.keys(data)[4]]  != '-' ? data[Object.keys(data)[4]] : '',
			place   = data[Object.keys(data)[5]]  != '-' ? data[Object.keys(data)[5]] : '',
			postal  = data[Object.keys(data)[6]]  != '-' ? data[Object.keys(data)[6]] : '',
			gender  = data[Object.keys(data)[7]]  != '-' ? data[Object.keys(data)[7]] : '',
			birtdt  = data[Object.keys(data)[10]] != '-' ? _dateFormatSlash_JS(data[Object.keys(data)[10]]) : '';

		$('#modalEdit').find('.modal-title').empty().append('Edit Detail User');

		$('#id').val(id);
		$('#user_id').val(uid);
		$('#no_hp').val(nohp);
		$('#address').val(address);
		$('#place').val(place).change();
		$('#postal_code').val(postal);
		$('#gender').val(gender).change();
		
		$('#date_birth_dp').datepicker({format: "dd/mm/yyyy", todayHighlight: true, autoclose: true});
		$('#date_birth_dp').datepicker('setDate', birtdt);

		$('#modalEdit').modal('show');

	});

	{{-- Submit Form --}}

	$('#profile-form').on('submit', function(e){

		e.preventDefault();
		$('#modalEdit').modal('toggle');

		_sendRequest('profile', 'POST', {

			id      : $('#id').val(),
			user_id : $('#user_id').val(),
			no_hp	: $('#no_hp').val(),
			address : $('#address').val(),
			place   : $('#place').val(),
			postal  : $('#postal_code').val(),
			gender  : $('#gender').val(),
			birth   : $('#date_birth').val() != '' ? _dateFormatStrip_JS($('#date_birth').val()) : '',
			by 		: "{{ Session::get('user')->id }}"

		}, false, 'dataTable');

	});

	{{-- Zoom Image --}}

	function zoomFoto(type, sel){

		var data = table.row($(sel).closest('tr')).data();

		switch(type){
			case 1:
				var title = 'Foto';
				var img  = data[Object.keys(data)[8]];
				break;
			case 2:
				var title = 'Foto KTP';
				var img  = data[Object.keys(data)[9]];
				break;
		}

		$('#modalView').find('.modal-title').empty().append(title);

		$('#imgView').attr('src', "{{ env('API_URL') . '/' }}" + img);

		$('#modalView').modal('show');

	}

</script>

@endsection