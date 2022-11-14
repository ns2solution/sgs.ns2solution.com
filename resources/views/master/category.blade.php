@extends('layouts.app')

@section('title', '| Kategori')
@section('breadcrumb', 'Dashboard  /  Master Data  /  Kategori')

@section('content')

<link href="https://cdn3.devexpress.com/jslib/20.1.7/css/dx.common.css" rel="stylesheet">
<link href="https://cdn3.devexpress.com/jslib/20.1.7/css/dx.material.blue.light.css" rel="stylesheet">


<button type="button" class="btn btn-primary btn-icon-text px-3 px-lg-4 btn-gradient float" id="btnAdd" style="font-size:17px;" onclick="tambahKategori()">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>&nbsp;
    Tambah Kategori&nbsp;
</button>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
                <h5 class="card-title"> Master Data Kategori </h5>
            </div>
            <div class="card-body">
                <div id="treelist"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border:none;">
            <form id="kategori-form" enctype="multipart/form-data">
                <div class="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                    <h5 class="modal-title" style="color:#fff;">Edit Detail User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group" id="gambarDiv">
                        <!-- <label> Gambar </label>
                        <input type="file" class="form-control" id="category_image" name="category_image"> -->
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label> Parent </label>
                            <!-- <input type="text" class="form-control" id="title" name="title" autocomplete="off" required> -->
                            <select name="parent_id" id="parent_id" class="form-control" readonly> 
                                
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label> Nama Kategori </label>
                            <input type="text" class="form-control" id="category_name" name="category_name" autocomplete="off" required>
                        </div>
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
<script src="https://cdn3.devexpress.com/jslib/20.1.7/js/dx.all.js"></script>

