<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }} @yield('title')</title>

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" />
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/core/core.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/select2/select2.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/fonts/feather-font/css/iconfont.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/css/demo_1/style.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/main/css/toast.min.css') }}">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css">

    <link rel="shortcut icon" href="{{ asset('icon.png') }}">

	{{--  <link rel="stylesheet" href="https://rawgit.com/Eonasdan/bootstrap-datetimepicker/master/build/css/bootstrap-datetimepicker.min.css">  --}}
  	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" integrity="sha512-63+XcK3ZAZFBhAVZ4irKWe9eorFG0qYsy2CaM5Z+F3kUn76ukznN0cp4SArgItSbDFD1RrrWgVMBY9C/2ZoURA==" crossorigin="anonymous" />
  	<style>
	  /* *{
		  background:black !important;
		  color:#fff !important;
	  } */
		.icn-spinner {
			animation: spin-animation 1.5s infinite;
			display: inline-block;
		}
		@keyframes spin-animation {
			0% {
				transform: scale(1,1);
			}
			50% {
				transform: scale(1.2,1.2);
			}
			100% {
				transform: scale(1,1);
			}
		}
		.btn {
			transition: 0.4s;
			background-size: 200% auto;
			border: none;
		}
		.btn:hover:enabled {
			background-position: right center;
		}
		.btn-gradient {
			background-image: linear-gradient(to right, #2a8fcc 0%, #6BB7E5 51%, #2a8fcc 100%);
		}
		.btn-gradient-gold {
			background-image: linear-gradient(to right, #FED23E 0%, #FFF46A 51%, #FED23E 100%);
		}
		.btn-gradient-black {
			background-image: linear-gradient(to right, #1f1f1f 0%, #696969 51%, #1f1f1f 100%);
		}
		.btn-custom:focus {
			background-color: #2A8FCC !important;
		}
		.btn-custom:hover, .btn-custom:active {
			background-color: #52A8DC !important;
		}
		.float{
			position: fixed;
			bottom: 40px;
			right: 40px;
			-webkit-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			-moz-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			height: 40px;
			z-index: 1039;
		}
		.float2{
			position: fixed;
			bottom: 40px;
			right: 245px;
			-webkit-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			-moz-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			height: 40px;
			z-index: 1039;
		}
		.float3{
			position: fixed;
			bottom: 40px;
			right: 270px;
			-webkit-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			-moz-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			height: 40px;
			z-index: 1039;
		}
		.float4{
			position: fixed;
			bottom: 40px;
			right: 350px;
			-webkit-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			-moz-box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			box-shadow: 2px 2px 15px -4px rgba(0,0,0,0.5);
			height: 40px;
			z-index: 1039;
		}
		.modal-backdrop{
			opacity: 0;
			transition: opacity .2s;
		}
		.modal-backdrop.in{
			opacity: .5;
		}
		select {
			cursor: pointer !important;
			color: #495057 !important;
		}
		.alert-primary{
			background-color: #e0f4ff;
			border-color: #e0f4ff;
			color: #2A8FCC;
		}
		.form-group > .select2-container {
			width: 100% !important;
		}
		.select2-selection__placeholder {
			color: #495057 !important;
		}
		.without_ampm::-webkit-datetime-edit-ampm-field {
   			display: none;
   		}
   		input[type=time]::-webkit-clear-button {
   			-webkit-appearance: none;
   			-moz-appearance: none;
   			-o-appearance: none;
   			-ms-appearance:none;
   			appearance: none;
   			margin: -10px;
   		}
	</style>

	@yield('style')

</head>
<body class="sidebar-light" style="cursor:default;">

	<div style="background-color:#fff;height:100%;width:100%;position:absolute;z-index:1199;overflow:none;" id="white-bg"></div>

	<div class="main-wrapper">

		@include('layouts.partials.sidebar')

		<div class="page-wrapper">

			@include('layouts.partials.navbar')

			<div class="page-content" style="background-color:#fff;">

				<nav aria-label="breadcrumb" class="mb-4">
					<ol class="breadcrumb bg-primary" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
						<li class="breadcrumb-item active" aria-current="page"> @yield('breadcrumb') </li>
					</ol>
				</nav>

        		@yield('content')

			</div>
			<footer class="footer d-flex flex-column flex-md-row align-items-center justify-content-between" style="background-color:#fff;">
				<p class="text-muted text-center text-md-left">Copyright © 2020 <a href="https://ns2solution.com" style="color:#2A91D6;" target="_blank">Nusa Sistem Solusi</a>. All rights reserved</p>
			</footer>
		</div>
	</div>

	<!-- Loading  -->

	@include('layouts.partials.modal')
	@include('layouts.partials.loading')

    {{-- dont move position global.js to top or bottom --}}
    <script src="{{ URL::asset('js/global.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

	<script src="{{ asset('assets/noble/template/assets/vendors/core/core.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/chartjs/Chart.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/jquery.flot/jquery.flot.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/jquery.flot/jquery.flot.resize.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/feather-icons/feather.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
  	<script src="{{ asset('assets/noble/template/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
  	<script src="{{ asset('assets/noble/template/assets/vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/noble/template/assets/vendors/jquery-tags-input/jquery.tagsinput.min.js') }}"></script>
    <script src="{{ asset('assets/noble/template/assets/vendors/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/noble/template/assets/vendors/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('assets/noble/template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/template.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/dashboard.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/datepicker.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/jquery-blockUI.js') }}"></script>
	<script src="{{ asset('assets/main/js/notification.js') }}"></script>
    <script src="{{ asset('assets/main/js/toast.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    {{-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> --}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js"></script>

	{{--  <script src="https://rawgit.com/Eonasdan/bootstrap-datetimepicker/master/build/js/bootstrap-datetimepicker.min.js"></script>  --}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js" integrity="sha512-GDey37RZAxFkpFeJorEUwNoIbkTwsyC736KNSYucu1WJWFK9qTdzYub8ATxktr6Dwke7nbFaioypzbDOQykoRg==" crossorigin="anonymous"></script>
	<script type="text/javascript" src="https://cdn.rawgit.com/ashl1/datatables-rowsgroup/fbd569b8768155c7a9a62568e66a64115887d7d0/dataTables.rowsGroup.js"></script>

</body>
<script>

	{{-- Loading Screen --}}

	blockUI();

	$(document).ready(async function(){

		getTotalOrder();

		setTimeout(function(){
			unblockUI();
		}, 100);

		{{-- let role = `{{ Session::get('role') }}`;
		role_check(role) --}}

	});

	{{-- async function role_check(role){

		let role_data = await __newPromise('get-role',`{{ env('API_URL') }}/user-role`);
		let role_data_arr = [];
		for(const data of role_data.data){
			role_data_arr.push(data.role)
		}

		let menu_data = await __newPromise('get-menu-nav',`{{ env('API_URL') }}/menu-nav/byrequest`);

		let json_data,menu_id = {};
		for(const data of menu_data.data){
			json_data = JSON.parse(data.role_access);
			Object.keys(json_data).forEach(function(key) {
				 if(json_data[key].includes(role)){
					$(`#${key}`).show()
				 }else{
					$(`#${key}`).empty();
				 }
			});
		}
	} --}}

	async function getTotalOrder(){

    	let _GET_TOTAL_ORDER = await $.get("{{ env('API_URL') }}/order/total/{{ Session::get('user')->wh_id }}");

		let _SETTING  = await(await(await fetch(`{{ env('API_URL') . '/setting' }}` )).json()).data

		if(_SETTING) {
			localStorage.setItem('CONV_WP', _SETTING.convertion_warpay);
		}

    	$('#total-order').empty();

    	if(_GET_TOTAL_ORDER != 0){

    		$('#total-order').append(`
				<span class="badge badge-primary text-white font-weight-bold">${ _GET_TOTAL_ORDER }</span>
			`).show('fade');

    	}

	}

	function blockUI(){
		$.blockUI({
			message: `<img src="{{ asset('icon.png') }}" class="icn-spinner" style="width:90px;">`,
			overlayCSS: {
				backgroundColor: '#fff',
				opacity: 1,
				zIndex: 99998,
				cursor: 'default'
			},
			css: {
				border: 0,
				color: '#fff',
				padding: 0,
				zIndex: 99999,
				backgroundColor: 'transparent',
				cursor: 'default'
			}
		});
	}

	function unblockUI(){
		$('#white-bg').fadeOut('fast').delay(1000).remove();
		$.unblockUI();
	}

	function _notif(type, msg){
		toastr.options.timeOut = 3000;
		return toastr[type](msg);
	}

	function _sendRequest(url, type, data, conlog, id_dt = null, double = null, formData = null){
		var dataSending = null;
		var contentType = 'application/x-www-form-urlencoded; charset=UTF-8', cache = true, processData = true;

		if(formData == null){

			dataSending = {
				_token: '{{ csrf_token() }}',
				token : "{{ Session::get('token') }}",
				email : "{{ Session::get('email') }}",
				data  : data
			}

		}else{

			contentType = false;
			cache = false;
			processData = false;

			let elm_form = __getId(formData)
			dataSending = new FormData(elm_form);
			dataSending.append('_token', '{{ csrf_token() }}');
            dataSending.append('token',  "{{ Session::get('token')}}");
            dataSending.append('email',  "{{ Session::get('email')}}");
            dataSending.append('by', "{{ Session::get('user')->id }}");

		}
		var process = new Promise(function (resolve, reject) {
			response = $.ajax({
				url : '{{ env('API_URL') }}/' + url,
				type: type,
				data: dataSending,
				dataType:'JSON',
				contentType: contentType,
				cache: cache,
				processData: processData,
				beforeSend:function(){
					blockUI();
				},
				success:function(res){

					if($('#modalEdit').is(':visible')){
						$('#modalEdit').modal('toggle');
					}

					unblockUI();

					if(id_dt != null){
						if(double != null){
							$('#' + id_dt).DataTable().ajax.reload(null, false);
							$('#' + id_dt + '2').DataTable().ajax.reload(null, false);
						}else{
							$('#' + id_dt).DataTable().ajax.reload(null, false);
						}
					}

					setTimeout(function(){
						_notif('success', res.message);
					}, 200);

					conlog === true ? console.log(res) : '';

					resolve(res);

				},
				error:function(err){
					unblockUI();

					err.responseJSON ? err = err.responseJSON.message : err = err.statusText;

					setTimeout(function(){
						_notif('error', err);
					}, 200);

					conlog === true ? console.log('ERR: ' + err) : '';

					reject(err);
				}
			});
		});

		let json = process.then(
    	    (msg) => {
    	        console.log("Resolved: ", msg);
    	        return msg;
    	    },
    	    (err) => {
    	        console.error("Rejected: ", err);
    	        return err;
    	    }
    	);

    	return json;
	}

	{{-- Modal Yes No --}}

	var DATA_MODAL_YES_NO = null;

	function _modalYesNo(title, msg){
		$('#modalYesNo-ttl').empty().append(title);
		$('#modalYesNo-msg').empty().append(msg);

		$('#modalYesNo').modal('show');
	}

	{{-- Utility --}}

	$('.select').select2();

	//$('.btn').on("click",function(){
	//	setTimeout(function(){
	//		_notif('error', 'test');
	//	}, 200);
	//});

	function _dateToday(){
		var date = new Date();
		var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

		return today;
	}

	function _dateFormatSlash_JS(tgl){
		var ex = tgl.split('-');

		var tanggal = ex[0];
		var bulan = ex[1];
		var tahun = ex[2];

		var res = tanggal + '/' + bulan + '/' + tahun;

		return res;
	}

	function _dateFormatStrip_JS(tgl){
		var ex = tgl.split('/');

		var tanggal = ex[0];
		var bulan = ex[1];
		var tahun = ex[2];

		var res = tahun + '-' + bulan + '-' + tanggal;

		return res;
    }

    function _dotFormat(param){
		var res = param.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");

		return res;
	}

</script>

@yield('js')

</html>