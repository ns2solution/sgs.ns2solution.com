// ------------------------------------------
// ----------- maintenane by @sobari ------------
// -----------------------------------------

var zone = {
    prodcut: 'prodcut',
}


const dir = console.dir;
const log = console.log;
const warn = console.warn
const error = console.error;

const propModalPreventClick = {
    backdrop: 'static',
    keyboard: false,
    show:false
}

const propDatatable = {
    pageLength: 5,
    processing: true,
    deferRender: true,
    pagingType: "simple",
    dom: '<"toolbar">frtip',
    info:     false,
    autoWidth: false,
    columnDefs: [ {
        searchable: false,
        orderable: false,
        targets: [0]
    } ],
    language: {
        lengthMenu: "Display _MENU_ records per page",
        zeroRecords: "Tidak ada data",
        info: "Showing page _PAGE_ of _PAGES_",
        infoEmpty: "No records available",
        infoFiltered: "(filtered from _MAX_ total records)"
    }
}

const propertyDB = {
    scrollX: true,
    pageLength: 5,
    processing: true,
    bLengthChange:false,
    search:false,
    bFilter:true,
    serverSide: true,
    orderCellsTop: true,
    fixedHeader: true,
}

const optGraph = {
    fontName: 'Arial',
    height: 300,
    fontSize: 12,
    animation: {
        duration: 600,
        easing: "out",
        startup: true
    },
    chartArea: {
        left: '10%',
        width: '100%',
        height: 260
    },
    backgroundColor: 'transparent',
    tooltip: {
        textStyle: {
            fontName: 'Arial',
            fontSize: 13
        },
        isHtml: true
    },
    // curveType: 'function',
    pointSize: 5,
    pointShape:'square',
    lineWidth:1.6,
    vAxis: {
        title: 'Value',
        titleTextStyle: {
            fontSize: 12,
            italic: false,
            color: '#333'
        },
        textStyle: {
            color: '#333'
        },
        baselineColor: '#ccc',
        gridlines:{
            color: '#eee',
            count: 10
        },
        minValue: 0,
        maxValue: 5.0,
        format: '#.##'
    },
    hAxis: {
        textStyle: {
            color: '#333'
        }
    },
    legend: {
        position: 'top',
        alignment: 'center',
        textStyle: {
            color: '#333'
        }
    },
};

const managePageDashboard = () => {
    if(PAGE.isActive) {

        for(const elm of __querySelectorAll('.d-none')) {
            elm.classList.replace('d-none', 'd-block');
        }
        for(const elm of __querySelectorAll('.lazy-loading')) {
            elm.classList.add('d-none');
        }                

    } 
}


const rulesValidateGlobal = {
    onfocusout: (elm) => {
        return $(elm).valid();
    },
    ignore: [],
    errorClass: "error",
    errorElement: "span",
    errorClass: "help-inline text-danger",
    errorElement: "span",
    highlight: (elm, errorClass, validClass) => {
        $(elm).addClass('has-error');
    },
    unhighlight: (elm, errorClass, validClass) => {
        $(elm).removeClass('has-error');
    },
    errorPlacement: function (error, elm) {
        if(elm.hasClass('select2-hidden-accessible')) {
            log('a')
            error.insertAfter(elm.next('.select2.select2-container.select2-container--default'));
        } else {
            log('b')
            error.insertAfter(elm);
        }

    }
    ,
}

function __getId(name) {
    return document.getElementById(name);
}

function __getClass(name) {
    return document.getElementsByClassName(name);
}

function __querySelectorAll(tag) {
    return document.querySelectorAll(tag);
}

function __querySelector(tag) {
    return document.querySelector(tag);
}

function __toRp(money) {
    return new Intl.NumberFormat('id').format(money)
}

function __gramToKg(gram, total) {
    return  ((gram * total) / 1000)
}

function __serializeForm(form,  typeformdata=null) {

    let obj = {};
    let formData = new FormData(form)

    formData.forEach((value, key) => {
        obj[key] = value
    });
    let json = JSON.stringify(obj);
    return json
    // return new URLSearchParams(new FormData(form)).toString()
}

Number.prototype.format = function(n, x, s, c) {
var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
    num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};

function porpertyPOST(body) {
    return {
        headers: __headers(),
        method: 'POST',
        body: JSON.stringify(body)
    }
}


function __headers() {
    return {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json, text/plain, */*',
        "Content-type": "application/json"
    }
}

function __swalSuccess(msg) {
    return swal(msg, {
        icon: "success",
    });
}

function __swalInfo(msg) {
    return swal(msg, {
        icon: "info",
    });
}

function __swalConfirmation(title = 'Apakah anda yakin ?', text = 'Apakah anda yakin ingin menghapusnya ?', command) {
    return swal({
        title: title,
        text: text,
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then(async (willDelete) => {
        if (willDelete) {

            eval(command);

            /*try {

                let res = await fetch(__getUrl(`api/service/delete/service-exam/${id_service_exam}`), {
                    headers: __headers(),
                    method: 'DELETE',
                })

                let result = await res.json();

                const {status, message} = result;

                if(status) {
                    if ($.fn.DataTable.isDataTable("#daftar-layanan-table")) {
                        $('#daftar-layanan-table').DataTable().clear().destroy();
                        dl_dt = $('#daftar-layanan-table').DataTable(propLayananDT).draw();
                    }
                    toastr.success(message, { fadeAway: 10000 });
                } else {
                    toastr.error('Ops.. something went wrong!',  { fadeAway: 50000 });
                    console.error(message)
                }
            } catch (error) {
                console.error(error);
            }*/
        }
    })
}

