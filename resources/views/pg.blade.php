@extends('layouts.app')

@section('title', '| PG')
@section('breadcrumb', 'PG')

@section('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
<script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="SB-Mid-client-vUenKtksF_reh1Jz"></script>

<p>Last order id paid -> 133</p>
<button id="pay-button" class="btn btn-primary">Pay!</button>
<pre><div id="result-json">JSON result will appear here after payment:<br></div></pre>
<input id="token" type="text" placeholder="token order yg blm dibayar"/>

@endsection

@section('js')
<script>
    let token = document.getElementById('token');
    var valuegbl;
    token.addEventListener('keyup', (e) => {
        e.preventDefault();
        valuegbl = e.currentTarget.value;
        document.getElementById('pay-button').onclick = function(){
            snap.pay(`${valuegbl}`, {
                onSuccess: function(result){

                    document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);

                    $.ajax({
                        type: "POST",
                        url: 'http://api-sgs.com/payment/notification',
                        data: JSON.stringify(result),
                        contentType: "application/json; charset=utf-8",
                        success:function(result){
                            console.log(result);
                            alert('success, order has been paid');
                        },
                        error: function(err) {
                            console.log(err);
                        }
                    });
                },
                onError: function(result){

                    console.log(result)
                    //document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);

                }
            });
        };
    })
    {{--  2855072f-104c-42a8-9f59-8f44cacbdb77  --}}

{{--  };  --}}
</script>

@endsection
