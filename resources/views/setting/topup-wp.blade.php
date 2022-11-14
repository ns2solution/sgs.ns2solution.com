@extends('layouts.app')

@section('title', '| Konversi Warpay')
@section('breadcrumb', 'Dashboard  /  Lain-Lain  /  Topup Warpay')

@section('content')

<style>
	.help-inline{
		display:none !important;
	}
</style>

<div class="row">
	<div class="col-5">
		<form class="cmxform" id="form-topup" method="get" action="#" enctype="multipart/form-data">
			<div class="card">
				<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Jumlah Kelipatan </h5>
					</div>
				</div>
				<div class="card-body">
					<label> Jumlah (min Rp.250 )</label>
					<div class="input-group col-xs-12">
						<input type="hidden" name="id" id="id">
						<input type="number" class="form-control" style="cursor:default;" id="total" name="total" min=250>
						<span class="input-group-append">
							<button class="btn btn-secondary" style="pointer-events:none;" type="button"> &nbsp; Rupiah &nbsp; </button>
						</span>
					</div>
				</div>
				<div class="card-footer">
					<button type="button" id="btn-cancel" class="btn btn-light" style="display:none;"> Cancel </button>
					<button type="submit" id="btn-save"  class="btn btn-primary btn-custom" disabled>Save</button>
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
					<h5 class="card-title"> Daftar Kelipatan </h5>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-hover table-responsive" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>
								<center>#</center>
							</th>
							<th style="width:50px;">ID</th>
							<th style="width:100%;">Kelipatan</th>
							<th style="width:100%;">Warpay</th>
						</tr>
					</thead>
					<tbody id="list-kelipatan">
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')

