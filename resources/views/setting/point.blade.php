@extends('layouts.app')

@section('title', '| Tambah Poin')
@section('breadcrumb', 'Dashboard  /  Lain-Lain  /  Tambah Poin')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" style="font-size:17px;" onclick="tambahManual()">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Poin Manual&nbsp;
</button>

<div class="row">
	<div class="col-4">
		<div class="card">
			<form id="point-setting-form">
				<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Poin Ulang Tahun </h5>
					</div>
				</div>
				<div class="card-body">
					<label> Jumlah Pertambahan Poin </label>
					<input type="text" class="form-control" style="cursor:default;pointer-events:none;" id="point_birthday" name="point_birthday" data-mask="000.000" data-mask-reverse="true" readonly>
				</div>
				<div class="card-footer" id="dynamic-btn">
					<button type="button" id="btnBatal" onclick="batalPoint();" class="btn btn-light" style="display:none;"> Batal </button>
					<button type="button" id="btnEdit" onclick="editPoint();" class="btn btn-primary btn-custom" >Edit</button>
					<button type="submit" id="btnSubmitSetting" class="btn btn-primary btn-custom" >Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="border:none;">
			<form class="cmxform" id="point-form" method="get" action="#" enctype="multipart/form-data">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Tambah Poin</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					{{-- Hidden Param --}}
					<input type="hidden" id="message" name="message">
					<div class="form-group">
						<label> User </label>
						<select class="select" id="user_id" name="user_id" data-placeholder="-- Pilih User --" required>
							<option></option>
							@foreach($buyer as $a)
								<option value="{{ $a->id }}"> {{ $a->id }} - {{ $a->fullname }} ({{ $a->email }}) </option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label> Poin </label>
						<input type="text" class="form-control" id="point" name="point" autocomplete="off" placeholder="Masukkan jumlah poin yang ingin ditambah" data-mask="000.000" data-mask-reverse="true" required>
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

	{{-- Point Ulang Tahun --}}

	let __POINT_BIRTHDAY = "{{ $setting->point_birthday }}";

	$(document).ready(function(){

		$('#point_birthday').val(__POINT_BIRTHDAY).trigger('input');

		$('#btnSubmitSetting').hide()

	});

	function editPoint(){

		if($('#btnEdit').text() == 'Edit'){

			$('#point_birthday').removeAttr('readonly').css('pointer-events', 'inherit').css('cursor', 'text').focus();
			$('#btnEdit').hide();
			$('#btnBatal').show();
			$('#btnSubmitSetting').show();
		}else{

			_notif('warning', 'Function belum dibuat...');

		}

	}

	function batalPoint(){

		$('#btnBatal').hide();
		$('#point_birthday').attr('readonly', true).css('pointer-events', 'none').css('cursor', 'default');
		$('#point_birthday').val(__POINT_BIRTHDAY).trigger('input');
		$('#btnSubmitSetting').hide();
		$('#btnEdit').show();

	}

	{{-- Add --}}

	function tambahManual(){

		$('#user_id').val('').change();
		$('#point').val('');

		$('#modalEdit').modal('show');

	}

	{{-- Submit Form --}}

	$('#point-form').on('submit', function(e){

		e.preventDefault();

		$('#message').val("{{ Session::get('user')->fullname . ' (' . Session::get('role') . ')' }} menambahkan poin sebesar <b>" + $('#point').val() + "</b> ke user dengan id " + $('#user_id').val() + ".");

		_sendRequest('point/add', 'POST', null, false, null, null, 'point-form');

	});

	$('#point-setting-form').on('submit', async function(e){

		e.preventDefault();

		$('#message').val("{{ Session::get('user')->fullname . ' (' . Session::get('role') . ')' }} menambahkan poin sebesar <b>" + $('#point').val() + "</b> ke user dengan id " + $('#user_id').val() + ".");

		let res = await _sendRequest('setting/update', 'POST', null, false, null, null, 'point-setting-form');
		
		log(res);
		if(res.status){
			__POINT_BIRTHDAY = res.data.point_birthday
			batalPoint();
		}
	});

</script>

@endsection