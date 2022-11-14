@extends('layouts.app')

@section('title', '| Terms & Condition')
@section('breadcrumb', 'Dashboard  /  Lain-Lain  /  Terms & Condition')

@section('content')

<style>
	.help-inline{
		display:none !important;
	}
</style>

<!-- <div class="row">
	<div class="col-5">
		<form class="cmxform" id="form-terms-condition" method="get" action="#">
			<div class="card">
				<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Terms & Condition </h5>
					</div>
				</div>
				<div class="card-body">
						<input type="hidden" name="id" id="id">
						<textarea class="form-control" style="cursor:default;" id="content" name="content">
</div>
				<div class="card-footer">
					<button type="submit" id="btn-save"  class="btn btn-primary btn-custom" disabled>Save</button>
				</div>
			</div>
		</form>
	</div>
</div> -->


<div class="row">
	<div class="col-12">
		<form class="cmxform" id="form-terms-condition">
			<div class="card">
				<div class="card-header d-flex justify-content-between" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
					<div class="d-flex justify-content-between align-items-baseline">
						<h5 class="card-title"> Terms & Condition </h5>
					</div>
                    <div id="info-update"> </div>
				</div>
				<div class="card-body">
						<input type="hidden" name="id" id="id">
                        <textarea class="form-control" id="content" name="content" rows=22></textarea>
				</div>
				<div class="card-footer">
					<button type="button" id="btn-cancel" class="btn btn-light" style="display:none;"> Cancel </button>
					<button type="submit" id="btn-save"  class="btn btn-primary btn-custom">Save</button>
				</div>
			</div>
		</form>
	</div>

</div>


@endsection

@section('js')

<script>


	window.onload = async () => {
    
        let [ 
            elm_form_terms_condition,
            elm_id,
            elm_content,
            elm_btn_save,
            elm_info
        ] = [
            __getId('form-terms-condition'),
            __getId('id'),
            __getId('content'),
            __getId('btn-save'),
            __getId('info-update')
        ];

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


		const fetchTermsCondition = await(await(await fetch(`{{ env('API_URL') . '/terms-condition' }}`, customPost({type:'WEB'}))).json()).data;


        $('#form-terms-condition').submit((e) => {
			e.preventDefault();
		}).validate(validateForm({ content:'required' }, 'id', updateTermsCondition, saveTermsCondition));

        if(fetchTermsCondition) {

            const {id, content, updated_at} = fetchTermsCondition;

            elm_id.value        = id;
            elm_info.innerHTML  = `Terakhir diperbaharui : ${__dateYYYYMMDDHis(updated_at)}`; 
            elm_content.value   = content;

        } else {

            elm_id.value = null;

        }


        async function updateTermsCondition(e, id) {

            if(e) {
                e.preventDefault();
            }

            let formData = __serializeForm(elm_form_terms_condition);
            const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())

            elm_btn_save.innerHTML = 'Saving ' + ___iconLoading();
            elm_btn_save.disabled = true;

            try {
                
                const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
                let res = await fetch(`{{ env('API_URL') . '/terms-condition/update' }}`, __propsPOST)
                let result = await res.json();

                const {status, message, data} = result;

                if(status) {

                    elm_info.innerHTML  = `Terakhir diperbaharui : ${__dateYYYYMMDDHis(data.updated_at)}`; 
                    toastr.success(message, { fadeAway: 10000 });
                    elm_btn_save.innerHTML = 'Save';
                    elm_btn_save.disabled = false;

                } else {
                    elm_btn_save.disabled = false;
                    elm_btn_save.innerHTML = 'Save';
                }

            } catch (e) {
                
            }
        }

        async function saveTermsCondition(e) {

            if(e) {
                e.preventDefault();
            }

            // json stringify
            let formData = __serializeForm(elm_form_terms_condition);
            const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())

            elm_btn_save.innerHTML = 'Saving ' + ___iconLoading();
            elm_btn_save.disabled = true;

            try {
                
                const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
                let res = await fetch(`{{ env('API_URL') . '/terms-condition/update' }}`, __propsPOST)


                let result = await res.json();

                const {status, message, data} = result;

                if(status) {

                    elm_info.innerHTML  = `Terakhir diperbaharui : ${__dateYYYYMMDDHis(data.updated_at)}`; 
                    toastr.success(message, { fadeAway: 10000 });
                    elm_btn_save.disabled = false;
                    elm_btn_save.innerHTML = 'Save';

                } else {
                    elm_btn_save.disabled = false;
                    elm_btn_save.innerHTML = 'Save';
                }


            } catch (e) {
                log(e);
            }
        }

    }
</script>

@endsection