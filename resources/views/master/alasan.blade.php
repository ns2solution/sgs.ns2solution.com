@extends('layouts.app')

@section('title', '| Alasan')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Alasan')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float"  id="open-modal-alasan" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Alasan&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Alasan </h5>
					<button class="btn p-0" type="button" id="btn-refresh-alasan">
                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                    </button>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="data-table" style="border-top:solid 1px #ddd;">
					<thead>
						<tr>
							<th>Aksi</th>
                            <th>#</th>
                            <th>ID</th>
							<th>Alasan</th>
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

<div class="modal fade" id="form-alasan-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;">
            <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                <h5 class="modal-title" style="color:#fff;">Edit Alasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="cmxform" id="form-alasan" method="get" action="#" enctype="multipart/form-data">
                <div class="modal-body">
                    <fieldset>
                        <div class="form-group" style="margin-bottom:0px;">
                            <input type="hidden" name="id" id="id">
                        </div>
                        <div class="form-group">
                            <label>Alasan</label>
                            <textarea id="alasan" class="form-control" name="alasan" type="text" placeholder="Masukkan deskripsi alasan penolakan"></textarea>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close-alasan"> Batal </button>
                    <button type="submit" class="btn btn-primary btn-custom" id="save-alasan"> Simpan </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<script>

    $(document).ready(async function () {


        // ----------- variable ---------------------


        let elm_alasan_id = __getId('id')
        let elm_alasan = __getId('alasan')
        let elm_form_alasan = __getId('form-alasan')
        let elm_save_alasan = __getId('save-alasan')
        let elm_close_alasan = __getId('close-alasan')
        let elm_btn_refresh_alasan = __getId('btn-refresh-alasan')
        let elm_modal_header = __getId('modal-title')

        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

        const isSuccessfullyGettingData = {
            alasan: false
        }

        let table = null
        let elm_open_modal = __getId('open-modal-alasan')

        // ----------- fetch data ------------------

        $.fn.dataTable.ext.errMode = 'none';

        table = $('#data-table').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            autoWidth: true,
            order: [[0, 'ASC']],
            ajax:{
                url: "{{ env('API_URL') . '/alasan/data-table' }}",
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
                    data: 'id',
                    width: '35px',
                    visible: false,
                    sClass: 'text-center'
                },
                {
                    data: 'alasan',
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
                alasan : 'required',
            },
            ...rulesValidateGlobal,
            submitHandler:(form, e) => {
                e.preventDefault();

                const id =  elm_alasan_id.value ? elm_alasan_id.value : null;

                if(id) {
                    updateAlasan(e, id)
                } else {
                    saveAlasan(e)
                }

                return false;
            }
        }

        $('#form-alasan').submit((e) => {
            e.preventDefault();
        }).validate(rulesForm);


         // ----------- function ---------------------


         async function saveAlasan(e) {
            if(e) {
                e.preventDefault();
            }

            elm_save_alasan.innerHTML = 'Menyimpan ' + ___iconLoading();
            elm_save_alasan.disabled = true;

            let formData = new FormData(elm_form_alasan);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')

            $.ajax({
                url:`{{ env('API_URL') . '/alasan/create' }}`,
                method:"POST",
                data: formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(result){
                    const {status, message, data} = result;


                    if(status) {

                        refreshAlasanDT();

                        toastr.success(message, { fadeAway: 10000 });
                        elm_save_alasan.innerHTML = 'Simpan';
                        elm_save_alasan.disabled = false;

                        $('#form-alasan-modal').modal('hide')
                    } else {
                        elm_save_alasan.disabled = false;
                        elm_save_alasan.innerHTML = 'Simpan';

                        error(message);

                        $('#form-alasan-modal').modal('hide')
                    }
                },
                error: function(err) {
                    log(err);
                    const msg = err.responseJSON.message;

                    toastr.error(msg,  { fadeAway: 10000 });

                    elm_save_alasan.innerHTML = 'Simpan';

                }
            })


        }

        async function updateAlasan(e, id) {
            if(e) {
                e.preventDefault();
            }

            elm_save_alasan.innerHTML = 'Menyimpan ' + ___iconLoading();
            elm_save_alasan.disabled = true;


            let formData = new FormData(elm_form_alasan);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')

            $.ajax({
                url:`{{ env('API_URL') . '/alasan/update/${id}' }}`,
                method:"POST",
                data: formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(result){
                    const {status, message, data} = result;

                    if(status) {

                        refreshAlasanDT();

                        toastr.success(message, { fadeAway: 10000 });
                        elm_save_alasan.innerHTML = 'Simpan';
                        elm_save_alasan.disabled = false;

                        $('#form-alasan-modal').modal('hide')
                    } else {
                        elm_save_alasan.disabled = false;
                        elm_save_alasan.innerHTML = 'Simpan';

                        log(message);
                        toastr.error(message,  { fadeAway: 10000 });

                        $('#form-alasan-modal').modal('hide')
                    }
                },
                error: function(err) {

                    const msg = err.responseJSON.message;

                    toastr.error(msg,  { fadeAway: 10000 });

                    // $('#form-alasan-modal').modal('hide')

                    elm_save_alasan.innerHTML = 'Simpan';
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

            for(const elm of elm_form_alasan) {
                elm.value = '';
            }
        }


        async function closeModalAlasan(e) {
            if(e) {
                e.preventDefault();
                $('#form-alasan-modal').modal('hide')
            }
        }

        async function openModalAlasan() {

            $('#modal-title').empty().append('Tambah Alasan');

            $('#form-alasan-modal').modal('show');

            // manage error
            $('#form-alasan textarea.has-error').removeClass('has-error');
            $('#form-alasan .help-inline.text-danger').remove()
            //$('.dropify-errors-container').empty();

            resetForm();

            elm_save_alasan.disabled = false;
            elm_save_alasan.removeAttribute('disabled', '')
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

                        let res = await fetch(`{{ env('API_URL') . '/alasan/delete/${id}' }}`, Object.assign({}, __propsPOST, {
                            method: 'DELETE'
                        }))

                        let result = await res.json();

                        const {status, message} = result;

                        if(status) {
                            refreshAlasanDT();
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

        function refreshAlasanDT(e) {
            if(e) {
                e.preventDefault()
            }
            table.ajax.reload(null, false);
        }

        // ----------- event listener----------------

        elm_btn_refresh_alasan.addEventListener('click', refreshAlasanDT)
        elm_open_modal.addEventListener('click', openModalAlasan);
        elm_close_alasan.addEventListener('click', closeModalAlasan);


        $('#data-table tbody').on('click', '#delete-btn', function () {
            const data = table.row( $(this).parents('tr') ).data();

            __swalConfirmation('Apakah anda yakin ?', 'Apakah anda yakin ingin menghapusnya ?', data.id)

        });

        $('#data-table tbody').on('click', '#edit-btn', function () {
            // manage error
            $('#form-alasan input.has-error').removeClass('has-error');
            $('#form-alasan textarea.has-error').removeClass('has-error');
            $('#form-alasan .help-inline.text-danger').remove()

            $('#modal-title').empty().append('Edit Detail Alasan');

            resetForm();

            const data = table.row( $(this).parents('tr') ).data();

            elm_alasan_id.value = data.id;

            for (const key in data) {
                for(const elm of elm_form_alasan) {
                    if(key == elm.name) {
                        if(elm.type == 'textarea' || elm.type == 'hidden' || elm.type == 'textarea') {
                            elm.value = data[key];
                        }
                    }
                }
            }

            elm_save_alasan.disabled = false;

            $('#form-alasan-modal').modal('show');

        });

    });

</script>

@endsection