function __dateYYYYMMDD(value) {
    return moment(new Date(value)).format('YYYY-MM-DD');
}

function __dateYYYYMMDDHis(value) {
    return moment(new Date(value)).format('YYYY-MM-DD H:mm:s');
}

function __dateNOW() {
    return moment(new Date()).format('YYYY-MM-DD');
}

const ___createOpt = (value, title) => {
    let opt = document.createElement("option");
    opt.value = value;
    opt.innerHTML = title;
    return opt;
};

function elm_choose(msg = 'Pilih') {
    return `<option value=""> --- ${msg}--- </option>`;
}

function __iconPlus() {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square link-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>'
}

function ___iconLoading(color="white") {
    return `<svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke=${color} class="w-4 h-4 ml-3">
                <g fill="none" fill-rule="evenodd">
                    <g transform="translate(1 1)" stroke-width="4">
                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                        <path d="M36 18c0-9.94-8.06-18-18-18" transform="rotate(114.132 18 18)">
                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                        </path>
                    </g>
                </g>
            </svg>`;
}
window.addEventListener('load', (e) => {
    e.preventDefault();
    localStorage.removeItem('REQUEST_STOPED')
    $('html, body').animate({scrollTop: '0px'}, 500);
    log('load')
})
window.addEventListener('beforeunload', (e) => {
    e.preventDefault()
    localStorage.setItem('REQUEST_STOPED', true)
    log('beforeunload')
})

function removeManageError(type) {
    switch(type) {
        case zone.prodcut :
            $('#form-product input.has-error').removeClass('has-error');
            $('#form-product textarea.has-error').removeClass('has-error');
            $('#form-product select.has-error').removeClass('has-error');
            break;
    }

}


function insertAdjHTML(elm_name, position = 'afterbegin', html) {
    __getId(`${elm_name}`).insertAdjacentHTML(`${position}`,`${html}`);
}

function eventListener(elm_name, callback, type = 'click') {
    __getId(`${elm_name}`).addEventListener(type, callback);
}


function __newPromise(api_name, api_url, option = null) {

    // console.log(api_name);

    let process = new Promise(async (resolve, reject) => {

        // console.warn(`Promise started ${api_name}`);

        try{
            
            let data = null;

            if(option) {
                data = await (await fetch(`${api_url}`, option)).json();
            } else {
                data = await (await fetch(`${api_url}`)).json();
            }

            // console.log(data)
            resolve(data)

        } catch(e) {

            reject(e);

        }
    });

    let json = process.then(
        (msg) => {
            // console.log("Resolved: ", msg);
            return msg;
        },
        (err) => {
            // console.error("Rejected: ", err);
            return err;
        }
    );

    return json;


}



function __modalManage(name, type) {
    switch(type) {
        case 'hide':
            $(`${name}`).modal("hide");
        break;
        case 'show':
            $(`${name}`).modal("show");
        break;
    }
}


function __manageError(elm_name) {
    $(`${elm_name} input.has-error`).removeClass('has-error');
    $(`${elm_name} textarea.has-error`).removeClass('has-error');
    $(`${elm_name} select.has-error`).removeClass('has-error');
    $(`${elm_name} .help-inline.text-danger`).remove()
    
    $('.dropify-error').empty();
    $('.dropify-errors-container').empty();
}


// function __newPromise(api_name, api_url, option) {

//     let process = new Promise(async (resolve, reject) => {

//         console.warn(`Promise started get ${api_name}`);

//         try{

//             const data =  await fetch(api_url,option);

//             let res = data.json();
//             resolve(res);

//         } catch(e) {

//             reject(e);

//         }
//     });

//     let json = process.then(
//         (result) => {
//             return result;
//         },
//         (err) => {
//             return err;
//         }
//     );

//     return json;
// }


function __resetForm(elm_name) {
    for(const elm of elm_name) {
        elm.value = '';
        if(elm.type == 'select-one') {
            elm.dispatchEvent(new Event("change", {bubbles: true,}));
        }
    }
}


function sTop() {
    return $('html, body').animate({scrollTop: '0px'}, 500);
}

function validateForm(rules_validation, elm_name, callback_update, callback_save) {
    
    return {
            rules: {
                ...rules_validation,
            },
            ...rulesValidateGlobal,
            submitHandler:(form, e) => {
                e.preventDefault();

                const id =  __getId(`${elm_name}`).value ? __getId(`${elm_name}`).value : null;

                if(id) {
                    callback_update(e, id)
                } else {
                    callback_save(e)
                }

                return false;
            }
    }
}

isZero = (element) => element == 0;

Object.defineProperty(Array.prototype, 'chunk_inefficient', {
    value: function(chunkSize) {
        var array = this;
        return [].concat.apply([],
        array.map(function(elem, i) {              
            return i % chunkSize ? [] : [array.slice(i, i + chunkSize)];
        })
        );
    }
    });


    function my_group(arr) {
        let i, j = 1, fix = [], map = {};
        fix.push(arr.shift());
        for (i in arr) {
          let k = arr[i], tgl = k[0];
          if (typeof map[tgl] === "undefined") {
            k.shift();
            fix.push([tgl, ...k]);
            map[tgl] = j++;
          } else {
            let m;
            for (m in k) {
              if (m == 0)
                continue;
      
              if (typeof fix[map[tgl]][m] === "undefined") {
                fix[map[tgl]][m] = k[m];
              } else {
                fix[map[tgl]][m] += k[m];
              }
            }
          }
        }
        return fix;
      }


      function __getParam($param){
        const url = new URLSearchParams(window.location.search);
        const par = url.get($param)
        return par;
    }
