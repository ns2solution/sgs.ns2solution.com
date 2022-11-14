<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<title>SGS Warrior - {{ $order->no_po }}</title>

	<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900' type='text/css'>

	<link rel="stylesheet" type="text/css" href="/assets/invoice/vendor/bootstrap/css/bootstrap.min.css"/>
	<link rel="stylesheet" type="text/css" href="/assets/invoice/vendor/font-awesome/css/all.min.css"/>
	<link rel="stylesheet" type="text/css" href="/assets/invoice/css/stylesheet.css"/>

</head>
<body>

	<!-- Container -->
	<div class="container-fluid invoice-container">

		<!-- Header -->
		<header>
			<div class="row align-items-center">
			<div class="col-sm-7 text-center text-sm-left mb-3 mb-sm-0">
				<img id="logo" src="/assets/apps/images/sgswarrior-logo.png" title="SGS" alt="SGS" width="240" />
			</div>
			<div class="col-sm-5 text-center text-sm-right">
				<h4 class="text-7 mb-0">{{ $order->no_invoice }}</h4>
			</div>
			</div>
			<hr>
		</header>

		<!-- Main Content -->
		<main>

			<div class="row">
				<div class="col-sm-6" style="font-size:22px;">
					
					<strong>Tanggal:</strong> {{ date('d M Y', strtotime($order->created_at)) }}
					<br>
					<br>
					<strong>Transfer:</strong> {{ ucfirst($order->payment_type) }}
				
				</div>
				<div class="col-sm-6 text-sm-right" style="font-size:22px;"> <strong>Nomor INV:</strong> {{ $order->no_invoice ? $order->no_invoice : '-' }}</div>
				
			</div>
			<hr>
			<div class="row">
				<div class="col-sm-6 order-sm-0" style="font-size:22px;"> <strong>Pembelian Oleh:</strong>
					<address style="font-size:20px;line-height:23px;">
						{{ $order->user_fullname }}<br />
						{{ $order->user_profile_phone }}<br />
						@php
							$user_profile_address = explode('"',$order->address);
							$user_profile_address = implode(' ', $user_profile_address);
							echo $user_profile_address;
						@endphp
					</address>
				</div>
				<div class="col-sm-6 order-sm-0" style="font-size:22px;"> <strong>Pengirim Oleh:</strong>
					<address style="font-size:20px;line-height:23px;">
						@if($order->is_dropshipper)
							{{ $order->dropshipper_name }}<br />
							{{ $order->dropshipper_number }}<br />
						@else
							SGS
						@endif
					</address>
				</div>
			</div>  
			<div class="card">
				<div class="table-responsive">
					<table class="table mb-0">
						<thead class="card-header">
							<tr>
								<td class="col-2 border-0" width="20%"><strong>Nama Produk</strong></td>
								{{--<td class="col-3 border-0"><strong>SKU</strong></td> --}}
								<td class="col-2 text-right border-0"><strong>Harga Satuan</strong></td>
								<td class="col-2 text-right border-0"><strong>Jumlah</strong></td>
								<td class="col-2 text-right border-0"><strong>Berat</strong></td>
								<td class="col-3 text-right border-0"><strong>Sub Total</strong></td>
							</tr>
						</thead>
						<tbody>
							@php 

								$subtotal = 0;

							@endphp
							@foreach ($order_item as $key => $d)
							<tr>
								<td class="col-2 {{ $key == 0 ? 'border-0' : '' }}" width="20%">{{ $d->prod_name }}</td>
								{{-- <td class="col-3 {{ $key == 0 ? 'border-0' : '' }}">{{ $d->prod_number }}</td> --}}
								<td class="col-2 text-right {{ $key == 0 ? 'border-0' : '' }}">{{ $d->price, 0 }}</td>
								<td class="col-2 text-right {{ $key == 0 ? 'border-0' : '' }}">{{ $d->total_item, 0 }}</td>
								<td class="col-2 text-right {{ $key == 0 ? 'border-0' : '' }}">{{ $d->total_weight}}</td>
								<td class="col-3 text-right {{ $key == 0 ? 'border-0' : '' }}">{{ $d->total_price }}</td>
							</tr>
					
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<td colspan="12" class="bg-light-2 text-right">
									<strong>Sub Total:</strong><br />
									{{ $order->total_price }}
								</td>
							</tr>
							<tr>
								<td colspan="12" class="bg-light-2 text-right">
									<strong>Ongkos Kirim:</strong><br />
									{{ $order->total_ongkir }}
								</td>
							</tr>
							<tr>
								<td colspan="12" class="bg-light-2 text-right">
									<strong>Total:</strong><br />
									{{ $order->final_total }}
								</td>
							</tr>
						</tfoot>
					</table
				</div>
			</div>
			
		</main>

		<!-- Footer -->
		<footer class="text-center mt-4">
			<p class="text-1"><strong>NOTE :</strong> This is computer generated receipt and does not require physical signature.</p>
			<div class="btn-group btn-group-sm d-print-none"> <a href="javascript:window.print()" class="btn btn-light border text-black-50 shadow-none" id="print-doc"><i class="fa fa-print"></i> Print</a> </div>
		</footer>

	</div>

	<script>
		// window.scrollTo(0, document.body.scrollHeight);
		// window.print();

		window.onload = () => {
			
			document.getElementById('print-doc').addEventListener('click', () => {
				javascript:window.print()
			})

			document.getElementById('print-doc').dispatchEvent(new Event("click", {bubbles: true,}));

		}
	</script>

</body>
</html>