@extends('layouts.app')

@section('title', '| Principle')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Principle')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" style="font-size:17px;" onclick="tambahPrinciple()">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Principle&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Principle </h5>
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
							<th>Nama Principle</th>
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
			<form class="cmxform" id="principle-form" method="get" action="#" enctype="multipart/form-data">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Edit Detail Principle</h5>
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
								<input type="text" class="form-control" id="code" name="code" autocomplete="off" maxlength="5" placeholder="Masukkan kode principle" required>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label> Nama Principle </label>
						<input type="text" class="form-control" id="name" name="name" autocomplete="off" placeholder="Masukkan nama principle" required>
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

	$.fn.dataTable.ext.errMode = 'none';
	var table = $('#dataTable').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		order: [[2, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/principle/data-table' }}",
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
				data: 'logo',
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
		$('#modalEdit').find('.modal-title').empty().append('Edit Detail Principle');

		$('#id').val(data.id);
		$('#name').val(data.name);
		$('#code').val(data.code);

		$('.dropify-error').empty();
        $('.dropify-errors-container').empty();

		changeImageDropify('#logo', "{{ env('API_URL') . '/' }}" + data.logo);

		$('#modalEdit').modal('show');

	});

	{{-- Add --}}

	function tambahPrinciple(){

		$('#div_id').hide();
		$('#div_cb').hide();
		$('#modalEdit').find('.modal-title').empty().append('Tambah Principle');

		$('#id').val('');
		$('#code').val('');
		$('#name').val('');

		removeImageDropify('#logo');

		$('#modalEdit').modal('show');

	}

	{{-- Submit Form --}}

	$('#principle-form').on('submit', function(e){

		e.preventDefault();

		var url = '';
		if($('#id').val() != ''){
			url = 'principle/update/' + $('#id').val();
		}else{
			url = 'principle/create';
		}

		_sendRequest(url, 'POST', null, false, 'dataTable', null, 'principle-form');

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

		_modalYesNo('Hapus Principle', 'Apakah Anda yakin principle ' + name + ' dengan id ' + id + ' akan dihapus?');

	});

	$('#modalYesNo-form').on('submit', function(e){

		e.preventDefault();
		$('#modalYesNo').modal('toggle');

		_sendRequest('principle/delete', 'POST', DATA_MODAL_YES_NO, false, 'dataTable');

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

</script>

@endsection