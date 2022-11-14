<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{ env('APP_NAME') }} | Login</title>
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/core/core.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/fonts/feather-font/css/iconfont.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/css/demo_1/style.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/main/css/notification.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/main/css/login.css') }}">
	<link rel="shortcut icon" href="{{ asset('icon.png') }}">
</head>
<body style="cursor:default;">
	<div style="background-color:#fff;height:100%;width:100%;position:absolute;z-index:1199;overflow:none;" id="white-bg"></div>
	<div id="notification" style="display:none;background:#EE5E64;">
		{{-- JS --}}
	</div>
	<div class="main-wrapper">
		<div class="page-wrapper full-page" style="background: url({{ asset('assets/main/bg.jpg')  }}) no-repeat center center fixed;-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;">
			<div class="page-content d-flex align-items-center justify-content-center">
				<div class="row w-100 mx-0 auth-page">
					<div class="col-12 col-md-6 col-lg-5 col-xl-3 mx-auto">
						<div class="card">
							<div class="row">
								<div class="col-md-12 pl-md-0">
									<div class="auth-form-wrapper px-4 py-4 mt-2 mb-2 ml-2 mr-1">
										<center>
											<a href="{{ route('login') }}">
												<img src="{{ asset('icon.png') }}" style="height:90px;" class="mb-3">
											</a>
											<a class="noble-ui-logo d-block mb-2" style="color:#2a8fcc;"><span style="color:#555;">Login </span>SGS Warrior</a>
										</center>
										<hr class="mb-4">
										<form id="login-form">
											<div class="form-group">
												<label>Email</label>
												<input type="text" class="form-control" name="email" id="email" placeholder="Masukkan Email" autofocus>
											</div>
											<div class="form-group">
												<label>Kata Sandi</label>
												<input type="password" class="form-control" name="password" placeholder="Masukkan Kata Sandi">
											</div>
											<div class="mt-4">
												<input type="hidden" name="media" value="2">
												<button type="submit" class="btn btn-primary btn-block btn-gradient text-white" style="padding-top:11px;padding-bottom:11px;"><span class="spinner-border spinner-border-sm" id="btn_loading" style="display:none;"></span>&nbsp; Login &nbsp;</button>
											</div>
										</form>
										<hr class="mt-4 mb-4">
										<a href="" class="a-href"> Lupa Kata Sandi? </a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="modal fade" id="aktivasi-email-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none;">
            <div class="modal-header" id="modal-header" style="background: #ff9800; /* background: linear-gradient(41deg, #FF9800 0%, #FF5722 50%); */ padding: 12px !important;">
                <h5 class="modal-title" style="color:#fff;">Informasi Aktivasi Email</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="cmxform" id="form-resend-mail" method="get" action="#" enctype="multipart/form-data">
                <div class="modal-body">
                   <h4 style="font-size: 16px; TEXT-ALIGN: center; font-weight: 300; text-align: left;">Kami akan mengirimkan email aktivasi ke akun anda, Apakah anda ingin mengirimkannya?</h4>
                </div>
                <div class="modal-footer">
					<input type="hidden" name="id" id="id">
                    <button type="submit" class="btn btn-danger" id="btn-save"> Kirim </button>
                </div>
            </form>
        </div>
    </div>
</div>


	<script src="{{ asset('assets/noble/template/assets/vendors/core/core.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/vendors/feather-icons/feather.min.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/template.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/jquery-blockUI.js') }}"></script>
	<script src="{{ asset('assets/main/js/notification.js') }}"></script>
</body>
<script>
	$('#login-form').on('submit', function(e){
		e.preventDefault();

		$.ajax({
			url:`{{ env('API_URL') }}/login`,
			type:'POST',
			data: $(this).serialize(),
			dataType: 'json',
			beforeSend:function(){
				$('#btn_loading').show();
				$('#login-form').find('input,button').attr('disabled', true);
				closeNotif();
			},
			success:function(res){
				showNotif('success', res.message);

				setTimeout(function(){

					blockUI();
					window.location.href = "{{ url('login') }}/" + res.data[0].user.email + '||' + res.token;

				}, 1000);
			},
			error:function(err){
				err = err.responseJSON.message;

				if(err.includes('Aktifasi Email anda terlebih dahulu')) {

					showNotif('error', err);
					$('#btn_loading').hide();
					$('#login-form').find('input,button').removeAttr('disabled');
					$('input[name="password"]').val('');

					setTimeout(() => {
						$('#aktivasi-email-modal').modal('show');
					}, 800);
				} else {

					setTimeout(function(){


						showNotif('error', err);

						$('#btn_loading').hide();
						$('#login-form').find('input,button').removeAttr('disabled');
						$('input[name="password"]').val('');

						if(err.toLowerCase().includes('email')){
							$('input[name="email"]').focus();
						}else if(err.toLowerCase().includes('password')){
							$('input[name="password"]').focus();
						}

					}, 500);

				}

			}
		});
	});

	{{-- Loading Screen --}}

	blockUI();

	$(document).ready(function(){


		function __getParam($param){
			const url = new URLSearchParams(window.location.search);
			const par = url.get($param)
			return par;
		}


		const [
			title,
			msg,
			elm_form_send_mail,
			elmBtnSave
		] = [
			__getParam('title'),
			__getParam('message'),
			document.getElementById('form-send-mail'),
			document.getElementById('btn-save'),
		];

		elmBtnSave.addEventListener('click', resendEmail)


		async function resendEmail(e) {
			if(e) e.preventDefault(); 
			const EMAIL = document.getElementById('email').value;
			window.open(`{{ env('API_URL') . '/users/verification/resend/${EMAIL}' }}`)

			$('#aktivasi-email-modal').modal('hide');

			alert('Email aktivasi akun terkirim !')
		}
			
		if(title === 'Verified') {

			showNotif('success', msg);

		}


		setTimeout(function(){
			unblockUI();
			@if(\Session::has('success'))
			    showNotif('success', "{{ \Session::get('success') }}");
		    @elseif(\Session::has('error'))
		    	showNotif('error', "{{ \Session::get('error') }}");
		    @endif
		}, 1000);
	});

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
</script>
</html>
