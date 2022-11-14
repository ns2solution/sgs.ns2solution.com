@extends('layouts.app')

@section('title', '| Produk Poin')
@section('breadcrumb', 'Dashboard  /  Produk  /  Produk Poin')

@section('content')

<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" onclick="tambah()" style="font-size:17px;">
	&nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
	Tambah Produk Poin&nbsp;
</button>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
				<div class="d-flex justify-content-between align-items-baseline">
					<h5 class="card-title"> Produk Poin </h5>
					<button class="btn p-0" type="button" onclick="$('#dataTable').DataTable().ajax.reload(null, false);">
                    	<i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                    </button>
				</div>
			</div>
			<div class="card-body">
				<table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="dataTable" style="border-top:solid 1px #ddd;width:100%;">
					<thead>
						<tr>
							<th>Aksi</th>
							<th>#</th>
							<th>ID</th>
							<th>Judul</th>
							<th>Foto</th>
							<th>Tipe</th>
							<th>Produk</th>
							<th>Warpay</th>
							<th>Minimal Poin</th>
							<th>Stok</th>
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

<div class="modal fade" id="modalEdit" tabindex="-1">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="border:none;">
			<form method="get" id="tukar-form" enctype="multipart/form-data">
				<div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
					<h5 class="modal-title" style="color:#fff;">Modal Produk Poin</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label> Gambar </label>
						<input type="file" class="form-control" id="product_image" name="product_image" required>
					</div>
					<div class="form-group">
						<label> Judul </label>
						<input type="text" class="form-control" id="title" name="title" autocomplete="off" required>
					</div>
					<div class="form-group">
						<label> Tipe Produk Poin</label>
						<select class="form-control" id="type" name="type" onchange="changeType(this.value)" required>
							<option value=""> -- Pilih Tipe -- </option>
							<option value="1">Produk</option>
							<option value="2">Warpay</option>
						</select>
					</div>
					<div class="form-group" style="display: none;" id="typeDiv">

					</div>
					<div class="form-group">
						<label> Harga Poin </label>
						<input type="text" class="form-control" id="min_poin" name="min_poin" data-mask="000.000" data-mask-reverse="true" autocomplete="off" required>
					</div>
					<div class="form-group">
						<label> Stok </label>
						<input type="number" class="form-control" id="stok" name="stok" autocomplete="off" min='1' required>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" id="type_form">
					<input type="hidden" id="id">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"> Batal </button>
					<button type="submit" class="btn btn-primary btn-custom"> Simpan </button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modalView" tabindex="-1">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content" data-dismiss="modal" style="border:none;background-color:transparent;">
			<div class="modal-body">
				<div class="form-group">
					<center>
						<img src="" style="max-height:80vh;max-width:100%;" draggable="false" id="imgView">
					</center>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js')

