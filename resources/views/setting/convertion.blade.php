@extends('layouts.app')

@section('title', '| Konversi Warpay')
@section('breadcrumb', 'Dashboard  /  Lain-Lain  /  Konversi Warpay')

@section('content')

<div class="row">
	<div class="col-5">
		<form class="cmxform" id="convert-form" method="get" action="#" enctype="multipart/form-data">
			<div class="card">
				<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Rupiah ke Warpay </h5>
					</div>
				</div>
				<div class="card-body">
					<label> 1 Warpay Sama Dengan Berapa Rupiah </label>
					<div class="input-group col-xs-12">
						<input type="text" class="form-control" style="cursor:default;pointer-events:none;" id="convertion_warpay" name="convertion_warpay" data-mask="000.000" data-mask-reverse="true" readonly>
						<span class="input-group-append">
							<button class="btn btn-secondary" style="pointer-events:none;" type="button"> &nbsp; Rupiah &nbsp; </button>
						</span>
					</div>
				</div>
				<div class="card-footer">
					<button type="button" id="btnBatal" onclick="batalConvert();" class="btn btn-light" style="display:none;"> Batal </button>
					<button type="button" id="btnEdit" onclick="editConvert();" class="btn btn-primary btn-custom">Edit</button>
				</div>
			</div>
		</form>
	</div>
	<div class="col-1">
		<center>
			<i data-feather="chevrons-right" style="margin-top:115px;color:#2a8fcc;"></i>			
		</center>
	</div>
	<div class="col-6">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Simulasi Konversi </h5>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-hover table-responsive" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th style="width:50px;"><center>#</center></th>
							<th style="width:350px;">Rupiah</th>
							<th style="width:350px;">Warpay</th>
						</tr>
					</thead>
					<tbody id="simulasi">
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')

<script>

	{{-- Point Ulang Tahun --}}

	let __AMOUNT_CONVERTION = "{{ $setting->convertion_warpay }}";

	$(document).ready(function(){

		$('#convertion_warpay').val(__AMOUNT_CONVERTION).trigger('input');

		printSimulation();

	});

	function editConvert(){

		if($('#btnEdit').text() == 'Edit'){

			$('#convertion_warpay').removeAttr('readonly').css('pointer-events', 'inherit').css('cursor', 'text').focus();
			$('#btnEdit').empty().append('Simpan');
			$('#btnBatal').show();

		}else{

			$('#convert-form').submit();

		}

	}

	function batalConvert(){

		$('#btnBatal').hide();
		$('#convertion_warpay').attr('readonly', true).css('pointer-events', 'none').css('cursor', 'default');
		$('#convertion_warpay').val(__AMOUNT_CONVERTION).trigger('input');
		$('#btnEdit').empty().append('Edit');

	}

	{{-- Submit Form --}}

	$('#convert-form').on('submit', function(e){

		e.preventDefault();

		_sendRequest('setting/update', 'POST', null, false, null, null, 'convert-form');

		__AMOUNT_CONVERTION = parseInt($('#convertion_warpay').val().replace(/\./g,''));

		batalConvert();

	});

	{{-- Print Simulation --}}

	function printSimulation(){

		const rupiah = [25000, 50000, 100000, 250000, 1000000];

		let data   = '';
		let amount = parseInt($('#convertion_warpay').val().replace(/\./g,''));

		rupiah.forEach(function(item, index){

			let result = item / amount;
			isNaN(result) ? result = 0 : result === Infinity ? result = 0 : '';

			result = Math.round(result);

			data += `
				<tr>
					<td><center> ${ index + 1 } </center></td>
					<td> Rp ${ _dotFormat(item) } </td>
					<td>
						<img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp; 
						${ _dotFormat(result) } 
					</td>
				</tr>
			`

		});

		$('#simulasi').empty().append(data);

	}

	$('#convertion_warpay').on('input', function(){

		printSimulation();

	})

</script>

@endsection