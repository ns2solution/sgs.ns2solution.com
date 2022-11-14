@extends('layouts.app')

@section('title', '| Brand')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Brand')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float"  id="open-modal-brand" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Brand&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Brand </h5>
					<button class="btn p-0" type="button" id="btn-refresh-brand">
                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                    </button>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="data-table" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>Aksi</th>
							<th>#</th>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Kode</th>
							<th>Nama Brand</th>
                            <th>Principle</th>
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

<div class="modal fade" id="form-brand-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;">
            <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                <h5 class="modal-title" style="color:#fff;">Edit Detail Brand</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="cmxform" id="form-brand" method="get" action="#" enctype="multipart/form-data">
                <div class="modal-body">
                    <fieldset>
                        <div class="form-group" style="margin-bottom:0px;">
                            <input type="hidden" name="id_brand" id="id">
                        </div>
                        <div class="form-group">
                            <label>Logo</label>
                            <input type="file" name="logo" id="brand-logo" class="dropify"  data-height="150" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                        </div>
                        <div class="form-group">
                            <label> Principle </label>
                            <select class="select" id="principle_id" name="principle_id" data-placeholder="-- Pilih Principle --" required>
                                <option></option>
                                @foreach($principle as $a)
                                    <option value="{{ $a->id }}"> {{ $a->name }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label> Kode </label>
                            <input type="text" class="form-control" id="code" name="code" autocomplete="off" maxlength="5" placeholder="Masukkan kode brand" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Brand</label>
                            <input id="brand_name" class="form-control" name="brand_name" type="text" minlength="3" autocomplete="off" placeholder="Masukkan nama brand">
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close-brand"> Batal </button>
                    <button type="submit" class="btn btn-primary btn-custom" id="save-brand"> Simpan </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-view" tabindex="-1">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content" data-dismiss="modal" style="border:none;background-color:transparent;">
			<div class="modal-body">
				<div class="form-group">
					<center>
						<img src="" style="max-height:80vh;max-width:100%;" draggable="false" id="img-view">
					</center>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<script>

    $(document).ready(async function () {


        // ----------- variable ---------------------


        let elm_brand_id = __getId('id')
        let elm_name = __getId('prod-type-id')
        let elm_form_brand = __getId('form-brand')
        let elm_save_brand = __getId('save-brand')
        let elm_close_brand = __getId('close-brand')
        let elm_btn_refresh_brand = __getId('btn-refresh-brand')
        let elm_modal_header = __getId('modal-title')

        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

        var drEvent = $('#brand-logo').dropify({
            messages: {
                default: 'Drag atau drop untuk memilih gambar',
                replace: 'Ganti',
                remove:  'Hapus',
                error:   'error'
            }
        });

        drEvent.on('dropify.afterClear', function(event, element){
            elm_save_brand.disabled = true;
        });

        drEvent.on('dropify.fileReady', function(event, element){
            elm_save_brand.disabled = false;
        });

        drEvent.on('dropify.errors', function(event, element){
            elm_save_brand.disabled = true;
        });

        const isSuccessfullyGettingData = {
            brand: false
        }


        let table = null
        let elm_open_modal = __getId('open-modal-brand')

        // ----------- fetch data ------------------

        $.fn.dataTable.ext.errMode = 'none';

        table = $('#data-table').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            autoWidth: true,
            order: [[0, 'ASC']],
            ajax:{
                url: "{{ env('API_URL') . '/brand/data-table' }}",
                dataType: 'JSON',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    token: "{{ Session::get('token') }}",
                    email: "{{ Session::get('email') }}"
                }
            },
            columns: [
                {
                    sClass: 'text-center',
                    orderable: false,
                    render: function(){
                        return `
                            <button class="btn p-0" type="button" id='edit-btn'>
                                <svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            &nbsp;&nbsp;
                            <button class="btn p-0" type="button" id='delete-btn'>
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
                    data: 'id_brand',
                    width: '35px',
                    sClass: 'text-center'
                },
                {
                    data: 'brand_logo',
                    sClass: 'text-center',
                    orderable: false,
                    render: function(data){
                        if(data != '-'){
                            return `<img src="{{ env('API_URL') . '/' }}${data}" id="zoom-foto" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();">`;
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
                    data: 'brand_name',
                    width: '30%'
                },
                {
                    data: 'principle',
                    width: '30%'
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


        // ----------- init ---------------------

        const rulesForm = {
            rules: {
                brand_name : 'required',
            },
            ...rulesValidateGlobal,
            submitHandler:(form, e) => {
                e.preventDefault();

                const id =  elm_brand_id.value ? elm_brand_id.value : null;

                if(id) {
                    updateBrand(e, id)
                } else {
                    saveBrand(e)
                }

                return false;
            }
        }

        $('#form-brand').submit((e) => {
            e.preventDefault();
        }).validate(rulesForm);


         // ----------- function ---------------------


         async function saveBrand(e) {
            if(e) {
                e.preventDefault();
            }

            elm_save_brand.innerHTML = 'Menyimpan ' + ___iconLoading();
            elm_save_brand.disabled = true;

            let formData = new FormData(elm_form_brand);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')

            $.ajax({
                url:`{{ env('API_URL') . '/brand/create' }}`,
                method:"POST",
                data: formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(result){
                    const {status, message, data} = result;


                    if(status) {

                        refreshBrandDT();

                        toastr.success(message, { fadeAway: 10000 });
                        elm_save_brand.innerHTML = 'Simpan';
                        elm_save_brand.disabled = false;

                        $('#form-brand-modal').modal('hide')
                    } else {
                        elm_save_brand.disabled = false;
                        elm_save_brand.innerHTML = 'Simpan';

                        error(message);

                        $('#form-brand-modal').modal('hide')
                    }
                },
                error: function(err) {
                    log(err);
                    const msg = err.responseJSON.message;

                    toastr.error(msg,  { fadeAway: 10000 });

                    elm_save_brand.innerHTML = 'Simpan';

                }
            })


        }

        async function updateBrand(e, id) {
            if(e) {
                e.preventDefault();
            }

            elm_save_brand.innerHTML = 'Menyimpan ' + ___iconLoading();
            elm_save_brand.disabled = true;


            let formData = new FormData(elm_form_brand);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')

            $.ajax({
                url:`{{ env('API_URL') . '/brand/update/${id}' }}`,
                method:"POST",
                data: formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(result){
                    const {status, message, data} = result;

                    if(status) {

                        refreshBrandDT();

                        toastr.success(message, { fadeAway: 10000 });
                        elm_save_brand.innerHTML = 'Simpan';
                        elm_save_brand.disabled = false;

                        $('#form-brand-modal').modal('hide')
                    } else {
                        elm_save_brand.disabled = false;
                        elm_save_brand.innerHTML = 'Simpan';

                        log(message);
                        toastr.error(message,  { fadeAway: 10000 });

                        $('#form-brand-modal').modal('hide')
                    }
                },
                error: function(err) {

                    const msg = err.responseJSON.message;

                    toastr.error(msg,  { fadeAway: 10000 });

                    // $('#form-brand-modal').modal('hide')

                    elm_save_brand.innerHTML = 'Simpan';
                }
            })


        }



        function getCurrentToken() {
            return {
                email : `{{ Session::get("email")}}`,
                token: `{{ Session::get("token")}}`,
                by : "{{ Session::get('user')->id }}"
            }
        }

        function resetForm() {
            var drEvent = $('#brand-logo').dropify();
            drEvent = drEvent.data('dropify');
            drEvent.resetPreview();
            drEvent.clearElement();

            for(const elm of elm_form_brand) {
                elm.value = '';
                if(elm.type == 'select-one') {
                    elm.dispatchEvent(new Event("change", {bubbles: true,}));
                }
            }
        }


        async function closeModalBrand(e) {
            if(e) {
                e.preventDefault();
                $('#form-brand-modal').modal('hide')
            }
        }

        async function openModalBrand() {

            $('#modal-title').empty().append('Tambah Brand');

            $('#form-brand-modal').modal('show');

            // manage error
            $('#form-brand input.has-error').removeClass('has-error');
            $('#form-brand textarea.has-error').removeClass('has-error');
            $('#form-brand select.has-error').removeClass('has-error');
            $('#form-brand .help-inline.text-danger').remove()

            $('.dropify-error').empty();
            //$('.dropify-errors-container').empty();

            resetForm();

            elm_save_brand.disabled = false;
            elm_save_brand.removeAttribute('disabled', '')
        }


        async function __swalConfirmation(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin menghapusnya ?', id) {
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

                        let res = await fetch(`{{ env('API_URL') . '/brand/delete/${id}' }}`, Object.assign({}, __propsPOST, {
                            method: 'DELETE'
                        }))

                        let result = await res.json();

                        const {status, message} = result;

                        if(status) {
                            refreshBrandDT();
                            toastr.success(message, { fadeAway: 10000 });
                        } else {
                            toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
                            console.error(message)
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }
            })
        }

        function refreshBrandDT(e) {
            if(e) {
                e.preventDefault()
            }
            table.ajax.reload(null, false);
        }

        // ----------- event listener----------------

        elm_btn_refresh_brand.addEventListener('click', refreshBrandDT)
        elm_open_modal.addEventListener('click', openModalBrand);
        elm_close_brand.addEventListener('click', closeModalBrand);


        $('#data-table tbody').on('click', '#delete-btn', function () {
            const data = table.row( $(this).parents('tr') ).data();

            __swalConfirmation('Apakah anda yakin ?', 'Apakah anda yakin ingin menghapusnya ?', data.id_brand)

        });


        $('#data-table tbody').on('click', '#zoom-foto', function () {
            const data = table.row( $(this).parents('tr') ).data();
            let img = data.brand_logo

            __querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

            $('#modal-view').modal('show');
        });

        $('#data-table tbody').on('click', '#edit-btn', function () {
            // manage error
            $('#form-brand input.has-error').removeClass('has-error');
            $('#form-brand textarea.has-error').removeClass('has-error');
            $('#form-brand select.has-error').removeClass('has-error');
            $('#form-brand .help-inline.text-danger').remove()
            $('.dropify-error').empty();
            $('.dropify-errors-container').empty();

            $('#modal-title').empty().append('Edit Detail Brand');

            resetForm();

            const data = table.row( $(this).parents('tr') ).data();

            elm_brand_id.value = data.id_brand;

            $("select#principle_id option").each(function(){
                this.selected = (this.text == data.principle);
            }).change();

            for (const key in data) {
                for(const elm of elm_form_brand) {
                    if(key == elm.name) {
                        if(elm.type == 'text' || elm.type == 'hidden' || elm.type == 'textarea') {
                            elm.value = data[key];
                        }
                    }
                    if(key == 'brand_logo' && elm.name == 'logo') {

                        var drEvent = $('#brand-logo').dropify({
                            defaultFile: `{{ env('API_URL') . '/' }}${data[key]}`,
                        });

                        drEvent = drEvent.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.settings.defaultFile = `{{ env('API_URL') . '/' }}${data[key]}`;
                        drEvent.destroy();
                        drEvent.init();

                        $('.dropify-render > img').attr('src', `{{ env('API_URL') . '/' }}${data[key]}`);

                    }
                }
            }

            elm_save_brand.disabled = false;

            $('#form-brand-modal').modal('show');

        });

    });

</script>

@endsection
