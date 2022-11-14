<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title> {{ env('APP_NAME') }} | Error 404 </title>
	<link rel="icon" href="{{ asset('icon.png') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/core/core.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/fonts/feather-font/css/iconfont.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/noble/template/assets/css/demo_1/style.css') }}">
	<style>
		.btn {
			transition: 0.4s;
			background-size: 200% auto;
			border: none;
		}
		.btn:hover:enabled {
			background-position: right center;
		}
		.btn-gradient {
			background-image: linear-gradient(to right, #2a8fcc 0%, #8ecdf3 51%, #2a8fcc 100%);
		}
	</style>
</head>
<body class="sidebar-dark" style="cursor:default;">
	<div class="main-wrapper">
		<div class="page-wrapper full-page">
			<div class="page-content d-flex align-items-center justify-content-center" style="background-color:#fff;">
				<div class="row w-100 mx-0 auth-page">
					<div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
						<img src="{{ asset('assets/main/img/404.jpg') }}" class="img-fluid mb-2" draggable="false">
						<h6 class="text-muted mb-4 mt-2 text-center" style="font-size:19px;">Oopss.. Halaman yang Anda cari tidak ditemukan.</h6>
						@php
							$url = url('')
						@endphp
						<button type="submit" class="btn btn-primary btn-gradient text-white" onclick="window.location.href = '{{ $url }}'">
							&nbsp; Kembali ke halaman utama &nbsp;
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="{{ asset('assets/noble/template/assets/vendors/core/core.js') }}"></script>
	<script src="{{ asset('assets/noble/template/assets/js/template.js') }}"></script>
</body>
</html>