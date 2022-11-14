@extends('layouts.app')

@section('title', '| Warriors')
@section('breadcrumb', 'Dashboard  /  User Management  /  Warriors')

@section('content')
<style>
    input{
        text-align: left !important;
    }
    .waiting-for-fetch-data{
        position: fixed;
        background: rgb(0 0 0 / 0.53);
        height: 100%;
        width: 100vw;
        top: 0;
        font-weight: 500;
        z-index: 999 !important;
        font-size: 24px;
        color: #fff;
        justify-content: center;
        align-items: center;
        left: 0;
    }
    .waiting-for-fetch-data.active{
        display: flex;
    }
    .waiting-for-fetch-data.inactive{
        display:none;
    }
</style>
<div class="waiting-for-fetch-data inactive" id="waiting-for-fetch-data">

    <svg width="25" viewBox="-2 -2 42 42" class="mr-3" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-3">
        <g fill="none" fill-rule="evenodd">
            <g transform="translate(1 1)" stroke-width="4">
                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                <path d="M36 18c0-9.94-8.06-18-18-18" transform="rotate(114.132 18 18)">
                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                </path>
            </g>
        </g>
    </svg>

    Sedang memuat data ...
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Warriors </h5>
					<button class="btn p-0" type="button" id="btn-refresh-buyer">
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
                            <th>Fullname</th>
                            <th>Jenis Kelamin</th>
                            <th>Photo</th>
							<th>Photo KTP</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Active</th>
                            <th>Postal Code</th>
							<th>Address</th>
							<th>Created At</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="form-buyer-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;">
            <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                <h5 class="modal-title" id="modal-title" style="color:#fff;">Edit Detail Buyer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="cmxform" id="form-buyer" method="get" action="#" enctype="multipart/form-data">
                <div class="modal-body">
                    <fieldset>
                        <div class="form-group" style="margin-bottom:0px;">
                            <input type="hidden" name="id" id="id">
                        </div>
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" name="photo" id="photo" class="dropify"  data-height="150" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                        </div>
                        <div class="form-group">
                            <label>Photo KTP</label>
                            <input type="file" name="photo_ktp" id="photo-ktp" class="dropify"  data-height="150" data-errors-position="outside" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M"/ >
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="0">Tidak Aktif</option>
                                <option value="1">Aktif</option>
						    </select>
                    </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="close-buyer"> Tutup </button>
                    <button type="submit" class="btn btn-primary btn-custom d-none" id="save-buyer"> Simpan </button>
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

    let elm_waiting_for_fetch_data  = __getId('waiting-for-fetch-data')
    let elm_form_buyer              = __getId('form-buyer')
    let elm_save_buyer              = __getId('save-buyer')
    let elm_close_buyer             = __getId('close-buyer')
    let elm_buyer_id                = __getId('id')


    $(document).ready(async function () {

        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

        function getCurrentToken() {
            return {
                email : `{{ Session::get("email")}}`,
                token: `{{ Session::get("token")}}`,
                by : "{{ Session::get('user')->id }}"
            }
        }


        async function closeModalBuyer(e) {
            if(e) {
                e.preventDefault();
                $('#form-buyer-modal').modal('hide')
            }
        }


        elm_close_buyer.addEventListener('click', closeModalBuyer);


        // ----------- fetch data ------------------

        $.fn.dataTable.ext.errMode = 'none';

        table = $('#data-table').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            autoWidth: true,
            order: [[2, 'DESC']],
            ajax:{
                url: "{{ env('API_URL') . '/buyer/data-table' }}",
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
                    sClass: 'text-center'
                },
                {
                    data: 'fullname',
                    sClass: 'text-center',

                },
                {
                    data: 'gender',
                    sClass: 'text-center',

                },
                {
                    data: 'photo',
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
                    data: 'photo_ktp',
                    orderable: false,
                    render: function(data){
                        if(data != '-'){
                            return `<img src="{{ env('API_URL') . '/' }}${data}" id="zoom-foto-ktp" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();">`;
                        }else{
                            return '-';
                        }
                    }
                },
                {
                    data: 'email',
                    width: '30%'
                },
                {
                    data: 'phone',
                    width: '30%'
                },
                {
                    data: 'active',
                    width: '30%'
                },
                {
                    data: 'postal_code',
                    width: '30%'
                },
                {
                    data: 'address',
                    width: '30%'
                },
                {
                    data: 'created_at',
                    sClass: 'text-center'
                },
            ]
        });



        var drEvent = $('.dropify').dropify({
            messages: {
                default: 'Drag atau drop untuk memilih gambar',
                replace: 'Ganti',
                remove:  'Hapus',
                error:   'error'
            }
        });

        drEvent.on('dropify.afterClear', function(event, element){
            elm_save_buyer.disabled = true;
        });

        drEvent.on('dropify.fileReady', function(event, element){
            elm_save_buyer.disabled = false;
        });

        drEvent.on('dropify.errors', function(event, element){
            elm_save_buyer.disabled = true;
        });


        function refreshBuyerDT(e) {
            if(e) {
                e.preventDefault()
            }
            table.ajax.reload(null, false);
        }

        // ----------- event listener----------------
        let elm_btn_refresh_buyer = __getId('btn-refresh-buyer')
        elm_btn_refresh_buyer.addEventListener('click', refreshBuyerDT)


        $('#data-table tbody').on('click', '#zoom-foto', function () {
            const data = table.row( $(this).parents('tr') ).data();
            let img = data.photo;

            __querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

            $('#modal-view').modal('show');
        });



        $('#data-table tbody').on('click', '#zoom-foto-ktp', function () {
            const data = table.row( $(this).parents('tr') ).data();
            let img = data.photo_ktp;

            __querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

            $('#modal-view').modal('show');
        });


        $('#data-table tbody').on('click', '#edit-btn', function () {
            
            elm_waiting_for_fetch_data.classList.replace('inactive', 'active');
            
            const data = table.row( $(this).parents('tr') ).data();
            
            // debugger;

            __manageError('#form-buyer');

            $('#modal-title').empty().append('View Warrior');

            __resetForm(elm_form_buyer); 

            for (const key in data) {

                for(const elm of elm_form_buyer) {
                    
                    if(elm.type !== 'button') {
                        elm.disabled = true;
                    }
                    
                    if(key == elm.name) {
                        
                        if(elm.type == 'text' || elm.type == 'hidden' || elm.type == 'textarea') {
                            elm.value = data[key];
                        }
                        
                        if(elm.type == 'select-one') {
                            elm.value = data[key];
                            elm.dispatchEvent(new Event("change", {bubbles: true,}));
                            elm.disabled = true;
                        }

                    }

                    if(key == 'photo' && elm.name == 'photo') {
                        var drPhoto = $('#photo').dropify({
                            defaultFile: `{{ env('API_URL') . '/' }}${data[key]}`,
                        });

                        drPhoto = drPhoto.data('dropify');
                        drPhoto.resetPreview();
                        drPhoto.clearElement();
                        drPhoto.settings.defaultFile = `{{ env('API_URL') . '/' }}${data[key]}`;
                        drPhoto.destroy();
                        drPhoto.init();

                        // $('.dropify-render > img').attr('src', `{{ env('API_URL') . '/' }}${data[key]}`);
                    }

                    if(key == 'photo_ktp' && elm.name == 'photo_ktp') {
                        var drPhotoKTP = $('#photo-ktp').dropify({
                            defaultFile: `{{ env('API_URL') . '/' }}${data[key]}`,
                        });

                        drPhotoKTP = drPhotoKTP.data('dropify');
                        drPhotoKTP.resetPreview();
                        drPhotoKTP.clearElement();
                        drPhotoKTP.settings.defaultFile = `{{ env('API_URL') . '/' }}${data[key]}`;
                        drPhotoKTP.destroy();
                        drPhotoKTP.init();

                        // $('.dropify-render > img').attr('src', `{{ env('API_URL') . '/' }}${data[key]}`);
                    } 

                }
            }

            elm_save_buyer.disabled = true;

            elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

            __modalManage('#form-buyer-modal', 'show')


        });


        const rulesForm = {
            rules: {
                active : 'required',
            },
            ...rulesValidateGlobal,
            submitHandler:(form, e) => {
                e.preventDefault();

                const id =  elm_buyer_id.value ? elm_buyer_id.value : null;

                if(id) {
                    updateBuyer(e, id)
                }

                return false;
            }
        }

        $('#form-buyer').submit((e) => {
            e.preventDefault();
        }).validate(rulesForm);


        async function updateBuyer(e, id) {
            if(e) {
                e.preventDefault();
            }

            elm_save_buyer.innerHTML = 'Menyimpan ' + ___iconLoading();
            elm_save_buyer.disabled = true;


            let formData = new FormData(elm_form_buyer);
            formData.append('token', '{{ Session::get("token")}}')
            formData.append('email', '{{ Session::get("email")}}')
            formData.append('by', '{{ Session::get("user")->id }}')

            $.ajax({
                url:`{{ env('API_URL') . '/buyer/update/${id}' }}`,
                method:"POST",
                data: formData,
                dataType:'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success:function(result){
                    const {status, message, data} = result;

                    if(status) {

                        refreshBuyerDT();

                        toastr.success(message, { fadeAway: 10000 });
                        elm_save_buyer.innerHTML = 'Simpan';
                        elm_save_buyer.disabled = false;

                        $('#form-buyer-modal').modal('hide')
                    } else {
                        elm_save_buyer.disabled = false;
                        elm_save_buyer.innerHTML = 'Simpan';

                        log(message);
                        toastr.error(message,  { fadeAway: 10000 });

                        $('#form-buyer-modal').modal('hide')
                    }
                },
                error: function(err) {

                    const msg = err.responseJSON.message;

                    toastr.error(msg,  { fadeAway: 10000 });

                    // $('#form-brand-modal').modal('hide')

                    elm_save_buyer.innerHTML = 'Simpan';
                }
            })


        }


    });

</script>

@endsection