<script>

	function changeType(val){
		if (val == 1) {
			var html = `<label> Nama Produk </label>
						<input type="text" class="form-control" id="product_name" name="product_name" autocomplete="off" required>`
			$('#typeDiv').show().empty().append(html)
		}else{
			var html = `<label> Jumlah Warpay </label>
						<input type="text" class="form-control" id="warpay" name="warpay" autocomplete="off" required>`
			$('#typeDiv').show().empty().append(html)
			$('#warpay').mask('000.000.000', {reverse: true});
		}

	}

	// $(function(){
	// 	$('#product_image').dropify()
	// })

	var table = null;
	$.fn.dataTable.ext.errMode = 'none';
	table = $('#dataTable').DataTable({
		pageLength: 10,
		processing: true,
		serverSide: true,
		order: [[0, 'ASC']],
		ajax:{
			url: "{{ env('API_URL') . '/tukar_poin/data-table' }}",
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
	        			<button class="btn p-0" type="button" id='edit'>
                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#2A8FCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    	</button>
                    	&nbsp;&nbsp;
	        			<button class="btn p-0" type="button" id='del'>
                    		<svg viewBox="0 0 24 24" width="19" height="19" stroke="#FF3366" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    	</button>
	        		`;
				}
			},
			{
				data: 'no',
                orderable: false
			},
			{
				data: 'id'
			},
			{
				data: 'title',
				width: '40%'
			},
			{
				data: 'product_image',
				sClass: 'text-center',
                orderable: false,
				render: function(data){
					if(data != '-'){
						return `<img src="{{ env('API_URL') . '/' }}${data}" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();" onclick="zoomFoto(1, this);">`;
					}else{
						return '-';
					}
				}
			},
			{
				data: 'type'
			},
			{
				data: 'product_name'
			},
			{
				data: 'warpay'
			},
			{
				data: 'min_poin'
			},
			{
				data: 'stok'
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

	$('#dataTable tbody').on('click', '#del', function () {
        var data = table.row( $(this).parents('tr') ).data();
        // console.log(data)
        // __swalConfirmation('Apakah anda yakin ?', 'Apakah anda yakin ingin menghapusnya ?', data.id)
        return swal({
		  title: 'Apakah anda yakin menghapus produk?',
		  icon: 'warning',
		  buttons: {
		    cancel: true,
		    confirm: true,
		  },
		}).then((result) => {
		  if (result) {
		    
		    $.ajax({
		    	type:'post',
		    	url:"{{ env('API_URL') . '/tukar_poin/delete/' }}"+data.id,
		    	data:{
		    		token: "{{ Session::get('token') }}",
					email: "{{ Session::get('email') }}",
					id:data.id
		    	},
		    	success:function(res){
		    		console.log(res)
		    		swal(
				      'Deleted!',
				      'Your file has been deleted.',
				      'success'
				    )
				    $('#dataTable').DataTable().ajax.reload(null, false)
		    	},
		    	error:function(err){
		    		console.log(err)
		    	}
		    })
		  }
		})
    });

    // async function __swalConfirmation(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin menghapusnya ?', id) {
    //         return swal({
    //             title: title,
    //             text: text,
    //             icon: "warning",
    //             buttons: true,
    //             dangerMode: true,
    //         })
    //         .then(async (willDelete) => {
    //             if (willDelete) {

    //                 try {

    //                     let res = await fetch(`{{ env('API_URL') . '/tukar_poin/delete/${id}' }}`, Object.assign({}, __propsPOST, {
    //                         method: 'DELETE'
    //                     }))

    //                     let result = await res.json();

    //                     const {status, message} = result;

    //                     if(status) {
    //                         refreshProductDT();
    //                         toastr.success(message, { fadeAway: 10000 });
    //                     } else {
    //                         toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
    //                         console.error(message)
    //                     }
    //                 } catch (error) {
    //                     console.error(error);
    //                 }
    //             }
    //         })
    //     }

	$('#dataTable tbody').on('click', '#edit', function () {
        var data = table.row( $(this).parents('tr') ).data();
        console.log(data)
        $('.modal-title').text('Edit Produk Poin')
        $('#type_form').val('edit')
        $('#modalEdit').modal('show')
        $('#id').val(data.id)
        $('#title').val(data.title)
        $('#type').val(data.type)
        $('#min_poin').val(data.min_poin)
        // $('#product_image').attr('data-default-file','{{ env('API_URL') }}/'+data.product_image)
        // $('#product_image').dropify()
        var drEvent = $(`#product_image`).dropify({
            defaultFile: `{{ env('API_URL') }}${data.product_image}`,
        });
        drEvent = drEvent.data('dropify');
        drEvent.resetPreview();
        drEvent.clearElement();
        drEvent.settings.defaultFile = `{{ env('API_URL') }}/${data.product_image}`;
        drEvent.destroy();
        drEvent.init();
        if (data.type == 1) {
			var html = `<label> Nama Produk </label>
						<input type="text" class="form-control" id="product_name" name="product_name" autocomplete="off" required>`
			$('#typeDiv').show().empty().append(html)
	        $('#product_name').val(data.product_name)
		}else{
			var html = `<label> Jumlah Warpay </label>
						<input type="text" class="form-control" id="warpay" name="warpay" autocomplete="off" required>`
			$('#typeDiv').show().empty().append(html)
	        $('#warpay').val(data.warpay)
		}


    });

	$('#tukar-form').on('submit', function(e){
		e.preventDefault();

		console.log($('#type_form').val())
		// return;
		// let data = $(this).serialize() + '&email={{ Session::get("email")}}&token={{ Session::get("token")}}';
		let data = new FormData(document.getElementById('tukar-form'));
		data.append('token', '{{ Session::get("token")}}')
		data.append('email', '{{ Session::get("email")}}')

		if ($('#type_form').val() == 'add') {
			$.ajax({
				type:'post',
				url:"{{ env('API_URL') . '/tukar_poin' }}",
				contentType: false,
	            processData: false,
				data:data,
				success:function(res){
					console.log(res)
					toastr.success(res.message, { fadeAway: 10000 });
					$('#dataTable').DataTable().ajax.reload(null, false)
					$('#modalEdit').modal('hide')
				},
				error:function(err){
					console.log(err)
				}
			})
		}else{
			$.ajax({
				type:'post',
				url:"{{ env('API_URL') . '/tukar_poin/update/' }}"+$('#id').val(),
				contentType: false,
	            processData: false,
				data:data,
				success:function(res){
					console.log(res)
					toastr.success(res.message, { fadeAway: 10000 });
					$('#dataTable').DataTable().ajax.reload(null, false)
					$('#modalEdit').modal('hide')
				},
				error:function(err){
					console.log(err)
				}
			})
		}

		// $('#dataTable').DataTable().ajax.reload(null, false)
		
	})

	function tambah(){
		$('.modal-title').text('Tambah Produk Poin')
		$('#modalEdit').modal('show')
		$('#type_form').val('add')
		clearForm()
	}

	function clearForm(){
		$('#title').val('')
		$('#type').prop('selectedIndex',0)
		$('#product_name').val('')
		$('#warpay').val('')
		$('#min_poin').val('')
		$('#stok').val('')
		$('#typeDiv').empty()
		$('#product_image').removeAttr('data-default-file')
		var drEvent = $(`#product_image`).dropify({
            defaultFile: ``,
        });
        drEvent = drEvent.data('dropify');
        drEvent.resetPreview();
        drEvent.clearElement();
        drEvent.settings.defaultFile = ``;
        drEvent.destroy();
        drEvent.init();
	}

	{{-- Zoom Image --}}

	function zoomFoto(type, sel){

		var data = table.row($(sel).closest('tr')).data();

		var title = 'Logo';
		var img  = data[Object.keys(data)[3]];

		$('#modalView').find('.modal-title').empty().append(title);

		$('#imgView').attr('src', "{{ env('API_URL') . '/' }}" + img);

		$('#modalView').modal('show');

	}

</script>

@endsection