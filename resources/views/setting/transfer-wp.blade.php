@extends('layouts.app')

@section('title', '| Transfer Warpay')
@section('breadcrumb', 'Dashboard  /  Lain-Lain  /  Transfer Warpay')

@section('content')
    
<div class="row">
	<div class="col-12 col-md-6">
		<div class="card">
			<form id="form-transfer-wp">
				<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Kredit Warpay </h5>
					</div>
				</div>
				<div class="card-body">
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
						<label> Warrior </label>
						<select class="select" id="user-id" name="user_id" data-placeholder="-- Pilih User --" >
						</select>
					</div>
					<div class="form-group">
						<label> Jumlah </label>
						<input type="number" class="form-control" id="warpay" name="warpay" autocomplete="off" placeholder="Masukkan jumlah warpay yang ingin ditransfer">
					</div>
				</div>
				<div class="card-footer" id="dynamic-btn">
					<button type="submit" id="btn-transfer" class="btn btn-primary btn-custom" >Save</button>
				</div>
			</form>
		</div>
	</div>

    <div class="col-12 col-md-6">
		<div class="card">
			<form id="form-transfer-wp-2">
				<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Debit Warpay </h5>
					</div>
				</div>
				<div class="card-body">
                    <input type="hidden" name="id" id="id-2">
                    <div class="form-group">
						<label> Warrior </label>
						<select class="select" id="user-id-2" name="user_id" data-placeholder="-- Pilih User --" >
						</select>
					</div>
					<div class="form-group">
						<label> Jumlah </label>
						<input type="number" class="form-control" id="warpay" name="warpay" autocomplete="off" placeholder="Masukkan jumlah warpay yang ingin ditransfer">
					</div>
				</div>
				<div class="card-footer" id="dynamic-btn">
					<button type="submit" id="btn-transfer-2" class="btn btn-primary btn-custom" >Save</button>
				</div>
			</form>
		</div>
	</div>
</div>


@endsection

@section('js')

<script>

window.onload = async () => {
    
    let [ 
        elm_form_kredit_wp,
        elm_form_debit_wp,
        elm_user_id,
        elm_user_id2,
        elm_btn_save1,
        elm_btn_save2,
    ] = [
        __getId('form-transfer-wp'),
        __getId('form-transfer-wp-2'),
        __getId('user-id'),
        __getId('user-id-2'),
        __getId('btn-transfer'),
        __getId('btn-transfer-2'),
    ];


    $('select').select2();

    $('select').on('select2:close', function (e) {
        $(this).valid();
    });

    const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})


    function customPost(data){
        return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data))})
    }

    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
            by : "{{ Session::get('user')->id }}"
        }
    }


    elm_user_id.innerHTML    = elm_choose('Pilih Warrior')
    elm_user_id2.innerHTML    = elm_choose('Pilih Warrior')
    
    const warriors = await(await(await fetch(`{{ env('API_URL') . '/buyers' }}`, customPost({}))).json()).data;

    if(warriors) {

        for (const w of warriors) {
        
            let newOption = ___createOpt(w.id, w.fullname + ' | Warpay : ' + __toRp(w.warpay));
            elm_user_id.appendChild(newOption);
            elm_user_id.dispatchEvent(new Event("change", {bubbles: true,}));
        }


        for (const w of warriors) {
        
            let newOption = ___createOpt(w.id, w.fullname + ' | Warpay : ' + __toRp(w.warpay));
            elm_user_id2.appendChild(newOption);
            elm_user_id2.dispatchEvent(new Event("change", {bubbles: true,}));
        }

    } 


    $('#form-transfer-wp').submit((e) => {
        e.preventDefault();
    }).validate(validateForm({ user_id:'required', warpay:'required' }, 'id', saveTransferWP1, saveTransferWP1));


    $('#form-transfer-wp-2').submit((e) => {
        e.preventDefault();
    }).validate(validateForm({ user_id:'required', warpay:'required' }, 'id-2', saveTransferWP2, saveTransferWP2));


    async function saveTransferWP1(e) {

        if(e) {
            e.preventDefault();
        }

        // json stringify
        let formData = __serializeForm(elm_form_kredit_wp);
        const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())

        elm_btn_save1.innerHTML = 'Saving ' + ___iconLoading();
        elm_btn_save1.disabled = true;

        try {
            
            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
            let res = await fetch(`{{ env('API_URL') . '/warpay-transfer/kredit' }}`, __propsPOST)


            let result = await res.json();

            const {status, message, data} = result;

            if(status) {

                toastr.success(message, { fadeAway: 10000 });
                elm_btn_save1.disabled = false;
                elm_btn_save1.innerHTML = 'Save';

                __resetForm(elm_form_kredit_wp)
                
                setTimeout(() => {
                    location.reload();
                }, 300);

            } else {
                elm_btn_save1.disabled = false;
                elm_btn_save1.innerHTML = 'Save';
            }


        } catch (e) {
            log(e);
        }
    }


    async function saveTransferWP2(e) {

        if(e) {
            e.preventDefault();
        }

        // json stringify
        let formData = __serializeForm(elm_form_debit_wp);
        const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())

        elm_btn_save2.innerHTML = 'Saving ' + ___iconLoading();
        elm_btn_save2.disabled = true;

        try {
            
            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
            let res = await fetch(`{{ env('API_URL') . '/warpay-transfer/debit' }}`, __propsPOST)


            let result = await res.json();

            const {status, message, data} = result;

            if(status) {

                toastr.success(message, { fadeAway: 10000 });
                elm_btn_save2.disabled = false;
                elm_btn_save2.innerHTML = 'Save';

                __resetForm(elm_form_debit_wp)

                              
                setTimeout(() => {
                    location.reload();
                }, 300);


            } else {
                elm_btn_save2.disabled = false;
                elm_btn_save2.innerHTML = 'Save';
            
                toastr.error(message,  { fadeAway: 10000 });
            }


        } catch (e) {
            log(e);
        }
    }

}
</script>

@endsection