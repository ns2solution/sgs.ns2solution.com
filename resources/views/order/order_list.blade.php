@extends('layouts.app')

@section('title', '| Order List')
@section('breadcrumb', 'Dashboard  /  Order Produk  /  Order List')

@section('content')

<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
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
    table#wrap-order-items-2 tbody:first-child tr:first-child {
        display: none;
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

<button value = "1" class="btn btn-success btn-icon-text px-3 px-lg-4 float3" style="right: 40px" id="download-bulk">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>
    Export Order
</button>

<button value = "2" class="btn btn-success btn-icon-text px-3 px-lg-4 float3" style="right: 220px" id="download-bulk-principle">
    &nbsp;<i class="link-icon" data-feather="plus-square"></i>
    Export Order Principle
</button>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" @if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat') style="padding:1.35rem 1.5rem 0rem 1.5rem;" @else style="padding:1rem 1.5rem .3rem 1.5rem;" @endif>
                <div class="d-flex justify-content-between align-items-baseline">
                    <h5 class="card-title"> Order List </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group date datepicker" id="start-date-picker">
                                <input name="start_date" placeholder="Start date" type="text" class="form-control" id="start-date" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group date datepicker" id="end-date-picker">
                                <input name="end_date" placeholder="End date" type="text" class="form-control" id="end-date" autocomplete="off"><span class="input-group-addon"><i data-feather="calendar"></i></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div>
                                <select multiple id="status-order" name="status_order[]" placeholder="status">
                                    {{-- <option value="all-status" selected> ALL </option> --}}
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="row d-flex align-items-baseline">
                                <div class="col" @if(Session::get('role') != 'Super' && Session::get('role') != 'Admin Pusat') style="display:none;" @endif>
                                    <select class="form-control select" style="width: 100% !important" id="warehouse">
                                        @foreach($warehouse as $a)
                                            <option value="{{ $a->id }}"> {{ $a->code . ' - ' . $a->name . ' (' . $a->short . ')' }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <button class="btn btn-primary " type="button" id="search-order">
                                    <i class="icon-lg text-white pb-3px" data-feather="search"></i>
                                </button> --}}
                                <button class="btn" type="button" onclick="$('#dataTable').DataTable().ajax.reload(null, false);">
                                    <i class="icon-lg text-muted pb-3px" data-feather="refresh-cw"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table datatable-basic table-bordered table-striped table-hover table-responsive" id="dataTable" style="border-top:solid 1px #ddd;width:100%;">
                    <thead>
                        <tr>
                            <th>Aksi</th>
                            <th>#</th>
                            <th>ID</th>
                            <th>No. PO</th>
                            <th>No. PO Principle</th>
                            <th>Warehouse</th>
                            <th>Status</th>
                            <th>Batal Sebagian</th>
                            <th>Nama Buyer</th>
                            {{-- <th>Foto Produk</th> --}}
                            {{-- <th>Nama Produk</th> --}}
                            <th>ID Buyer</th>
                            <th>Alasan</th>
                            <th>Keterangan</th>
                            <th>Waktu Order Dikirim</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- 
<!-- <div class="row">
    <div class="col-4 mt-5">
        <div class="card">
            <div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
                <div class="d-flex justify-content-between align-items-baseline">
                    <h5 class="card-title"> TGR/20201008/194/QC/477077 </h5>
                    <h5 class="card-title" style="color:#2A8FCC;" > Pesanan Diproses </h5>
                </div>
            </div>
            <div class="card-body">
                <table style="width:100%;">
                    <tr>
                        <td style="width:140px;">
                            <img src="https://api.sgs.ns2solution.com/assets/product_image/5e22b662de4463dce7302b5d2ec193039122c27b.jpg" style="width:120px;" draggable="false">
                        </td>
                        <td valign="top">
                            <h6>Royal Canin Persian Adult 2KG</h6>
                            <p>10 Barang (2 kg)</p>
                            <h6 class="mt-2" style="color:#2A8FCC;">Rp 12.000</h6>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td style="width:140px;">
                            <img src="https://api.sgs.ns2solution.com/assets/product_image/5e22b662de4463dce7302b5d2ec193039122c27b.jpg" style="width:120px;" draggable="false">
                        </td>
                        <td valign="top">
                            <h6>Royal Canin Persian Adult 2KG</h6>
                            <p>10 Barang (2 kg)</p>
                            <h6 class="mt-2" style="color:#2A8FCC;">Rp 12.000</h6>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td valign="top"> Pembeli </td>
                        <td > : <span id="pembeli">Seno Adji</span> </td>
                    </tr>
                    <tr>
                        <td valign="top"> No. HP </td>
                        <td> : <span>082261140002</span> </td>
                    </tr>
                    <tr>
                        <td valign="top"> Alamat </td>
                        <td> : <span>Jl. Coba - coba Gang Kambing, Kecamatan Karet, Semanggi, Jakarta Barat, 103930.</span> </td>
                    </tr>
                    <tr>
                        <td valign="top"> Kurir Pengiriman </td>
                        <td> : <span>JNE Reguler</span> </td>
                    </tr>
                    <tr>
                        <td valign="top"> No. Resi </td>
                        <td> : <span>0000374821934</span> &nbsp;<button class="btn btn-sm btn-light" style="padding:.3rem .7rem .3rem;"> Salin No. </button></td>
                    </tr>
                    <tr>
                        <td valign="top"> Total Ongkos Kirim </td>
                        <td> : <b>Rp 9.000</b> </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <p>Total Harga:</p>
                <h4 style="color:#2A8FCC;" >Rp 129.000</h4>
            </div>
        </div>
    </div>
</div>  -->
--}}

<div class="modal fade" id="order-detail-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;">
            <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                <h5 class="modal-title" style="color:#fff;">View Detail Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h5 class="card-title" id="no-po"> TGR/20201008/194/QC/477077 </h5>
                            <h5 class="btn btn-sm" style="" id="status-po"> Pesanan Diproses </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <table style="width:100%;" id="wrap-order-items">
                        </table>
                        <table style="width:100%;">
                            <tr>
                                <td valign="top"> Tipe Pembayaran </td>
                                <td > : <span id="payment-type">-</span> </td>
                            </tr>
                            <tr>
                                <td valign="top"> Pembeli </td>
                                <td > : <span id="pembeli">-</span> </td>
                            </tr>
                            <tr>
                                <td valign="top"> No. HP </td>
                                <td> : <span id="no-hp">-</span> </td>
                            </tr>
                            <tr>
                                <td valign="top"> Alamat </td>
                                <td> : <span id="alamat">-</span> </td>
                            </tr>
                            <tr>
                                <td valign="top"> Kurir Pengiriman </td>
                                <td> : <span id="kurir-pengiriman">-</span> </td>
                            </tr>
                            <tr>
                                <td valign="top"> Total Berat </td>
                                <td> : <span id="total-berat">-</span> </td>
                            </tr>
                            <tr>
                                <td valign="top"> No. Resi </td>
                                <td>
                                 : <span id="no-resi">-</span>
                                 <!-- &nbsp;<button class="btn btn-sm btn-light" style="padding:.3rem .7rem .3rem;"> Salin No. </button> -->
                                 </td>
                            </tr>
                            <tr>
                                <td valign="top"> Total Ongkos Kirim </td>
                                <td> : <b id="total-ongkos-kirim" style="color:#2A8FCC">-</b> </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <p>Total (Harga + Ongkir):</p>
                        <h4 style="color:#2A8FCC;" id="total-harga"></h4>
                    </div>
                    <div class="card-footer btn" id="print-po-div">
                        <a href="" target="blank" class="btn btn-gradient btn-block text-white" id="print-po-btn">
                            Print Purchase Order
                        </a>
                        
                        <a href="" target="blank" class="btn btn-block text-white" style="background-image:linear-gradient(to right, #4caf50 0%, #009688 51%, #4caf50 100%);" id="print-invoice-btn">
                            Print Invoice
                        </a>
                    </div>

              
                </div>
            </div>
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
<form id="form-cancel-po">
    <div class="modal fade" id="modal-po" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border:none;">
                <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                    <h5 class="modal-title" style="color:#fff;">Proses Pesanan</h5>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h5 class="card-title" id="no-po-2"> TGR/20201008/194/QC/477077 </h5>
                                <h5 class="btn btn-sm" style="" id="status-po-2"> Pesanan Diproses </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="po-id" name="id">
                            <input type="hidden" id="final-total" name="final_total">
                            <input type="hidden" id="total-price" name="total_price">
                            <input type="hidden" id="total-ongkir" name="total_ongkir">
                            <table style="width:100%;" id="wrap-order-items-2">
                            </table>
                            {{-- <table style="width:100%;" id="wrap-order-items-2">
                                <tbody><tr>
                                    <td style="width:140px;">
                                        <img src="http://api-sgs.com//assets/product_image/_blank.jpg" onerror="this.onerror=null;this.src='http://api-sgs.com//assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                                    </td>
                                    <td valign="top">
                                        <h6>Produc second test</h6>
                                        <p class="d-flex align-items-center">
                                            <span style="width:155px"> Stock dikirim</span>
                                            <input ype="text" class="form-control  form-control-sm" value="23" onkeyup="changeStock(1)">
                                        </p>
                                        <h6 class="mt-2" style="color:#2A8FCC;" id="total_price">Rp 30.000</h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><hr></td>
                                </tr>
                                </tbody>
                                <tbody><tr>
                                    <td style="width:140px;">
                                        <img src="http://api-sgs.com//assets/product_image/_blank.jpg" onerror="this.onerror=null;this.src='http://api-sgs.com//assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                                    </td>
                                    <td valign="top">
                                        <h6>Tester</h6>
                                        <p class="d-flex align-items-center">
                                            <span style="width:155px"> Stock dikirim</span>
                                            <input ype="text" class="form-control form-control-sm" value="69">
                                        </p>
                                        <h6 class="mt-2" style="color:#2A8FCC;">Rp 2.500</h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><hr></td>
                                </tr>
                                </tbody></table> --}}
                            <table style="width:100%; display:none">
                                <tr>
                                    <td valign="top"> Tipe Pembayaran </td>
                                    <td > : <span id="payment-type-2">-</span> </td>
                                </tr>
                                <tr>
                                    <td valign="top"> Pembeli </td>
                                    <td > : <span id="pembeli-2">-</span> </td>
                                </tr>
                                <tr>
                                    <td valign="top"> No. HP </td>
                                    <td> : <span id="no-hp-2">-</span> </td>
                                </tr>
                                <tr>
                                    <td valign="top"> Alamat </td>
                                    <td> : <span id="alamat-2">-</span> </td>
                                </tr>
                                <tr>
                                    <td valign="top"> Kurir Pengiriman </td>
                                    <td> : <span id="kurir-pengiriman-2">-</span> </td>
                                </tr>
                                <tr>
                                    <td valign="top"> Total Berat </td>
                                    <td> : <span id="total-berat-2">-</span> </td>
                                </tr>
                                <tr>
                                    <td valign="top"> No. Resi </td>
                                    <td> : <span id="no-resi-2">-</span> &nbsp;<button class="btn btn-sm btn-light" style="padding:.3rem .7rem .3rem;"> Salin No. </button></td>
                                </tr>
                                <tr>
                                    <td valign="top"> Total Ongkos Kirim </td>
                                    <td> : <b id="total-ongkos-kirim-2">-</b> </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer" style="display: flex; justify-content: space-between;">
                            <div>
                                <p>Total Ongkir:</p>
                                <h4 style="color:#2A8FCC;" id="total-ongkos-kirim-3"></h4>
                            </div>
                            <div>
                                <p>Total Harga:</p>
                                <h4 style="color:#2A8FCC;" id="final-total-2"></h4>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="form-group mb-0">
                                <label class="col-form-label">Alasan</label>
                                <!-- <textarea class="form-control" name="cancel_msg" placeholder=""></textarea> -->
                                <select class="form-control cancel_msg" name="cancel_msg" id="cancel_msg">

                                </select>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close-cancel-po"> Batal </button>
                    <button type="submit" class="btn btn-primary btn-custom" id="save-edit-po"> Simpan </button>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="form-input-resi">
    <div class="modal fade" id="modal-input-resi" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border:none;">
                <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                    <h5 class="modal-title" style="color:#fff;">Input Resi</h5>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body pt-0">
                            <div class="form-group mb-0">
                                <label class="col-form-label">No.Resi</label>
                                <input type="text" class="form-control" name="number_resi" placeholder=""/>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close-input-resi"> Batal </button>
                    <button type="submit" class="btn btn-primary btn-custom" id="save-input-resi"> Simpan </button>
                </div>
            </div>
        </div>
    </div>