<script>


    $('#kategori-form').on('submit', function(e){
        e.preventDefault()

        let data = new FormData(document.getElementById('kategori-form'));
        data.append('token', '{{ Session::get("token")}}')
        data.append('email', '{{ Session::get("email")}}')

        if ($('#type_form').val() == 'add') {
            $.ajax({
                type:'post',
                url:"{{ env('API_URL') }}/category/add",
                contentType: false,
                processData: false,
                data:data,
                success:function(res){
                    console.log(res)
                    if (res.data) {
                        toastr.success(res.message, { fadeAway: 10000 });
                    }else{
                        toastr.error(res.message, { fadeAway: 10000 });
                    }
                    $("#treelist").dxTreeList("instance").refresh();
                },
                error:function(err){
                    toastr.error(err.responseJSON.message, { fadeAway: 10000 });
                    console.log(err)
                }
            })
        }else{
            $.ajax({
                type:'post',
                url:"{{ env('API_URL') }}/category/update/"+$('#id').val(),
                contentType: false,
                processData: false,
                data:data,
                success:function(res){
                    console.log(res)
                    if (res.data) {

                        toastr.success(res.message, { fadeAway: 10000 });
                    }else{
                        toastr.error(res.message, { fadeAway: 10000 });

                    }
                    $("#treelist").dxTreeList("instance").refresh();
                },
                error:function(err){
                    console.log(err)
                    toastr.error(err.responseJSON.message, { fadeAway: 10000 });
                }
            })
        }
        $('#modalEdit').modal('hide')
        
    })

    function tambahKategori(){
        $('#type_form').val('add')
        $('.modal-title').text('Tambah Kategori')
        $('#modalEdit').modal('show')
        $('#parent_id').empty()
        $('#parent_id').attr('readonly','readonly')
        $('#parent_id').val('')
        $('#category_name').val('');
        $('#gambarDiv').empty().append(` <label> Gambar </label>
                                <input type="file" class="form-control dropify" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="1M" id="category_image" name="category_image" required>`)
        $('.dropify').dropify();
    }

    var store = new DevExpress.data.CustomStore({
        key: "id",
        load: function() {
            return sendRequest("{{ env('API_URL') }}/category", "", {email: "{{ Session::get('email') }}", token:"{{ Session::get('token') }}"});
        },
        insert: function(values) {
            values.email = "{{ Session::get('email') }}";
            values.token = "{{ Session::get('token') }}";
            return sendRequest("{{ env('API_URL') }}/category", "POST", values);
        },
        update: function(key, values) {
            values.email = "{{ Session::get('email') }}";
            values.token = "{{ Session::get('token') }}";
            return sendRequest("{{ env('API_URL') }}/category/"+key, "POST", values);
        },
        remove: function(key, values) {
            return sendRequest("{{ env('API_URL') }}/category/delete/"+key , "POST", {email: "{{ Session::get('email') }}", token:"{{ Session::get('token') }}"});
        }
    });

    var treeList = $("#treelist").dxTreeList({
        dataSource: store,
        keyExpr: "id",
        parentIdExpr: "parent_id",
        editing: {
            mode: "popup",
            // allowUpdating: true,
            allowDeleting: true,
            // allowAdding: true,
            popup: {
                title: "Categories",
                showTitle: true,
                height: 400,
                width: 400,
                position: { my: "middle", at: "middle", of: window }
            }
        },
        columnAutoWidth: true,
        showRowLines: true,
        showBorders: true,
        onEditorPreparing: function(e) {
            if (e.dataField === "parent_id" && e.row.data.parent_id == 0) {
                e.editorOptions.disabled = true;
                e.editorOptions.value = null;
            }
            if (e.dataField === "parent_id" && e.row.data.id == 1) {
                e.editorOptions.disabled = true;
                e.editorOptions.value = null;
            }
            if (e.dataField === "parent_id" && e.row.data.id != 1) {
                e.editorOptions.disabled = true;

            }
        },
        onInitNewRow: function(e) {
            if (e.data.parent_id == 0) {
                e.data.parent_id = null;
            }
        },
        onContentReady: function(e){
            $('.dx-treelist-header-panel').hide();
        },
        columns: [{
            dataField: "parent_id",
            caption: "Parent",
            visible: false,
            lookup: {
                dataSource: new DevExpress.data.CustomStore({
                    key: "id",
                    byKey: function(key) {
                        return sendRequest("{{ env('API_URL') }}/category",'' , {email: "{{ Session::get('email') }}", token:"{{ Session::get('token') }}", id:key});
                    },
                    load: function() {
                        return sendRequest("{{ env('API_URL') }}/category", "", {email: "{{ Session::get('email') }}", token:"{{ Session::get('token') }}"});
                    }
                }),
                displayExpr: "name",
                valueExpr: "id"
            }
        },{
            dataField: "category_name",
            caption: "Category Name"
        },{
            caption:'Gambar',
            cellTemplate:function(element, info){
                if (info.data.category_image) {
                    element.append(`<img src="${info.data.category_image}" height="75" style="height:70px;width:auto;border-radius:5%;cursor:pointer;" draggable="false" onerror="this.remove();" onclick="zoomFoto(1, this, '${info.data.category_image}');">`)
                }
            }
        },{
            type:'buttons',
            buttons:[{
                icon:'plus',
                visible:function(e){
                    if (e.row.data.parent_id == 0) {
                        return !e.row.isEditing;
                    }
                },
                onClick:function(e){
                    console.log(e)
                    $('.modal-title').text('Tambah Sub Kategori')
                    $('#modalEdit').modal('show')
                    $('#type_form').val('add')
                    $('#gambarDiv').empty()
                    $('#category_name').val('');
                    $.ajax({
                        type:'post',
                        url:"{{ env('API_URL') }}/category/"+e.row.data.id,
                        data:{
                            email: "{{ Session::get('email') }}", 
                            token:"{{ Session::get('token') }}"
                        },
                        success:function(res){
                            console.log(res)
                            if (res.data != '') {
                                let html = `<option value="${res.data[0].id}">${res.data[0].category_name}</option>`
                                $('#parent_id').empty().append(html)
                            }
                        },
                        error:function(err){
                            console.log(err)
                        }
                    })
                }
            },{
                icon:'edit',
                onClick:function(e){
                    console.log(e)
                    $('.modal-title').text('Ubah Kategori')
                    $('#modalEdit').modal('show')
                    $('#type_form').val('edit')
                    $('#id').val(e.row.data.id)
                    $('#category_name').val(e.row.data.category_name)
                    
                    if (e.row.data.parent_id != 0) {
                        $('#gambarDiv').empty()
                        $.ajax({
                            type:'post',
                            url:"{{ env('API_URL') }}/category/"+e.row.data.parent_id,
                            data:{
                                email: "{{ Session::get('email') }}", 
                                token:"{{ Session::get('token') }}"
                            },
                            success:function(res){
                                console.log(res)
                                if (res.data != '') {
                                    let html = `<option value="${res.data[0].id}">${res.data[0].category_name}</option>`
                                    $('#parent_id').empty().append(html)
                                }
                            },
                            error:function(err){
                                console.log(err)
                            }
                        })
                    }else{
                        $('#gambarDiv').empty().append(` <label> Gambar </label>
                                                <input type="file" class="form-control dropify" id="category_image" name="category_image" data-allowed-file-extensions="png jpeg jpg gif" data-max-file-size="2M" required>`)
                        var drEvent = $(`#category_image`).dropify({
                            defaultFile: e.row.data.category_image,
                        });

                        drEvent = drEvent.data('dropify');
                        drEvent.resetPreview();
                        drEvent.clearElement();
                        drEvent.destroy();
                        drEvent.init();
                    }
                }
            },{
                icon:'trash',
                onClick:function(e){
                    var result = DevExpress.ui.dialog.confirm("<i>Apakah anda yakin hapus kategori ini?</i>","Peringatan");
                    result.done(function(dialogResult) {
                        if (dialogResult) {
                            $.ajax({
                                type: 'post',
                                url: "{{ env('API_URL') }}/category/delete/"+e.row.data.id,
                                data:{
                                    email: "{{ Session::get('email') }}", 
                                    token:"{{ Session::get('token') }}"
                                },
                                success:function(res){
                                    $("#treelist").dxTreeList("instance").refresh();
                                },
                                error:function(err){

                                }
                            })
                        }
                    });
                }
            }]
        }]
    }).dxTreeList("instance");

    {{-- Zoom Image --}}

    function zoomFoto(type, sel, img){

        var title = 'Icon';

        $('#modalView').find('.modal-title').empty().append(title);

        $('#imgView').attr('src', "{{ env('API_URL') . '/' }}" + img);

        $('#modalView').modal('show');

    }

    function sendRequest(url, method, data) {
        var d = $.Deferred();
    
        method = method || "GET";
    
        $.ajax(url, {
            method: method || "GET",
            data: data,
            cache: false,
        }).done(function(result) {
            // console.log(result.data)
            // method === "GET" ? 
            // null
            // :
            // new Noty({
            //     text: result.message,
            //     theme: 'limitless',
            //     timeout: 2500,
            //     type: 'success'
            // }).show();
            d.resolve(method === "GET" ? result.data : result);
        }).fail(function(xhr) {
            // new Noty({
            //     text: xhr.responseJSON.message,
            //     theme: 'limitless',
            //     timeout: 2500,
            //     type: 'success'
            // }).show();
            d.reject(xhr.responseJSON ? xhr.responseJSON.Message : xhr.statusText);
        });
    
        return d.promise();
    };
</script>
@endsection