<script>


	window.onload = async () => {
	
		/* -------------------------------------------------------------------------- */
		/*                                  Variabel                                  */
		/* -------------------------------------------------------------------------- */

		let [ 
			elm_form_topup,
			elm_id,
			elm_btn_save,
			elm_btn_cancel,
			elm_list_kelipatan,
			elm_total,	
			edit_active,
			edit_inactive,
		] = [ 
			__getId('form-topup'),
			__getId('id'),
			__getId('btn-save'),
			__getId('btn-cancel'),
			__getId('list-kelipatan'),
			__getId('total'),
			() => {
					
					sTop();
					elm_total.style.cssText 		= 'border:1px double #3395d0 !important; transition: .3s;'
					elm_btn_save.disabled 			= false;
					elm_btn_cancel.style.cssText 	= 'display:inline-block';
					
					setTimeout(() => {
						elm_total.style.cssText		= 'transition:.3s';
					}, 1000);
				
			},
			() => {
					elm_id.value					= null;
					elm_total.value 				= null;
					elm_btn_save.disabled 			= true;
					elm_btn_save.innerHTML 			= 'Save';
					elm_btn_cancel.style.cssText 	= 'display:none';
				
			}
		];
		
		const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

		function getCurrentToken() {
			return {
				email : `{{ Session::get("email")}}`,
				token: `{{ Session::get("token")}}`,
				by : "{{ Session::get('user')->id }}"
			}
		}

			
		/* -------------------------------------------------------------------------- */
		/*                               Form Validation                              */
		/* -------------------------------------------------------------------------- */
		$('#form-topup').submit((e) => {
			e.preventDefault();
		}).validate(validateForm({ total:'required' }, 'total', updateTopup, saveTopup));


		/* -------------------------------------------------------------------------- */
		/*                             Get Data Kelipatan                             */
		/* -------------------------------------------------------------------------- */

		elm_list_kelipatan.innerHTML = `
		<tr>
			<td colspan="4" style="text-align:center">
				${___iconLoading('black')}
			</td>
		</tr>
		`;

		async function fetchKelipatan() {
			return await(await(await fetch(`{{ env('API_URL') . '/warpay' }}`, __propsPOST)).json()).data;
		}

		async function manageKelipatan() {

			let [ __fetchKelipatan ] = [ 
			/* __fetchKelipatan */ await(await(await fetch(`{{ env('API_URL') . '/warpay' }}`, __propsPOST)).json()).data
			];


			if(__fetchKelipatan && __fetchKelipatan.length > 0) {

				elm_list_kelipatan.innerHTML = '';

				for (const item of __fetchKelipatan) {
					
					let idx = ( __fetchKelipatan.indexOf(item) + 1 );
					
					insertAdjHTML('list-kelipatan', 'beforeend', `
						<tr>
							<td>
								<button class="btn p-0 edit-btn" type="button" id='edit-btn' value='${ JSON.stringify(item) }'>
									<svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
								</button>
								<button class="btn p-0 delete-btn" type="button" id='delete-btn' value='${ JSON.stringify(item) }'>
									<svg viewBox="0 0 24 24" width="19" height="19" stroke="#FF3366" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
								</button>
							</td>
							<td><center> ${ idx } </center></td>
							<td> Rp ${ item.total_ } </td>
							<td> 
								<img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp;
								${ item.warpay } 
							</td>
						</tr>
					`)
				}

				let [
					edit_btn,
					delete_btn 
				] = [ 
					__querySelectorAll('.edit-btn'), 
					__querySelectorAll('.delete-btn') 
				];
				
				/* ------------------------------- edit button ------------------------------ */

				if( edit_btn &&  edit_btn.length ) {
					
					const len = edit_btn.length;
					
					for( let i = 0; i < len; i++ ) {

						edit_btn[i].addEventListener('click', (e) => {
							e.preventDefault();
							
							const val = JSON.parse(e.currentTarget.value);
							
							elm_id.value 	= val.id; 
							elm_total.value = ( val.total );
							elm_total.dispatchEvent(new Event("keyup", {bubbles: true,}))
							
							edit_active();

						})

					}

				}

				/* ------------------------------- delete button ------------------------------ */
				
				if( delete_btn &&  delete_btn.length ) {
					
					const len = delete_btn.length;
					
					for( let i = 0; i < len; i++ ) {

						delete_btn[i].addEventListener('click', (e) => {
							e.preventDefault();
							
							const val 	= JSON.parse(e.currentTarget.value);
							const id	= val.id;

							__swalConfirmation(id);

						})

					}


				}

			}

	}

	manageKelipatan();



		
	/* -------------------------------------------------------------------------- */
	/*                               Event Listener                               */
	/* -------------------------------------------------------------------------- */


	eventListener(elm_btn_cancel.id, (e) => {
		edit_inactive();
	})


	eventListener(elm_total.id, (e) => {
		
		const target = e.currentTarget;

		if(target.value) {
			elm_btn_save.removeAttribute('disabled');
		} else {
			elm_btn_save.setAttribute('disabled', '')
		}

	}, 'keyup')

	async function updateTopup(e, id) {

		if(e) {
			e.preventDefault();
		}

		let formData = __serializeForm(elm_form_topup);
		const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())

		elm_btn_save.innerHTML = 'Saving ' + ___iconLoading();
		elm_btn_save.disabled = true;

		try {
			
			const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
			let res = await fetch(`{{ env('API_URL') . '/warpay-update' }}`, __propsPOST)
			let result = await res.json();

			const {status, message, data} = result;

			if(status) {

				refreshListKelipatan();
    			toastr.success(message, { fadeAway: 10000 });

				edit_inactive();
			} else {
				edit_inactive();
			}

		} catch (e) {
			
		}
	}

	async function saveTopup(e) {

		if(e) {
			e.preventDefault();
		}

		// json stringify
		let formData = __serializeForm(elm_form_topup);
		const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())

		log(newFormData)

		elm_btn_save.innerHTML = 'Saving ' + ___iconLoading();
		elm_btn_save.disabled = true;

		try {
			
			const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
			let res = await fetch(`{{ env('API_URL') . '/warpay-create' }}`, __propsPOST)

			log(res);

			let result = await res.json();

			const {status, message, data} = result;

			if(status) {

				refreshListKelipatan();
    			toastr.success(message, { fadeAway: 10000 });

				edit_inactive();
			} else {
				edit_inactive();
			}


		} catch (e) {
			log(e);
		}
	}

	function refreshListKelipatan(e) {
		if(e) {
			e.preventDefault()
		}

		manageKelipatan();

	}

	async function __swalConfirmation(id, title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin menghapusnya ?') {
            
		return swal({
			title: title,
			text: text,
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(async (willDelete) => {
			if (willDelete) {

				try {

					let res = await fetch(`{{ env('API_URL') . '/warpay/delete/${id}' }}`, Object.assign({}, __propsPOST, {
						method: 'DELETE'
					}))

					let result = await res.json();

					const {status, message} = result;

					if(status) {
						
						refreshListKelipatan();
						toastr.success(message, { fadeAway: 10000 });
					
					} else {
					
						toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
						log(message)
					
					}
				} catch (e) {
					log(e);
				}
			}
		})
	}


}
</script>

@endsection