</form>


<form id="form-input-shipped-date">
    <div class="modal fade" id="modal-input-shipped-date" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border:none;">
                <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                    <h5 class="modal-title" style="color:#fff;">Tanggal order dikirim</h5>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body pt-0">
                            <div class="form-group mb-0 row col-12">
                                <label class="col-form-label">Tanggal Kirim</label>
                                <input type="date" class="form-control" name="shipped_date" placeholder=""/>
                            </div>
                            <div class="row">
                                <div class="form-group mb-0 col-6">
                                    <label class="col-form-label">Dari</label>
                                    <input type="hidden" id="start-time-sd-tmp">
                                    <input class="form-control without_ampm" type="text" class="form-control" name="start_time" id="start-time-sd"  placeholder=""/>
                                </div>
                                <div class="form-group mb-0 col-6">
                                    <label class="col-form-label">Sampai</label>
                                    <input type="hidden" id="end-time-sd-tmp">
                                    <input class="form-control without_ampm" type="text" class="form-control" name="end_time" id="end-time-sd"  placeholder=""/>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close-input-shipped-date"> Batal </button>
                    <button type="submit" class="btn btn-primary btn-custom" id="save-input-shipped-date"> Simpan </button>
                </div>
            </div>
        </div>
    </div>
</form>


<form id="form-cancel-order">
    <div class="modal fade" id="modal-cancel-order" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border:none;">
                <div class="modal-header" id="modal-header" style="background:rgb(42,143,204);background:linear-gradient(41deg, rgba(42,143,204,1) 0%, rgba(142,205,243,1) 50%);">
                    <h5 class="modal-title" style="color:#fff;">Pembatalan Pesanan</h5>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <h5 class="card-title" id="no-po-3"> TGR/20201008/194/QC/477077 </h5>
                                <h5 class="btn btn-sm" style="" id="status-po-3"> Pembatalan Pesanan </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="po-id-3" name="id">
                            <div class="form-group mb-0">
                                <label class="col-form-label">Alasan</label>
                                <!-- <textarea class="form-control" name="cancel_msg" placeholder=""></textarea> -->
                                <select class="form-control cancel_msg" name="cancel_msg" id="cancel_msg">

                                </select>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="close-cancel-order-btn"> Batal </button>
                    <button type="submit" class="btn btn-primary btn-custom" id="save-cancel-order-btn"> Simpan </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('js')

