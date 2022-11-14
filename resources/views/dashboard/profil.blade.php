@extends('layouts.app')

@section('title', '| ' . Session::get('user')->fullname)
@section('breadcrumb', 'Profil')

@section('content')



<style>

.ui-w-80 {
    width: 80px !important;
    height: auto;
}

.btn-default {
    border-color: rgba(24,28,33,0.1);
    background: rgba(0,0,0,0);
    color: #4E5155;
}

label.btn {
    margin-bottom: 0;
}

.btn-outline-primary {
    border-color: #26B4FF;
    background: transparent;
    color: #26B4FF;
}

.btn {
    cursor: pointer;
}

.text-light {
    color: #babbbc !important;
}

.btn-facebook {
    border-color: rgba(0,0,0,0);
    background: #3B5998;
    color: #fff;
}

.btn-instagram {
    border-color: rgba(0,0,0,0);
    background: #000;
    color: #fff;
}

.card {
    background-clip: padding-box;
    box-shadow: 0 1px 4px rgba(24,28,33,0.012);
}

.row-bordered {
    overflow: hidden;
}

.account-settings-fileinput {
    position: absolute;
    visibility: hidden;
    width: 1px;
    height: 1px;
    opacity: 0;
}
.account-settings-links .list-group-item.active {
    font-weight: bold !important;
}
html:not(.dark-style) .account-settings-links .list-group-item.active {
    background: transparent !important;
}
.account-settings-multiselect ~ .select2-container {
    width: 100% !important;
}
.light-style .account-settings-links .list-group-item {
    padding: 0.85rem 1.5rem;
    border-color: rgba(24, 28, 33, 0.03) !important;
}
.light-style .account-settings-links .list-group-item.active {
    color: #4e5155 !important;
}
.material-style .account-settings-links .list-group-item {
    padding: 0.85rem 1.5rem;
    border-color: rgba(24, 28, 33, 0.03) !important;
}
.material-style .account-settings-links .list-group-item.active {
    color: #4e5155 !important;
}
.dark-style .account-settings-links .list-group-item {
    padding: 0.85rem 1.5rem;
    border-color: rgba(255, 255, 255, 0.03) !important;
}
.dark-style .account-settings-links .list-group-item.active {
    color: #fff !important;
}
.light-style .account-settings-links .list-group-item.active {
    color: #4E5155 !important;
}
.light-style .account-settings-links .list-group-item {
    padding: 0.85rem 1.5rem;
    border-color: rgba(24,28,33,0.03) !important;
}


    
</style>
<div class="container light-style flex-grow-1 container-p-y">


<div class="card overflow-hidden">
    <div class="card-header d-flex justify-content-between" style="padding:1.35rem 1.5rem 0rem 1.5rem;">
        <div class="d-flex justify-content-between align-items-baseline">
            <h5 class="card-title"> Edit Profile </h5>
        </div>
        <div id="info-update"> </div>
    </div>
    <div class="row no-gutters row-bordered row-border-light">
    <div class="col-md-3 pt-0">
    <div class="list-group list-group-flush account-settings-links">
        <a class="list-group-item list-group-item-action active" data-toggle="list" href="#account-general">Umum</a>
        <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-change-password">Ganti Password</a>
        <!-- <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-social-links">Social links</a> -->
        <!-- <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-notifications">Notifications</a> -->
    </div>
    </div>
    <div class="col-md-9">
    <div class="tab-content">
        <div class="tab-pane fade active show" id="account-general">
        <form id="GF">
            <!-- <div class="card-body media align-items-center">
                <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="" class="d-block ui-w-80 rounded">
                <div class="media-body ml-4">
                <label class="btn btn-outline-primary">
                    Change
                    <input type="file" name="photo_profile" class="account-settings-fileinput">
                </label>

                <div class="text-light small mt-1">Allowed JPG, GIF or PNG. Max size of 800K</div>
                </div>
            </div> -->

            <input type="hidden" name="id" id="id-general">

            <div class="card-body">
                <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Masukkan Nama Lengkap">
                </div>
                <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" class="form-control mb-1" name="email2" id="email" placeholder="Masukkan Email">
                </div>
            </div>


            <div class="card mt-3 p-3">
                <div class="text-right">
                <button type="submit" id="btn-save-gen" class="btn btn-primary">Save</button>&nbsp;
                <!-- <button type="button" class="btn btn-default">Cancel</button> -->
                </div>
            </div>
        </form>
        </div>
        <div class="tab-pane fade" id="account-change-password">

        <form id="PF">
            <div class="card-body pb-2">
                <input type="hidden" name="id" id="id-password">

                <!-- <div class="form-group">
                <label class="form-label">Password saat ini</label>
                <input type="password" name="oldpassword" class="form-control">
                </div> -->

                <div class="form-group">
                <label class="form-label">Password Baru <br><span style="color:red;font-style:italic"> Password minimal 6 karakter, harus terdapat alphabet dan numeric</label>
                <input type="password" name="password" class="form-control">
                </div>

                <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name= "confirmnewpassword" class="form-control">
                </div>

                
            </div>


            <div class="card mt-3 p-3">
                <div class="text-right">
                <button type="submit" id="btn-save-pass" class="btn btn-primary">Save</button>&nbsp;
                <!-- <button type="button" class="btn btn-default">Cancel</button> -->
                </div>
            </div>
        </form>

        </div>
 
    </div>