<script>

    function customPost(data){
        return Object.assign({}, porpertyPOST(), { body: JSON.stringify(Object.assign(getCurrentToken(), data))})
    }

    function __formatValueStep(payment_type, total, is_active = null, calc_from_backend = null) {
        if(obj_payment_type.TF == payment_type) {
            return total;
        } else if(obj_payment_type.POINT == payment_type) {
            return total;            
        } else if(obj_payment_type.WP == payment_type) {
            // log(calc_from_backend, 'calc_from_backend')
            if(is_active) {
                // log(calc_from_backend, 'calc_from_backend')
                return `<img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp; ${ calc_from_backend }`                
            } else {
                return (Math.floor(total / localStorage.getItem('CONV_WP')))

            }
        } else {
            return `-`;
        }
    
    }

    function __formatValue(payment_type, total, is_active = null, calc_from_backend = null) {
        
        // log(total)
        // return obj_payment_type.TF == payment_type ? `Rp ${__toRp(total)}` : 
        // obj_payment_type.POINT == payment_type ? `<img draggable="false" src="{{ asset('assets/main/img/icon_point.png') }}" style="width:16px;height:16px;">&nbsp; ${__toRp(total)}` : 
        // obj_payment_type.WP == payment_type ? `<img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp; ${ (Math.floor(total / localStorage.getItem('CONV_WP'))) }` : '-' ;
        
        if(obj_payment_type.TF == payment_type) {
            return `Rp ${__toRp(total)}`
        } else if(obj_payment_type.POINT == payment_type) {
            return `<img draggable="false" src="{{ asset('assets/main/img/icon_point.png') }}" style="width:16px;height:16px;">&nbsp; ${__toRp(total)}`            
        } else if(obj_payment_type.WP == payment_type) {
            log(calc_from_backend, 'calc_from_backend')
            if(is_active) {
                log(calc_from_backend, 'calc_from_backend')
                return `<img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp; ${ calc_from_backend }`                
            } else {
                return `<img draggable="false" src="{{ asset('assets/main/img/icon_warpay.png') }}" style="width:16px;height:16px;">&nbsp; ${ (Math.floor(total / localStorage.getItem('CONV_WP'))) }`
            }
        } else {
            return `-`;
        }
    
    }


    var obj_status_order = {
        pd: 'pesanan-dibatalkan',
        proses: 'pesanan-diproses',
        mk : 'pesanan-menunggu-konfirmasi',
    }

    var obj_payment_type = {
        POINT: 'point',
        TF: 'transfer',
        WP: 'warpay'
    }

    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
            by : "{{ Session::get('user')->id }}"
        }
    }
 
    let elm_save_cancel_po = __getId('save-cancel-po')
    let elm_close_cancel_po = __getId('close-cancel-po')
    let elm_form_cancel_po = __getId('form-cancel-po')
    let elm_form_cancel_order = __getId('form-cancel-order')
    let elm_form_input_resi = __getId('form-input-resi')
    let elm_form_input_shipped_date = __getId('form-input-shipped-date')
    let elm_close_input_shipped_date = __getId('close-input-shipped-date')
    let elm_save_input_shipped_date = __getId('save-input-shipped-date')
    let elm_save_edit_po = __getId('save-edit-po')
    let elm_close_input_resi = __getId('close-input-resi')
    let elm_save_input_resi = __getId('save-input-resi')
    let elm_status_order = __getId('status-order')

    let po_id = __getId('po-id')
    let order_final_total = __getId('final-total') // total price + ongkir
    let order_total_price = __getId('total-price') // total price
    let order_total_ongkir = __getId('total-ongkir')

    // for order detail
    let elm_payment_type = __getId('payment-type')
    let elm_no_po = __getId('no-po')
    let elm_status_po = __getId('status-po')
    let elm_pembeli = __getId('pembeli')
    let elm_no_hp = __getId('no-hp')
    let elm_alamat = __getId('alamat')
    let elm_kurir_pengiriman = __getId('kurir-pengiriman')
    let elm_total_berat = __getId('total-berat')
    let elm_no_resi = __getId('no-resi')
    let elm_ttl_ongkir = __getId('total-ongkos-kirim')
    let elm_ttl_harga = __getId('total-harga')
    let elm_wrap_order_items = __getId('wrap-order-items')


    // for order confirmation  
    let elm_payment_type_2 = __getId('payment-type-2')
    let elm_no_po_2 = __getId('no-po-2')
    let elm_status_po_2 = __getId('status-po-2')
    let elm_pembeli_2 = __getId('pembeli-2')
    let elm_no_hp_2 = __getId('no-hp-2')
    let elm_alamat_2 = __getId('alamat-2')
    let elm_kurir_pengiriman_2 = __getId('kurir-pengiriman-2')
    let elm_total_berat_2 = __getId('total-berat-2')
    let elm_no_resi_2 = __getId('no-resi-2')
    let elm_ttl_ongkir_2 = __getId('total-ongkos-kirim-2')
    let elm_final_total_2 = __getId('final-total-2')
    let elm_wrap_order_items_2 = __getId('wrap-order-items-2')

    let elm_no_po_3 = __getId('no-po-3')
    let po_id_3 = __getId('po-id-3')
    let elm_cancel_order_btn = __getId('save-cancel-order-btn')
    let elm_close_cancel_order_btn = __getId('close-cancel-order-btn')


    let elm_ttl_ongkir_3 = __getId('total-ongkos-kirim-3')


    let elm_waiting_for_fetch_data = __getId('waiting-for-fetch-data')

    let id = 0;

    let elm_download_bulk = __getId('download-bulk')
    let elm_download_bulk_principle = __getId('download-bulk-principle')

    function fDataTable(){
        //set warehouse by user role
        let user_role = `{{ Session::get('role') }}`;
        let user_wh = `{{ Session::get('warehouse_id') }}`;
        let table_wh = ``;
        switch(user_role){
            case  'Admin Gudang' : table_wh = user_wh;
            break;
            default : table_wh = $('#warehouse').val();
        }
        $.fn.dataTable.ext.errMode = 'none';
        table = $('#dataTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [[2, 'DESC']],
            ajax:{
                url: "{{ env('API_URL') . '/order/data-table' }}",
                dataType: 'JSON',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    token : "{{ Session::get('token') }}",
                    email : "{{ Session::get('email') }}",
                    warehouse_id   : table_wh,
                    start_date: $('#start-date').val(),
                    end_date: $('#end-date').val(),
                    status_order: $('#status-order').val(),
                }
            },
            columns: [
                {
                    sClass: 'text-center',
                    orderable: false,
                    render: function(_, __, data){
                        let status_po = data.status_po;
                        let is_accept_refund = data.is_accept_refund;
                        // log(status_po)
                        // log(data)
                        let status = status_po ? status_po.split(' ').join('-').toLocaleLowerCase() : '';
                        // log(is_accept_refund, status);
                        
                        let btn_proses_po = `
                            &nbsp;
                            <button class="btn p-0" type="button" id="process-po" data-toggle="tooltip" data-placement="bottom" title="Proses Order">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#4CAF54" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </button>
                            `;

                        let btn_edit_po = `
                            &nbsp;
                            <button class="btn p-0" type="button" id="process-with-edit-stock-po" data-toggle="tooltip" data-placement="bottom" title="Proses Order disertai edit stok">
                                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </button>
                            &nbsp;`;

                        let btn_cancel_po = `
                            <button class="btn p-0" type="button" id="cancel-po" data-toggle="tooltip" data-placement="bottom" title="Batal Order">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            </button>
                            `;

                        let btn_input_resi = `
                            &nbsp;
                            <button class="btn p-0" type="button" id="input-resi" data-toggle="tooltip" data-placement="bottom" title="Input Resi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6610f2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                            </button>
                            `;

                        let btn_pesanan_siap_diambil = `
                            &nbsp;
                            <button class="btn p-0" type="button" id="order-siap-diambil" data-toggle="tooltip" data-placement="bottom" title="Proses order siap diambil">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff5722" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-gift"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                            </button>
                            `;

                        let btn_menuggu_konfirmasi = ( status == obj_status_order.mk && is_accept_refund == 1)  ? `${btn_proses_po} ${btn_edit_po} ${btn_cancel_po}` : ( status == obj_status_order.mk && is_accept_refund != 1)  ? `${btn_proses_po} ${btn_cancel_po}` : ''
                        let btn_pesanan_diproses = ( status == obj_status_order.proses && (data.is_pick == 0 || data.is_pick == null)) ? `${btn_input_resi}` : ``
                        let btn_pesanan_diproses_2 = ( status == obj_status_order.proses  && data.is_pick ) ? `${btn_pesanan_siap_diambil}` : ``;

                        return `
                            <button class="btn p-0" type="button" id='view-order-detail' data-toggle="tooltip" data-placement="bottom" title="Lihat detail order">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#2A8FCC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-justify"><line x1="21" y1="10" x2="3" y2="10"></line><line x1="21" y1="6" x2="3" y2="6"></line><line x1="21" y1="14" x2="3" y2="14"></line><line x1="21" y1="18" x2="3" y2="18"></line></svg>
                            </button>
                            ${btn_menuggu_konfirmasi}
                            ${btn_pesanan_diproses}
                            ${btn_pesanan_diproses_2}
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
                    data: 'no_po',
                    sClass: 'text-center',
                    orderable: false,
                    render: function(data){
                        return `${data}`;
                    }
                },
                {
                    data: 'no_po_principle',
                    sClass: 'text-center',
                    orderable: false,
                    render: function(data){
                        if(data.length > 0) {
                            const po_principle = []
                            for(const i of data) {
                                po_principle.push(i.no_po_principle);
                            }
                            return po_principle.join('<hr style="margin:10px 0 !important">');
                        } else {
                            return `-`;
                        }
                    }
                },
                {
                    data: 'warehouse_name',
                    sClass: 'text-center',
                    orderable: false,
                },
                {
                    data: 'status_po',
                    sClass: 'text-center',
                    render: function(data){
                        let status = data ? data.split(' ').join('-').toLocaleLowerCase() : '';
                        return `<span class="btn btn-xs ${status}" style="pointer-events:none;">${data}</span>`;
                    }
                },
                {
                    data: 'is_accept_refund',
                    sClass: 'text-center',
                    render: function(data){
                        let is_accept_refund = data == 1 ? 'Setuju' : 'Tidak Setuju';
                        if(data == 1) {
                            return `<span>${is_accept_refund}</span>`;
                        } else {
                            return `<span>${is_accept_refund}</span>`
                        }
                    }

                },
                {
                    data: 'buyer_name',
                    sClass: 'text-left',
                    width:'40%',
                },
/*                {
                    data: 'prod_image',
                    width:'20%',
                    sClass: 'text-center',
                    orderable: false,
                    render: function(data){
                        return `<img src="{{ env('API_URL') . '/' }}${data}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" id="zoom-foto" style="height:70px;width:auto;border-radius:5%;cursor:pointer !important" draggable="false" onerror="this.remove();">`;
                    }
                },
                {
                    data: 'prod_name',
                    width:'auto',
                    sClass: 'text-left',
                    orderable: false
                },
*/
                {
                    data: 'buyer_id',
                    sClass: 'text-center'
                },
                {
                    data: 'cancel_msg',
                    sClass: 'text-center'
                },
                {
                    data: 'is_pick',
                    sClass: 'text-center',
                    render: function(data){
                        let is_pick = data == 1 ? 'Self Pick Up' : '-';
                        if(data == 1) {
                            return `<span class="badge badge-primary">${is_pick}</b>`;
                        } else {
                            return `<span>${is_pick}</span>`
                        }
                    }

                },
                {
                    data: 'shipped_date',
                    sClass: 'text-center'
                },
                {
                    data: 'created_at',
                    sClass: 'text-center'
                },
                {
                    data: 'updated_at',
                    sClass: 'text-center'
                },
            ]
        });
    }


    $('#warehouse').on('change', function(){
        $('#warehouse_id').val(this.value);
        if(this.value != ''){
            $('#dataTable').DataTable().destroy();
            fDataTable();
        }
    });

    window.onload = async () => {


        // init select2
        $('select').select2();

        @if(Session::get('user')->wh_id)
            $('#warehouse').val("{{ Session::get('user')->wh_id }}").change();
            $('#warehouse_id').val("{{ Session::get('user')->wh_id }}");
        @else

            // func append html
            insertAdjHTML(`warehouse`, `afterbegin`, `<option value='all-warehouse'> ALL </option>`);

            $('#warehouse').val('all-warehouse').change();
            $('#warehouse_id').val('all-warehouse');
        @endif




        // get master status order

        const elm_status_order_ = __getId('status-order');
        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

        let res = await fetch(`{{ env('API_URL') . '/status-order' }}`, __propsPOST)
        let result = await res.json();

        const {status, data, message} = result;

        if(status) {

            elm_status_order_.innerHTML = '';
            // elm_status_order_.appendChild(___createOpt('all-status', 'ALL'));

            for (const so of data) {
                let newOption = ___createOpt(so.id, so.status_name);
                elm_status_order_.appendChild(newOption);
                elm_status_order_.dispatchEvent(new Event("change", {bubbles: true,}));
            }

        } else {
            console.error('Cek Master Status Order !')
        }


    // ----------- init ---------------------

    const rulesForm = {
        rules: {
            cancel_msg : 'required',
        },
        messages: {
            cancel_msg: {
                required: "Tidak boleh kosong",
            },
        },
        ...rulesValidateGlobal,
        submitHandler:(form, e) => {
            e.preventDefault();

                const id =  po_id.value ? po_id.value : null;

                saveEditPO(e, id)

            return false;
        }
    }

    $('#form-cancel-po').submit((e) => {
        e.preventDefault();
    }).validate(rulesForm);


    $('#form-input-resi').submit((e) => {
        e.preventDefault();
    }).validate(
        Object.assign({},rulesForm, {
            rules: {
                number_resi : 'required',
            },
            messages: {
                number_resi: {
                    required: "Tidak boleh kosong",
                },
            },
            submitHandler:(form, e) => {
                e.preventDefault();

                    const id =  po_id.value ? po_id.value : null;

                    saveResi(e, id)

                return false;
            }
        })
    );


    $('#form-input-shipped-date').submit((e) => {
        e.preventDefault();
    }).validate(
        Object.assign({},rulesForm, {
            rules: {
                shipped_date : 'required',
                start_time: 'required',
                end_time: 'required'
            },
            messages: {
                shipped_date: {
                    required: "Tidak boleh kosong",
                },
                start_time: {
                    required: "Tidak boleh kosong",
                },
                end_time: {
                    required: "Tidak boleh kosong",
                },
            },
            submitHandler:(form, e) => {
                e.preventDefault();

                    const id =  po_id.value ? po_id.value : null;

                    saveShippedDate(e, id)

                return false;
            }
        })
    );

    $('#form-cancel-order').submit((e) => {
        e.preventDefault();
    }).validate(
        Object.assign({},rulesForm, {
            rules: {
                number_resi : 'required',
            },
            messages: {
                number_resi: {
                    required: "Tidak boleh kosong",
                },
            },
            submitHandler:(form, e) => {
                e.preventDefault();

                    const id =  po_id_3.value ? po_id_3.value : null;

                    saveCancelOrder(e, id)

                return false;
            }
        })
    );

    // save nomor resi, sekalian update status po jadi `pesanan siap diambil`
    async function saveResi(e, id) {
        if(e) {
            e.preventDefault();
        }

        elm_save_input_resi.innerHTML = 'Menyimpan ' + ___iconLoading();
        elm_save_input_resi.disabled = true;

        let formData = new FormData(elm_form_input_resi);
        formData.append('token', '{{ Session::get("token")}}')
        formData.append('email', '{{ Session::get("email")}}')
        formData.append('by', '{{ Session::get("user")->id }}')
        formData.append('status', 4) // pesanan dikirim

        $.ajax({
            url:`{{ env('API_URL') . '/order/reverse-status/update/${id}/send-with-add-number-resi' }}`,
            method:"POST",
            data: formData,
            dataType:'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success:function(result){
                const {status, message, data} = result;


                if(status) {

                    refreshOrderDT();

                    toastr.success(message, { fadeAway: 10000 });
                    elm_save_input_resi.innerHTML = 'Simpan';
                    elm_save_input_resi.disabled = false;

                    $('#modal-input-resi').modal('hide')
                } else {
                    elm_save_input_resi.disabled = false;
                    elm_save_input_resi.innerHTML = 'Simpan';

                    console.error(message);

                    $('#modal-input-resi').modal('hide')
                }
            },
            error: function(err) {
                log(err);
                const msg = err.responseJSON.message;

                toastr.error(msg,  { fadeAway: 10000 });

                elm_save_input_resi.innerHTML = 'Simpan';

            }
        })


    }

    async function saveShippedDate(e, id) {
        if(e) {
            e.preventDefault();
        }

        elm_save_input_shipped_date.innerHTML = 'Menyimpan ' + ___iconLoading();
        elm_save_input_shipped_date.disabled = true;
        try {

            let formData = __serializeForm(elm_form_input_shipped_date);

        let res = await fetch(`{{ env('API_URL') . '/order/reverse-status/update/${id}/pesanan-siap-diambil' }}`, Object.assign({}, __propsPOST, {
                body: JSON.stringify(Object.assign({}, JSON.parse(formData), {
                ...getCurrentToken(),
                status: 9 // pesanan siap diambil pembeli
                }))
            }))

            let result = await res.json();

            const {status, message} = result;

            if(status) {
                refreshOrderDT();
                toastr.success(message, { fadeAway: 10000 });
                elm_save_input_shipped_date.disabled = false;
                elm_save_input_shipped_date.innerHTML = 'Save';

                $('#modal-input-shipped-date').modal('hide')
            } else {
                toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
                console.error(message)
                elm_save_input_shipped_date.disabled = false;
                elm_save_input_shipped_date.innerHTML = 'Save';

                $('#modal-input-shipped-date').modal('hide')
            }
        } catch (error) {
            console.error(error);
        }


    }






    // ------------------------ event listen ----------------------
    elm_download_bulk.addEventListener('click', exportData);
    elm_download_bulk_principle.addEventListener('click', exportData);


    elm_close_cancel_po.addEventListener('click', closeModalCancelPO);
    elm_close_cancel_order_btn.addEventListener('click', closeModalCancelOrder);
    elm_close_input_resi.addEventListener('click', closeModalInputResi);
    elm_close_input_shipped_date.addEventListener('click', closeModalShippedDate);

    elm_status_order.addEventListener('change', evStatusOrder);
    $('#status-order').on('select2:select', evStatusOrder);
    $('#status-order').on('select2:clear', evStatusOrder);
    $('#status-order').on('select2:unselect', evStatusOrder);

    $('#dataTable tbody').on('click', '#zoom-foto', function () {
        const data = table.row( $(this).parents('tr') ).data();
        let img = data.prod_image ? data.prod_image : 'assets/product_image/_blank.jpg';

        __querySelector('#img-view').setAttribute('src', "{{ env('API_URL') . '/' }}" + img)

        $('#modal-view').modal('show');
    });



    $('#dataTable tbody').on('click', '#process-po', function () {
        // const data = table.row( $(this).parents('tr') ).data();

        const idx = table.row( $(this).parents('tr') )[0][0];
        const data = table.rows().data();

        const id_po = data[idx].id;
        const no_po = data[idx].no_po;

        __swalConfirmationApproved('Apakah anda yakin ?', `Konfirmasi PO ${no_po} ?`, id_po)

    });


    $('#dataTable tbody').on('click', '#order-siap-diambil', function () {
        //const data = table.row( $(this).parents('tr') ).data();

        const idx = table.row( $(this).parents('tr') )[0][0];
        const data = table.rows().data();

        const id_po = data[idx].id;
        po_id.value = id_po;

        // manage error
        $('#form-input-shipped-date input.has-error').removeClass('has-error');
        $('#form-input-shipped-date textarea.has-error').removeClass('has-error');
        $('#form-input-shipped-date select.has-error').removeClass('has-error');
        $('#form-input-shipped-date .help-inline.text-danger').remove()

        $('#modal-input-shipped-date').modal('show');

        elm_form_input_shipped_date.reset();

    });


    
    $('#dataTable tbody').on('click', '#process-with-edit-stock-po', async function () {
        // const data = table.row( $(this).parents('tr') ).data();
        //const id_po = data.id

        const idx                   = table.row( $(this).parents('tr') )[0][0];
        const data                  = table.rows().data();

        const id_po                 = data[idx].id;
        const wh_id                 = data[idx].warehouse_id;

        // RESET ONGKIR :)
        // let res = await fetch(`{{ env('API_URL') . '/rajaongkir/get-order/FROM-DATABASE' }}`, Object.assign({}, __propsPOST, {
        //                                                 body: JSON.stringify(Object.assign({}, getCurrentToken(), {
        //                                                     warehouse_id: wh_id,
        //                                                     order_id: id_po,
        //                                                 }))
        //                                             }))

        po_id.value                 = id_po;
        

        const courier_service       = data[idx].courier_service      ? data[idx].courier_service      : '';
        const courier_name          = data[idx].courier_name         ? data[idx].courier_name         : '';
        const number_resi           = data[idx].number_resi          ? data[idx].number_resi          : '';

        const address               = data[idx].address              ? data[idx].address              : '';
        const user_profile_phone    = data[idx].user_profile_phone   ? data[idx].user_profile_phone   : '';
        const user_full_name        = data[idx].user_full_name       ? data[idx].user_full_name       : '';
        const payment_type          = String(data[idx].payment_type).toUpperCase();

        elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

        try{

            let res = await fetch(`{{ env('API_URL') . '/order-item/by-order-id/${id_po}' }}`, Object.assign({}, __propsPOST, {
                method: 'POST'
            }))
            
            let result = await res.json();

            const {status, data, message} = result;
            const {order, order_items, products} = data;
            
            if(status) {

                let payment_type                = order.payment_type; 
                elm_payment_type_2.innerHTML = `<span style="text-transform:uppercase;font-weight:bold;">${payment_type}</span>`;
    
                elm_no_po_2.innerHTML               = order.no_po;

                elm_kurir_pengiriman_2.innerHTML    = `${courier_name} - <span class="badge-sm badge-primary">${courier_service}</span>`;
                elm_no_resi_2.innerHTML             = number_resi;
                elm_status_po_2.innerHTML           = order.status_po;
                elm_status_po_2.className           = '';
                elm_status_po_2.classList.add(order.status_po ? order.status_po.split(' ').join('-').toLocaleLowerCase() : '', 'btn', 'btn-sm')

                elm_final_total_2.innerHTML         = __formatValue(payment_type, order.total_price, true, order.PRODUCT_WARPAY);

                elm_alamat_2.innerHTML              = address;
                elm_no_hp_2.innerHTML               = user_profile_phone;
                elm_pembeli_2.innerHTML             = user_full_name;

                elm_ttl_ongkir_2.innerHTML          = __formatValue(payment_type, order.total_ongkir);

                order_total_ongkir.value            = order.total_ongkir;
                order_total_price.value             = order.total_price;
                order_final_total.value             = order.final_total;

                elm_ttl_ongkir_3.innerHTML          = __formatValue(payment_type, order.total_ongkir);

                elm_wrap_order_items_2.innerHTML    = '';

                for(const order_item of order_items) {
                    if(order_item.total_item !== 0) {
                        
                        const ttl_length = order_items.filter((data,idx,arr) => data.total_item !== 0).length;

                        elm_wrap_order_items_2.insertAdjacentHTML('afterbegin', __elmOrderItemWithUpdateStock(order_item, order_items, ttl_length, id_po, wh_id, order, payment_type,));
                    }
                }

                // calc value with stock exist
                let total_weight = 0;
                for(const order_item of order_items) {

                    total_weight += parseInt(order_item.prod_gram * order_item.total_item);

                    if(order_item.total_item !== 0) {
                        __querySelector(`input[name='total_price_order_item[${order_item.id}]']`).value = __querySelector(`input[name='total_stock_order_item[${order_item.id}]']`).value * order_item.price
                    }
                }

                elm_total_berat_2.innerHTML = `${(total_weight / 1000)} kg`;

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

                $('.cancel_msg').empty()

                //add by dd
                $.ajax({
                    type:'get',
                    url:"{{ env('API_URL') . '/alasan' }}",
                    success:function(res){
                        console.log(res)
                        if (res.data) {
                            let html = '';
                            $.each(res.data, function(key, data){
                                html += `<option value='${data.alasan}'>${data.alasan}</option>`
                            })
                            $('.cancel_msg').append(html)
                        }
                    },
                    error:function(err){
                        console.log(err)
                    }
                })


                // manage error
                $('#form-cancel-po input.has-error').removeClass('has-error');
                $('#form-cancel-po textarea.has-error').removeClass('has-error');
                $('#form-cancel-po select.has-error').removeClass('has-error');
                $('#form-cancel-po .help-inline.text-danger').remove()


                // cancel sebagian order item
                function cancelManagement() {

                    if(__querySelectorAll('.cancel-order-item') && __querySelectorAll('.cancel-order-item').length > 0) {

                        for(const elm of __querySelectorAll('.cancel-order-item')) {
                            elm.addEventListener('click', e => {

                                let id_order_item = elm.value;
                                
                                swal({
                                    title: 'Apakah anda yakin ?',
                                    text: 'Apakah anda yakin ingin membatalkannya ?',
                                    icon: "warning",
                                    buttons: true,
                                    dangerMode: true,
                                })
                                .then(async (willUpdate) => {
                                    if (willUpdate) {

                                        try {
                                            
                                            const load                       = '<div style="width:100%;display:flex; justify-content:center;">'+___iconLoading('black')+'</div>';
                                            
                                            elm_wrap_order_items_2.innerHTML = load;
                                            elm_ttl_ongkir_3.innerHTML       = load;
                                            elm_final_total_2.innerHTML      = load;

                                            let res = await fetch(`{{ env('API_URL') . '/order/update-qty-order-item/${id_order_item}' }}`, Object.assign({}, __propsPOST, {
                                                body: JSON.stringify(getCurrentToken())
                                            }))

                                            let result = await res.json();

                                            const {status} = result;

                                            if(status) {

                                                let res = await fetch(`{{ env('API_URL') . '/order-item/by-order-id/${id_po}' }}`, Object.assign({}, __propsPOST, {
                                                    method: 'POST'
                                                }))

                                                let result = await res.json();

                                                const {status, data} = result;
                                                const {order, order_items, products} = data;

                                                if(status) {

                                                    elm_wrap_order_items_2.innerHTML = '';

                                                    for(const order_item of order_items) {
                                                        if(order_item.total_item !== 0) {

                                                            const ttl_length = order_items.filter((data,idx,arr) => data.total_item !== 0).length;

                                                            elm_wrap_order_items_2.insertAdjacentHTML('afterbegin', __elmOrderItemWithUpdateStock(order_item, order_items, ttl_length, id_po, wh_id, order, payment_type));
                                                        }
                                                    }

                                                    let total_weight = 0;
                                                    for(const order_item of order_items) {
                                                        total_weight += parseInt(order_item.prod_gram * order_item.total_item);
                                                        if(order_item.total_item !== 0) {
                                                            __querySelector(`input[name='total_price_order_item[${order_item.id}]']`).value = __querySelector(`input[name='total_stock_order_item[${order_item.id}]']`).value * order_item.price
                                                        }
                                                    }

                                                    elm_total_berat.innerHTML = `${(total_weight / 1000)} kg`;

                                                    let res = await fetch(`{{ env('API_URL') . '/rajaongkir/get-order/FROM-DATABASE' }}`, Object.assign({}, __propsPOST, {
                                                        body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                                                            warehouse_id: wh_id,
                                                            order_id: id_po,
                                                        }))
                                                    }))

                                                    let result_ongkir = await res.json();

                                                    if(result_ongkir.status) {
                                                        order_total_ongkir.value = result_ongkir?.data?.order?.total_ongkir;
                                                        elm_ttl_ongkir_3.innerHTML  = __formatValue(payment_type, result_ongkir?.data?.order?.total_ongkir);
                                                        elm_final_total_2.innerHTML = __formatValue(payment_type, result_ongkir?.data?.order?.total_price, true, result_ongkir?.data?.order?.PRODUCT_WARPAY);
                                                    } else {
                                                        elm_ttl_ongkir_3.innerHTML  = __formatValue(payment_type, order.total_ongkir);
                                                        elm_final_total_2.innerHTML = __formatValue(payment_type, order.total_price, true, order.PRODUCT_WARPAY);
                                                    }


                                                    //callbackSelfFunction for handle error click not running
                                                    cancelManagement();

                                                    refreshOrderDT();
                                                    toastr.success(message, { fadeAway: 10000 });
                                                    
                                                }

                                            } else {
                                                toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
                                                console.error(message)
                                            }
                                        } catch (error) {
                                            console.error(error);
                                        }
                                    }
                                })

                            });
                        }
                    }

                }

                cancelManagement();

                $('#modal-po').modal('show');



            } else {

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');
                console.error(message)
            }

        } catch(e) {
            log(e)
        }


    });


    $('#dataTable tbody').on('click', '#cancel-po', async function () {
        //const data = table.row( $(this).parents('tr') ).data();

        const idx = table.row( $(this).parents('tr') )[0][0];
        const data = table.rows().data();

        const id_po = data[idx].id;

        po_id_3.value = id_po;

        elm_cancel_order_btn.disabled = false;

        elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

        try{

            let res = await fetch(`{{ env('API_URL') . '/order-item/by-order-id/${id_po}' }}`, Object.assign({}, __propsPOST, {
                method: 'POST'
            }))

            let result = await res.json();

            const {status, data, message} = result;
            const {order, order_items, products} = data;

            if(status) {

                elm_no_po_3.innerHTML = `${order.no_po}`;

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

                //add by dd
                $('.cancel_msg').empty()
                $.ajax({
                    type:'get',
                    url:"{{ env('API_URL') . '/alasan' }}",
                    success:function(res){
                        console.log(res)
                        if (res.data) {
                            let html = '';
                            $.each(res.data, function(key, data){
                                html += `<option value='${data.alasan}' >${data.alasan}</option>`
                            })
                            $('.cancel_msg').append(html)
                        }
                    },
                    error:function(err){
                        console.log(err)
                    }
                })


                // manage error
                $('#form-cancel-po input.has-error').removeClass('has-error');
                $('#form-cancel-po textarea.has-error').removeClass('has-error');
                $('#form-cancel-po select.has-error').removeClass('has-error');
                $('#form-cancel-po .help-inline.text-danger').remove()

                $('#modal-cancel-order').modal('show');


            } else {

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');
                console.error(message)
            }

        } catch(e) {
            log(e)
        }
    });

    $('#dataTable tbody').on('click', '#input-resi', function () {
        //const data = table.row( $(this).parents('tr') ).data();

        const idx = table.row( $(this).parents('tr') )[0][0];
        const data = table.rows().data();

        const id_po = data[idx].id;
        po_id.value = id_po;

        // manage error
        $('#form-input-resi input.has-error').removeClass('has-error');
        $('#form-input-resi textarea.has-error').removeClass('has-error');
        $('#form-input-resi select.has-error').removeClass('has-error');
        $('#form-input-resi .help-inline.text-danger').remove()

        $('#modal-input-resi').modal('show');

        elm_form_input_resi.reset();

    });


    $('#dataTable tbody').on('click', '#input-shipped-date', function () {
        const idx = table.row( $(this).parents('tr') )[0][0];
        const data = table.rows().data();

        const id_po = data[idx].id;
        po_id.value = id_po;

        // manage error
        $('#form-input-shipped-date input.has-error').removeClass('has-error');
        $('#form-input-shipped-date textarea.has-error').removeClass('has-error');
        $('#form-input-shipped-date select.has-error').removeClass('has-error');
        $('#form-input-shipped-date .help-inline.text-danger').remove()

        $('#modal-input-shipped-date').modal('show');

        elm_form_input_shipped_date.reset();

    });


    function evStatusOrder(e) {
        const id= e.params ? e.params.data.id : e.target.value;
        $('#dataTable').DataTable().destroy();
        fDataTable();
    }


    async function closeModalCancelPO(e) {
        if(e) {
            e.preventDefault();
            $('#modal-po').modal('hide')
        }
    }

    async function closeModalCancelOrder(e) {
        if(e) {
            e.preventDefault();
            $('#modal-cancel-order').modal('hide')
        }
    }


    async function closeModalInputResi(e) {
        if(e) {
            e.preventDefault();
            $('#modal-input-resi').modal('hide')
        }
    }

    async function closeModalShippedDate(e) {
        if(e) {
            e.preventDefault();
            $('#modal-input-shipped-date').modal('hide')
        }
    }

    async function saveCancelPO(e, id) {
        if(e) {
            e.preventDefault();
        }

        elm_save_edit_po.innerHTML = 'Menyimpan ' + ___iconLoading();
        elm_save_edit_po.disabled = true;

        let formData = __serializeForm(elm_form_cancel_po);
        formData = Object.assign({}, JSON.parse(formData), {
            ...getCurrentToken(),
            status: 3 // pesanan diproses
        })

        try {

            let res = await fetch(`{{ env('API_URL') . '/order/reverse-status/update/${id}' }}`, porpertyPOST(formData))

            let result = await res.json();

            const {status, message, data} = result;

            if(status) {

                refreshOrderDT();

                toastr.success(message, { fadeAway: 10000 });
                elm_save_edit_po.innerHTML = 'Simpan';
                elm_save_edit_po.disabled = false;

                $('#modal-po').modal('hide')
            } else {
                elm_save_edit_po.disabled = false;
                elm_save_edit_po.innerHTML = 'Simpan';

                console.error(message);

                $('#modal-po').modal('hide')
            }

        } catch (error) {
            console.error(error);
            toastr.error(error,  { fadeAway: 10000 });
            elm_save_edit_po.innerHTML = 'Simpan';
        }
    }

    async function saveEditPO(e, id) {
        if(e) {
            e.preventDefault();
        }

        elm_save_edit_po.innerHTML = 'Menyimpan ' + ___iconLoading();
        elm_save_edit_po.disabled = true;

        let formData = new FormData(elm_form_cancel_po);
        formData.append('token', '{{ Session::get("token")}}')
        formData.append('email', '{{ Session::get("email")}}')
        formData.append('by', '{{ Session::get("user")->id }}')
        formData.append('status', 3)

        $.ajax({
            url:`{{ env('API_URL') . '/order/reverse-status/update/${id}/process-with-change-stock' }}`,
            method:"POST",
            data: formData,
            dataType:'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success:function(result){
                const {status, message, data} = result;


                if(status) {

                    refreshOrderDT();

                    toastr.success(message, { fadeAway: 10000 });
                    elm_save_edit_po.innerHTML = 'Simpan';
                    elm_save_edit_po.disabled = false;

                    $('#modal-po').modal('hide')
                } else {
                    elm_save_edit_po.disabled = false;
                    elm_save_edit_po.innerHTML = 'Simpan';

                    console.error(message);

                    $('#modal-po').modal('hide')
                }
            },
            error: function(err) {
                log(err);
                const msg = err.responseJSON.message;

                toastr.error(msg,  { fadeAway: 10000 });

                elm_save_edit_po.innerHTML = 'Simpan';

            }
        })


    }

    async function saveCancelOrder(e, id) {
        if(e) {
            e.preventDefault();
        }

        elm_cancel_order_btn.innerHTML = 'Menyimpan ' + ___iconLoading();
        elm_cancel_order_btn.disabled = true;

        let formData = new FormData(elm_form_cancel_order);
        formData.append('token', '{{ Session::get("token")}}')
        formData.append('email', '{{ Session::get("email")}}')
        formData.append('by', '{{ Session::get("user")->id }}')
        formData.append('status', 8)

        $.ajax({
            url:`{{ env('API_URL') . '/order/reverse-status/update/${id}/cancel-without-change-stock' }}`,
            method:"POST",
            data: formData,
            dataType:'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success:function(result){
                const {status, message, data} = result;


                if(status) {

                    refreshOrderDT();

                    toastr.success(message, { fadeAway: 10000 });
                    elm_cancel_order_btn.innerHTML = 'Simpan';
                    elm_cancel_order_btn.disabled = false;

                    $('#modal-cancel-order').modal('hide')
                } else {
                    elm_cancel_order_btn.disabled = false;
                    elm_cancel_order_btn.innerHTML = 'Simpan';

                    console.error(message);

                    $('#modal-cancel-order').modal('hide')
                }
            },
            error: function(err) {
                log(err);
                const msg = err.responseJSON.message;

                toastr.error(msg,  { fadeAway: 10000 });

                elm_cancel_order_btn.innerHTML = 'Simpan';
                elm_cancel_order_btn.disabled = false;
            }
        })


    }


    async function __swalConfirmationApproved(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin memprosesnya ?', id) {
        return swal({
            title: title,
            text: text,
            icon: "success",
            buttons: true,
            dangerMode: true,
        })
        .then(async (willUpdate) => {
            if (willUpdate) {

                try {

                    let res = await fetch(`{{ env('API_URL') . '/order/reverse-status/update/${id}/process-without-change-stock' }}`, Object.assign({}, __propsPOST, {
                        body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                            status: 3 // pesanan diproses
                        }))
                    }))

                    let result = await res.json();

                    const {status, message} = result;

                    if(status) {
                        refreshOrderDT();
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

    async function __swalConfirmationPesananSiapDiambil(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin memprosesnya ?', id) {
        return swal({
            title: title,
            text: text,
            icon: "success",
            buttons: true,
            dangerMode: true,
        })
        .then(async (willUpdate) => {
            if (willUpdate) {

                try {

                    let res = await fetch(`{{ env('API_URL') . '/order/reverse-status/update/${id}/pesanan-siap-diambil' }}`, Object.assign({}, __propsPOST, {
                        body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                            status: 9 // pesanan siap diambil pembeli
                        }))
                    }))

                    let result = await res.json();

                    const {status, message} = result;

                    if(status) {
                        refreshOrderDT();
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

    async function __swalConfirmationCancel(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin membatalkannya ?', id) {
        return swal({
            title: title,
            text: text,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then(async (willUpdate) => {
            if (willUpdate) {

                try {

                    let res = await fetch(`{{ env('API_URL') . '/order/reverse-status/update/${id}/cancel-without-change-stock' }}`, Object.assign({}, __propsPOST, {
                        body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                            status: 8 // pesanan diproses
                        }))
                    }))

                    let result = await res.json();

                    const {status, message} = result;

                    if(status) {
                        refreshOrderDT();
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

//eksport data bulk sesuai filter
    async function exportData(e) {
        if(e) {
            e.preventDefault();
        }

        //elemen download
        id = e.target.value;

        if(id == 1){
            elm_download_bulk.innerHTML = 'Proses ' + ___iconLoading();
            elm_download_bulk.disabled = true;
        }else{
            elm_download_bulk_principle.innerHTML = 'Proses ' + ___iconLoading();
            elm_download_bulk_principle.disabled = true;
        }



        let data = new FormData();
        data.append('token', '{{ Session::get("token")}}')
        data.append('email', '{{ Session::get("email")}}')
        data.append('by', '{{ Session::get("user")->id }}')
        data.append('warehouse_id', $('#warehouse').val())
        data.append('start_date', $('#start-date').val())
        data.append('end_date', $('#end-date').val())
        data.append('status_order', $('#status-order').val())
        data.append('eksport_type', id)

        $.ajax({
            url:`{{ env('API_URL') . '/order/export' }}`,
            method:"POST",
            data: data,
            dataType:'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success:function(result){
                const {status, message, data} = result;


                if(status) {

                    refreshOrderDT();

                    toastr.success(message, { fadeAway: 10000 });
                    if(id == 1){
                        elm_download_bulk.innerHTML = __iconPlus() + ' Export Order';
                        elm_download_bulk.disabled = false;
                    }else{
                        elm_download_bulk_principle.innerHTML = __iconPlus() + ' Export Order Principle';
                        elm_download_bulk_principle.disabled = false;
                    }


                    // elm_target_blank_bulk_file.href = data;
                    // elm_target_blank_bulk_file

                    window.open(data, '_blank');

                } else {
                    if(id == 1){
                        elm_download_bulk.disabled = false;
                        elm_download_bulk.innerHTML = __iconPlus() + ' Export Order';
                    }else{
                        elm_download_bulk_principle.disabled = false;
                        elm_download_bulk_principle.innerHTML = __iconPlus() + ' Export Order Principle';
                    }


                    log(message);
                }
            },
            error: function(err) {
                log(err);
                const msg = err.responseJSON.message;

                toastr.error(msg,  { fadeAway: 10000 });

                if(id == 1){
                    elm_download_bulk.disabled = false;
                    elm_download_bulk.innerHTML = __iconPlus() + ' Export Order';
                }else{
                    elm_download_bulk_principle.disabled = false;
                    elm_download_bulk_principle.innerHTML = __iconPlus() + ' Export Order Principle';
                }


            }
        })


    }

    function refreshOrderDT() {
        $('#dataTable').DataTable().destroy();
        fDataTable();
    }

    function getCurrentToken() {
        return {
            email : `{{ Session::get("email")}}`,
            token: `{{ Session::get("token")}}`,
            by : "{{ Session::get('user')->id }}"
        }
    }

    $('#order-detail-modal').modal('hide');

    $('#dataTable').on('click', '#view-order-detail', async function() {

        // const idx = table.row( $(this).parents('tr') )[0][0];
        const data  = table.row($(this).parents('tr')).data();

        const id_po                 = data.id;

        const courier_service       = data.courier_service      ? data.courier_service      : '';
        const courier_name          = data.courier_name         ? data.courier_name         : '';
        const number_resi           = data.number_resi          ? data.number_resi          : '';

        const address               = data.address              ? data.address              : '';
        const user_profile_phone    = data.user_profile_phone   ? data.user_profile_phone   : '';
        const user_full_name        = data.user_full_name       ? data.user_full_name       : '';
        const payment_type          = String(data.payment_type).toUpperCase();
        elm_waiting_for_fetch_data.classList.replace('inactive', 'active');

        try{

            let res = await fetch(`{{ env('API_URL') . '/order-item/by-order-id/${id_po}' }}`, Object.assign({}, __propsPOST, {
                method: 'POST'
            }))

            let result = await res.json();

            const {status, data, message} = result;
            const {order, order_items, products} = data;

            log(order);

            if(status) {
    
                let payment_type                = order.payment_type; 
                elm_payment_type.innerHTML = `<span style="text-transform:uppercase;font-weight:bold;">${payment_type}</span>`;
    
                elm_no_po.innerHTML             = order.no_po;

                elm_status_po.innerHTML         = order.status_po;
                elm_status_po.className         = '';
                elm_status_po.classList.add(order.status_po ? order.status_po.split(' ').join('-').toLocaleLowerCase() : '', 'btn', 'btn-sm')

                elm_kurir_pengiriman.innerHTML  = `${courier_name} - <span class="badge badge-primary">${courier_service}</span>`;
                elm_no_resi.innerHTML           = number_resi;
                elm_pembeli.innerHTML           = user_full_name;
                elm_alamat.innerHTML            = address;
                elm_no_hp.innerHTML             = user_profile_phone;

                // elm_ttl_harga.innerHTML         = `Rp ${__toRp(order.final_total)}`;


                if(order.status_po === 'Pesanan Dibatalkan') {

                    let ttl_price = 0;

                    for (const order_item of order_items) {
                        ttl_price += __formatValueStep(payment_type, order_item.price);
                    }

                    elm_ttl_harga.innerHTML         = `${(__formatValueStep(payment_type, order.total_ongkir ) + parseInt(ttl_price))}`;

                } else {

                    elm_ttl_harga.innerHTML         = __formatValue(payment_type, order.final_total, true, order.FINAL_TOTAL_WARPAY);

                }


                // elm_ttl_ongkir.innerHTML        = `Rp ${__toRp(order.total_ongkir)}`;
                elm_ttl_ongkir.innerHTML         = __formatValue(payment_type, order.total_ongkir);

                elm_wrap_order_items.innerHTML  = '';

                let total_weight = 0;
                for(const order_item of order_items) {
                    total_weight += parseInt(order_item.prod_gram * order_item.total_item);
                    elm_wrap_order_items.insertAdjacentHTML('afterbegin', __elmOrderItem(order_item, order.status_po, payment_type));
                }
                elm_total_berat.innerHTML = `${(total_weight / 1000)} kg`;

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');

                const apiUrl = '{{ env("API_URL") }}';

                $('#print-po-btn').attr('href', `${apiUrl}/print/purchase-order/${data.order.id}`);
                $('#print-po-div').show();


                $('#print-invoice-btn').attr('href', `${apiUrl}/print/invoice/${data.order.id}`);
                $('#print-invoice-div').show();
                $('#print-invoice-btn').show();


                if(order.status_po === 'Pesanan Belum Dibayar') {
                    $('#print-invoice-btn').hide();
                    $('#print-invoice-div').hide();
                }

                $('#order-detail-modal').modal('show');

            } else {

                elm_waiting_for_fetch_data.classList.replace('active', 'inactive');
                console.error(message)
            }

        } catch(e) {
            log(e)
        }

    })

    }; // close window onload


    function __elmOrderItem(order_item, status_po, payment_type = null) {


        const PROMOSI_TYPE = order_item.promosi_type;
        const PROMOSI_NAME = order_item.promosi_name;

        let type;
        
        if(PROMOSI_TYPE) {
            if(PROMOSI_TYPE == 1) {
                type = `<span class="badge badge-sm badge-warning" style="color:#fff">Bundling</span>`
            } else {
                type = `<span class="badge badge-sm badge-warning" style="color:#fff">Diskon</span>`
            }
        }

        let info_product = '';

        // let price_ = 0;
        let kg_ = '';

        if((PROMOSI_TYPE && PROMOSI_TYPE == 1)) {
            // log('BUNDLING')
            
            // price_ = order_item.promosi_total_value;
            kg_ = ``;

        } else if((PROMOSI_TYPE && PROMOSI_TYPE == 2)) {
            // log('DISKON')
           
            // price_ = order_item.promosi_fix_value;
            kg_ = `(${__gramToKg(order_item.prod_gram, order_item.total_item)} kg)`;
           
        } else {
            
            // price_ = order_item.prod_base_price;   
            kg_ = `(${__gramToKg(order_item.prod_gram, order_item.total_item)} kg)`;
        }
        
        /*let price_ = (PROMOSI_TYPE && PROMOSI_TYPE == 1) ? order_item.promosi_total_value : 
                    (PROMOSI_TYPE && PROMOSI_TYPE == 2) ? order_item.promosi_fix_value : order_item.prod_base_price;
        
        let kg_ = (PROMOSI_TYPE && PROMOSI_TYPE == 1) ? `` : 
                (PROMOSI_TYPE && PROMOSI_TYPE == 2) ? `(${__gramToKg(order_item.prod_gram, order_item.total_item)} kg)` : `(${__gramToKg(order_item.prod_gram, order_item.total_item)} kg)`;
        */

        // log(payment_type, price_, 'HMM');
        // log(order_item, 'HMMM2')

        if(order_item.total_item != 0) {
            if((  (order_item.total_item_before && status_po) && order_item.total_item_before !== order_item.total_item) ) {
                info_product += `
                    <tr>
                        <tr>
                            <td style="color: #F44336; border: 1px solid; display: block; font-weight: 700; width: 121px; background: transparent; text-align: center; font-size: 12px; margin-bottom: 3px; border-color: #F44336;">
                                Sebelum Konfirmasi Principle
                            </td>
                        </tr>
                        <td style="width:140px;">
                            <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                        </td>
                        
                        <td valign="top">
                            <h6> ${PROMOSI_TYPE && PROMOSI_TYPE == 1 ? PROMOSI_NAME : order_item.prod_name } ${type ? type : ''}</h6>
                            <p style="display: inline-block;margin-right:5px;">Jumlah Barang dikirim :  <h6 style="color:#2A8FCC;display: inline-block;">${order_item.total_item_before}</h6> ${kg_}</p>
                            <h6 class="mt-2" style="color:#2A8FCC;"><span style="color:#000;font-size:14px; font-weight:500;margin-right:5px">Harga per-unit : </span> ${__formatValue(payment_type, order_item.price)}</h6>
                        </td>
                    </tr>
                    <tr>
                        <tr>
                            <td style="color: #4CAF50; border: 1px solid; display: block; font-weight: 700; width: 121px; background: transparent; text-align: center; font-size: 12px; margin-bottom: 3px; border-color: #4CAF50; margin-top: 5px;">
                                Sesudah Konfirmasi Principle
                            </td>
                        </tr>
                        <td style="width:140px;">
                            <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                        </td>
                        <td valign="top">
                            <h6>${PROMOSI_TYPE && PROMOSI_TYPE == 1 ? PROMOSI_NAME : order_item.prod_name }  ${type ? type : ''}</h6>
                            <p style="display: inline-block;margin-right:5px;">Jumlah Barang dikirim :  <h6 style="color:#2A8FCC;display: inline-block;">${order_item.total_item}</h6> ${kg_}</p>
                            <h6 class="mt-2" style="color:#2A8FCC;"><span style="color:#000;font-size:14px; font-weight:500;margin-right:5px">Harga per-unit : </span> ${__formatValue(payment_type, order_item.price)}</h6>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                `
            } else {
                info_product += `
                    <tr>
                        <td style="width:140px;">
                            <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                        </td>
                        <td valign="top">
                            <h6>${PROMOSI_TYPE && PROMOSI_TYPE == 1 ? PROMOSI_NAME : order_item.prod_name }  ${type ? type : ''}</h6>
                            <p class="d-flex align-items-center">
                                <span style="width:120px">Jumlah Pesanan</span>
                                <span>${order_item.total_item} Barang ${kg_}</span>
                            </p>
                            <p class="d-flex align-items-center">
                                <span style="width:120px">Harga Per Unit</span>
                                <span style="color:#2A8FCC;"> ${__formatValue(payment_type, order_item.price)}</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                `
            }
        } else if(order_item.total_item == 0 && status_po == 'Pesanan Dibatalkan') {
            info_product += `
                    <tr>
                        <td style="width:140px;">
                            <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                        </td>
                        <td valign="top">
                            <h6>${PROMOSI_TYPE && PROMOSI_TYPE == 1 ? PROMOSI_NAME : order_item.prod_name } ${type ? type : ''}</h6>
                            <p class="d-flex align-items-center">
                                <span style="width:120px">Jumlah Pesanan</span>
                                <span>${order_item.total_item} Barang ${kg_}</span>
                            </p>
                            <p class="d-flex align-items-center">
                                <span style="width:120px">Harga Per Unit</span>
                                <span style="color:#2A8FCC;">${__formatValue(payment_type, order_item.price )}</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                `
        }

        /*return `
        <tr>
            <td style="width:140px;">
                <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
            </td>
            <td valign="top">
                <h6>${order_item.prod_name}</h6>
                <p style="display: inline-block;margin-right:5px;">Jumlah Barang dikirim :  <h6 style="color:#2A8FCC;display: inline-block;">${order_item.total_item}</h6> (${__gramToKg(order_item.prod_gram, order_item.total_item)} kg)</p>
                <h6 class="mt-2" style="color:#2A8FCC;"><span style="color:#000;font-size:14px; font-weight:500;margin-right:5px">Harga per-unit : </span> Rp ${__toRp(order_item.prod_base_price)}</h6>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr></td>
        </tr>
        `*/

        return info_product;
    }

    var wrap_order_items ;
    function __elmOrderItemWithUpdateStock(order_item,order_items, length_of_order_items = 0, order_id = null, warehouse_id = null, order = null, payment_type = null) {


        const PROMOSI_TYPE = order_item.promosi_type;

        let type;

        if(PROMOSI_TYPE) {
            if(PROMOSI_TYPE == 1) {
                type = `<span class="badge badge-sm badge-warning" style="color:#fff">Bundling</span>`
            } else {
                type = `<span class="badge badge-sm badge-warning" style="color:#fff">Diskon</span>`
            }
        }


        wrap_order_items = order_items;

        // validation cancel order item
        if(length_of_order_items > 1) {

            return `
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width:140px;">
                        <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                    </td>
                    <td valign="top">
                        <h6 style="display:flex;justify-content:space-between;">${order_item.prod_name} ${type ? type : ''}
                            <button class="btn p-0 cancel-order-item" value="${order_item.id}" type="button" data-toggle="tooltip" title="Batal Order Item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            </button>
                        </h6>
                        <p class="d-flex align-items-center">
                            <span style="width:155px">Jumlah Pesanan</span>
                            <input type="number" class="form-control  form-control-sm" value="${order_item.total_item}" disabled>
                        </p>
                        <p class="d-flex align-items-center">
                            <span style="width:155px"> Stock dikirim</span>
                            <input type="hidden" class="form-control  form-control-sm" name="total_price_order_item[${order_item.id}]">
                            <input type="number" class="form-control  form-control-sm" name="total_stock_order_item[${order_item.id}]" value="${order_item.total_item}" onkeyup="changeStock(${JSON.stringify(order_item).replace(/\"/g,"'")}, this, ${order_id}, ${warehouse_id}, ${JSON.stringify(order).replace(/\"/g,"'")})">
                        </p>
                        <h6 class="mt-2" style="color:#2A8FCC;" id="total_price"><span style="color:#000;font-size:14px; font-weight:500;margin-right:5px">Harga per-unit</span> ${__formatValue(payment_type, order_item.price)}</h6>
                    </td>
                </tr>

            `

        } else {

            return `
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <td style="width:140px;">
                        <img src="{{ env('API_URL') . '/' }}${order_item.path}" onerror="this.onerror=null;this.src='{{ env('API_URL'). '/' }}assets/product_image/_blank.jpg';" style="width:120px;" draggable="false">
                    </td>
                    <td valign="top">
                        <h6 style="display:flex;justify-content:space-between;">${order_item.prod_name} ${type ? type : ''}</h6>
                        <p class="d-flex align-items-center">
                            <span style="width:155px">Jumlah Pesanan</span>
                            <input type="number" class="form-control  form-control-sm" value="${order_item.total_item}" disabled>
                        </p>
                        <p class="d-flex align-items-center">
                            <span style="width:155px"> Stock dikirim</span>
                            <input type="hidden" class="form-control  form-control-sm" name="total_price_order_item[${order_item.id}]">
                            <input type="number" class="form-control  form-control-sm" name="total_stock_order_item[${order_item.id}]" value="${order_item.total_item}" onkeyup="changeStock(${JSON.stringify(order_item).replace(/\"/g,"'")}, this, ${order_id}, ${warehouse_id}, ${JSON.stringify(order).replace(/\"/g,"'")})">
                        </p>
                        <h6 class="mt-2" style="color:#2A8FCC;" id="total_price"><span style="color:#000;font-size:14px; font-weight:500;margin-right:5px">Harga per-unit</span> ${__formatValue(payment_type, order_item.price)}</h6>
                    </td>
                </tr>

            `

        }

        
    }


    async function changeStock(order_item, e, id_po, wh_id, order) {
        
        let payment_type = order.payment_type;

        const load                       = '<div style="width:100%;display:flex; justify-content:center;">'+___iconLoading('black')+'</div>';
        
        elm_ttl_ongkir_3.innerHTML       = load;
        elm_final_total_2.innerHTML      = load;

        //elm_name = e.name;
        //__querySelector(`input[name='${elm_name}']`).value = e.value;


        let key = order_item.id;
        let all_total_price = 0;

        let elm_total_item = __querySelector(`input[name='total_stock_order_item[${key}]']`);
        let elm_total_price = __querySelector(`input[name='total_price_order_item[${key}]']`);

        // print total weight diluar validasi
        let TOTAL_WEIGHT = 0;

        // get price by id
        let price_order_item
        for(const item of wrap_order_items) {
            if(item.total_item !== 0) {
                if(item.id == key) {
                    price_order_item = item.price;
                    // log(elm_total_item.value , item.prod_gram, 'INI KEY');
                    TOTAL_WEIGHT += parseInt(elm_total_item.value) * parseInt(item.prod_gram);
                }
                if(item.id !== key) {
                    // log(item.total_item, item.prod_gram, 'INI BUKAN');
                    TOTAL_WEIGHT += parseInt(item.total_item) * parseInt(item.prod_gram)
                }
               
            }
        }

        // validation 1
        if(elm_total_item.value < 1 || elm_total_item.value == '') {
            elm_total_item.value = 1
        }
        // validation 2
        if(elm_total_item.value > order_item.total_item) {

            elm_total_item.value =  order_item.total_item;
            
            // calc stock * price
            let total_price_peritem = (elm_total_item.value * price_order_item );
            elm_total_price.value = parseInt(total_price_peritem);

            // save final total to element
            let total_price = 0;
            let final_total = 0;
            let total_price_manipulate = 0;
            let TOTAL_WEIGHT_2 = 0;

            for(const item of wrap_order_items) {
                if(item.total_item !== 0) {
                    
                    let price = parseInt(__querySelector(`input[name='total_price_order_item[${item.id}]']`).value)
                    let priceManipulate = __formatValueStep(payment_type, parseInt(__querySelector(`input[name='total_price_order_item[${item.id}]']`).value))

                    total_price += price;
                    total_price_manipulate += priceManipulate;
                    
                    if(item.id == key) {
                        price_order_item = item.price;
                        // log(elm_total_item.value , item.prod_gram, 'INI KEY');
                        TOTAL_WEIGHT_2 += parseInt(elm_total_item.value) * parseInt(item.prod_gram);
                    }
                    if(item.id !== key) {
                        // log(item.total_item, item.prod_gram, 'INI BUKAN');
                        TOTAL_WEIGHT_2 += parseInt(item.total_item) * parseInt(item.prod_gram)
                    }
                }
            }

            toastr.error(`Stock tidak boleh melebihi batas yang ditentukan (Max. ${order_item.total_item})`,  { fadeAway: 300 });


            // print total ongkir didalam validasi

            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

            let res = await fetch(`{{ env('API_URL') . '/rajaongkir/get-order/FROM-RAJAONGKIR' }}`, Object.assign({}, __propsPOST, {
                body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                    warehouse_id: wh_id,
                    order_id: id_po,
                    total_weight: TOTAL_WEIGHT_2
                }))
            }))

            let result_ongkir = await res.json();

            const { data } = result_ongkir;
            
            if(data !== '0') {

                const { price_ori } = data;

                order_total_ongkir.value = price_ori;
                elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, price_ori)}`;
            } else {
                elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, order_total_ongkir.value)}`;
            }


            final_total = (parseInt(parseInt(total_price) + parseInt(order_total_ongkir.value)))
            total_price = parseInt(parseInt(total_price))

            order_final_total.value = final_total;
            order_total_price.value = total_price;


            elm_final_total_2.innerHTML = `${total_price_manipulate}`

            return
        }

        // searcing ongkir berdasarkan weight

        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

        let res = await fetch(`{{ env('API_URL') . '/rajaongkir/get-order/FROM-RAJAONGKIR' }}`, Object.assign({}, __propsPOST, {
            body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                warehouse_id: wh_id,
                order_id: id_po,
                total_weight: TOTAL_WEIGHT
            }))
        }))

        let result_ongkir = await res.json();
        // log(result_ongkir);
        const { data } = result_ongkir;
        
        if(data !== '0') {
            
            const { price_ori } = data;
            
            order_total_ongkir.value = price_ori;
            elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, price_ori)}`;
        } else {
            elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, order_total_ongkir.value)}`;

        }

        // calc stock * price
        let total_price_peritem = (elm_total_item.value * price_order_item );
        elm_total_price.value = parseInt(total_price_peritem);

        // save final total to element
        let total_price = 0;
        let final_total = 0;
        let total_price_manipulate = 0;

        for(const item of wrap_order_items) {
            if(item.total_item !== 0) {
                
                let price = parseInt(__querySelector(`input[name='total_price_order_item[${item.id}]']`).value)
                let priceManipulate = __formatValueStep(payment_type, parseInt(__querySelector(`input[name='total_price_order_item[${item.id}]']`).value))

                total_price += price;
                total_price_manipulate += priceManipulate;
            }
        }

        final_total = (parseInt(parseInt(total_price) + parseInt(order_total_ongkir.value)))
        total_price = parseInt(parseInt(total_price))

        order_final_total.value = final_total;
        order_total_price.value = total_price;


        elm_final_total_2.innerHTML = `${total_price_manipulate}`
    }

    async function preventPositiveNumber(key,e, order_item, id_po, wh_id, order) {

        let payment_type = order.payment_type;
        
        const load                       = '<div style="width:100%;display:flex; justify-content:center;">'+___iconLoading('black')+'</div>';
        
        elm_ttl_ongkir_3.innerHTML       = load;
        elm_final_total_2.innerHTML      = load;

        let elm_total_item = __querySelector(`input[name='total_stock_order_item[${key}]']`);
        let elm_total_price = __querySelector(`input[name='total_price_order_item[${key}]']`);


        // print total weight diluar validasi
        let TOTAL_WEIGHT = 0;


        // get price by id
        let price_order_item
        for(const item of wrap_order_items) {
            if(item.total_item !== 0) {
                if(item.id == key) {
                    price_order_item = item.price;
                    // log(elm_total_item.value , item.prod_gram, 'INI KEY');
                    TOTAL_WEIGHT += parseInt(elm_total_item.value) * parseInt(item.prod_gram);
                }
                if(item.id !== key) {
                    // log(item.total_item, item.prod_gram, 'INI BUKAN');
                    TOTAL_WEIGHT += parseInt(item.total_item) * parseInt(item.prod_gram)
                }
               
            }
        }

        // validation 1
        if(elm_total_item.value < 1 || elm_total_item.value == '') {
            elm_total_item.value = 1
        }

        // validation 2
        if(elm_total_item.value > order_item.total_item) {

            elm_total_item.value =  order_item.total_item;

             // calc stock * price
            let total_price_peritem = (elm_total_item.value * price_order_item );
            elm_total_price.value = parseInt(total_price_peritem);

            // save final total to element
            let total_price = 0;
            let final_total = 0;
            let TOTAL_WEIGHT_2 = 0;

            for(const item of wrap_order_items) {
                if(item.total_item !== 0) {
                    
                    let price = parseInt(__querySelector(`input[name='total_price_order_item[${item.id}]']`).value)
                    total_price += price;

                    if(item.id == key) {
                        price_order_item = item.price;
                        // log(elm_total_item.value , item.prod_gram, 'INI KEY');
                        TOTAL_WEIGHT_2 += parseInt(elm_total_item.value) * parseInt(item.prod_gram);
                    }
                    if(item.id !== key) {
                        // log(item.total_item, item.prod_gram, 'INI BUKAN');
                        TOTAL_WEIGHT_2 += parseInt(item.total_item) * parseInt(item.prod_gram)
                    }
                }
            }


            toastr.error(`Stock tidak boleh melebihi batas yang ditentukan (Max. ${order_item.total_item})`,  { fadeAway: 300 });


             // print total ongkir didalam validasi

            const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

            let res = await fetch(`{{ env('API_URL') . '/rajaongkir/get-order/FROM-RAJAONGKIR' }}`, Object.assign({}, __propsPOST, {
                body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                    warehouse_id: wh_id,
                    order_id: id_po,
                    total_weight: TOTAL_WEIGHT_2
                }))
            }))

            let result_ongkir = await res.json();
            // log(result_ongkir);
            const { data } = result_ongkir;

            if(data !== '0') {

                const { price_ori } = data;

                order_total_ongkir.value = price_ori;
                elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, price_ori)}`;
            } else {
                elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, order_total_ongkir.value)}`;
            }

            final_total = (parseInt(parseInt(total_price) + parseInt(order_total_ongkir.value)))
            total_price = parseInt(parseInt(total_price))

            order_final_total.value = final_total;
            order_total_price.value = total_price;


            elm_final_total_2.innerHTML = `${__formatValue(payment_type, total_price)}`

            return
        }

        // searcing ongkir berdasarkan weight

        const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(getCurrentToken())})

        let res = await fetch(`{{ env('API_URL') . '/rajaongkir/get-order/FROM-RAJAONGKIR' }}`, Object.assign({}, __propsPOST, {
            body: JSON.stringify(Object.assign({}, getCurrentToken(), {
                warehouse_id: wh_id,
                order_id: id_po,
                total_weight: TOTAL_WEIGHT
            }))
        }))

        let result_ongkir = await res.json();
        // log(result_ongkir);
        const { data } = result_ongkir;
        
        if(data !== '0') {
            
            const { price_ori } = data;
            
            order_total_ongkir.value = price_ori;
            elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, price_ori)}`;
        } else {
            elm_ttl_ongkir_3.innerHTML = `${__formatValue(payment_type, order_total_ongkir.value)}`;
        }


        // calc stock * price
        let total_price_peritem = (elm_total_item.value * price_order_item );
        elm_total_price.value = parseInt(total_price_peritem);


        // save final total to element
        let total_price = 0;
        let final_total = 0;

        for(const item of wrap_order_items) {
            if(item.total_item !== 0) {
                let price = parseInt(__querySelector(`input[name='total_price_order_item[${item.id}]']`).value)
                total_price += price;
            }
        }

        final_total = (parseInt(parseInt(total_price) + parseInt(order_total_ongkir.value)))
        total_price = parseInt(parseInt(total_price))

        order_final_total.value = final_total;
        order_total_price.value = total_price;


        elm_final_total_2.innerHTML = `${__formatValue(payment_type, total_price)}`
    }

    // filter time order

    if($('#start-date-picker').length) {
        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());


        $('#start-date-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#start-date").val(formatedValue)
                $('#dataTable').DataTable().destroy();
                fDataTable();
            }
        });

        $("#start-date").on('keyup', function() {
            $('#dataTable').DataTable().destroy();
            fDataTable();
        })


        /*$('#start-date-picker').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: true,
            autoclose: true,
            altField: "#start-date",
            altFormat: "dd-mm-yyyy"
        });*/

        /*$('#start-date-picker').change(function() {
            log($("#start-date").val())
            $('#dataTable').DataTable().destroy();
            fDataTable();
        });
        */
    }


    if($('#end-date-picker').length) {
        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());


        $('#end-date-picker').datetimepicker({
            format: 'YYYY-MM-DD H:mm:ss',
            sideBySide: true
        }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('YYYY-MM-DD H:mm:ss');
                $("#end-date").val(formatedValue)
                $('#dataTable').DataTable().destroy();
                fDataTable();
            }
        });

        $("#end-date").on('keyup', function() {
            $('#dataTable').DataTable().destroy();
            fDataTable();
        })

        /*$('#end-date-picker').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: true,
            autoclose: true,
            altField: "#end-date",
            altFormat: "dd-mm-yyyy"
        });

        $('#end-date-picker').change(function() {
            log($("#end-date").val())
            $('#dataTable').DataTable().destroy();
            fDataTable();
        });
        */



    }



    if($('#start-time-sd').length) {
        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());


        $('#start-time-sd').datetimepicker({
            format: 'H:mm',
            sideBySide: true,
            icons:
            {
                up: 'fa fa-angle-up',
                down: 'fa fa-angle-down'
            },
            }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('H:mm');
                //log(moment(e.date).format('H:mm').valueOf())
                const ms = moment(e.date,'YYYY-MM-DD').valueOf()
                $('#start-time-sd-tmp').val(ms);

                let ttl_start = $('#start-time-sd-tmp').val();
                let ttl_end = $('#end-time-sd-tmp').val();

                $("#start-time-sd").val(formatedValue)
            }
        });
    }


    $('#end-time-sd').datetimepicker({
            format: 'H:mm',
            sideBySide: true,
            icons:
            {
                up: 'fa fa-angle-up',
                down: 'fa fa-angle-down'
            },
            }).on('dp.change', function (e) {
            if(e && e.date) {
                let formatedValue = e.date.format('H:mm');
                $("#end-time-sd").val(formatedValue)
            }
    });





</script>

@endsection