</div>
</div>

</div>




@endsection

@section('js')

<script>

    


    window.onload = async () => {
        
        let [ 
            elmFormGeneral,
            elmFormPassword,
            elmIdGeneral,
            elmIdPassword,
            elmFullname,
            elmEmail,
            elmBtnSaveG,
            elmBtnSaveP
        ] = [
            __getId('GF'),
            __getId('PF'),
            __getId('id-general'),
            __getId('id-password'),
            __getId('fullname'),
            __getId('email'),
            __getId('btn-save-gen'),
            __getId('btn-save-pass'),
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

		const fetchProfile = {{ Session::get('user')->id }};





        $('#GF').submit((e) => {
			e.preventDefault();
		}).validate(validateForm({  fullname:'required', email:'required' }, 'id-general', updateProfile, updateProfile));



        $('#PF').submit((e) => {
			e.preventDefault();
		}).validate(validateForm({  
        // oldpassword:'required', 
        password:'required', confirmnewpassword:'required' }, 'id-general', updateProfile2, updateProfile2));

        if(fetchProfile) {

            elmIdGeneral.value        = "{{ Session::get('user')->id }}";
            elmIdPassword.value        = "{{ Session::get('user')->id }}";

            elmFullname.value = "{{ Session::get('user')->fullname }}";
            elmEmail.value ="{{ Session::get('user')->email }}";

        } else {

            elmIdGeneral.value   = null;
            elmIdPassword.value   = null;

        }


        async function updateProfile(e, id) {

            if(e) {
                e.preventDefault();
            }

            let formData = __serializeForm(elmFormGeneral);
            const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())
            elmBtnSaveG.innerHTML = 'Saving ' + ___iconLoading();
            elmBtnSaveG.disabled = true;

            try {

                blockUI();
                
                const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
                let res = await fetch(`{{ env('API_URL') . '/profile/update-web' }}`, __propsPOST)
                let result = await res.json();

                const {status, message, data} = result;

                if(status) {

                    toastr.success(message, { fadeAway: 10000 });
                    elmBtnSaveG.innerHTML = 'Save';
                    elmBtnSaveG.disabled = false;

                    if(message === 'Data pengguna berhasil diperbarui. Email aktivasi sudah terkirim, harap aktivasi email anda') {
                        
                        setTimeout(() => {
                            
                            window.location.href = window.location.origin+'/logout';

                        }, 2500);

                    } else {

                            window.open(window.location.origin+'/UPT-SESSION/'+btoa(JSON.stringify(data)));
                            window.focus();

                            setTimeout(function(){

                                location.reload();

                            }, 1000);

                    }

                } else {

                    unblockUI();

                    toastr.error(message, { fadeAway: 10000 });
                    elmBtnSaveG.disabled = false;
                    elmBtnSaveG.innerHTML = 'Save';
                }

            } catch (e) {
                
                unblockUI();

                toastr.error(e, { fadeAway: 10000 });

            }
        }




        async function updateProfile2(e, id) {

            if(e) {
                e.preventDefault();
            }

            let formData = __serializeForm(elmFormPassword);
            const newFormData =   Object.assign({}, JSON.parse(formData), getCurrentToken())
            elmBtnSaveP.innerHTML = 'Saving ' + ___iconLoading();
            elmBtnSaveP.disabled = true;

            try {
                
                const __propsPOST = Object.assign({}, porpertyPOST(), { body: JSON.stringify(newFormData) })
                let res = await fetch(`{{ env('API_URL') . '/profile/update-web' }}`, __propsPOST)
                let result = await res.json();

                const {status, message, data} = result;

                if(status) {

                    toastr.success(message, { fadeAway: 10000 });
                    elmBtnSaveP.innerHTML = 'Save';
                    elmBtnSaveP.disabled = false;


                    location.reload();


                } else {
                    toastr.error(message, { fadeAway: 10000 });
                    elmBtnSaveP.disabled = false;
                    elmBtnSaveP.innerHTML = 'Save';
                }

            } catch (e) {
                

                toastr.error(e, { fadeAway: 10000 });

            }
        }


    }


</script>

@endsection