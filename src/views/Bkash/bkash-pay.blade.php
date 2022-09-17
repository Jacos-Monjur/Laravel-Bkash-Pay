<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    {{--Favicon--}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel BKash Payment Integration</title>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-sm mt-5">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Product</th>
                    <th scope="col">Currency</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Invoice</th>
                    <th scope="col">Transaction ID</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-right">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr @if($order->status =='Processing') class="table-success" @endif>
                        <th scope="row">{{ $order->id }}</th>
                        <td>{{ $order->product_name }}</td>
                        <td>{{ $order->currency }}</td>
                        <td>{{ $order->amount }}</td>
                        <td>{{ $order->invoice }}</td>
                        <td>{{ $order->trxID }}</td>
                        <td>{{ $order->status }}</td>
                        <td>
                        @if($order->status == 'Pending')
                        <button class="btn btn-primary" id="bKash_button">Pay with bKash</button>
                    @else
                        <h4><span class="badge badge-success">Paid</span></h4>
                    @endif
                </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

<script id="myScript"
src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>

<script>
var accessToken = '';

$(document).ready(function () {
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$.ajax({
    url: "{!! route('token') !!}",
    type: 'POST',
    contentType: 'application/json',
    success: function (data) {
        console.log('got data from token  ..');
        console.log(JSON.stringify(data));

        accessToken = JSON.stringify(data);
    },
    error: function () {
        console.log('error');

    }
});

var paymentConfig = {
    createCheckoutURL: "{!! route('createpayment') !!}",
    executeCheckoutURL: "{!! route('executepayment') !!}"
};


var paymentRequest;
paymentRequest = {amount: $('.amount').text(), intent: 'sale', invoice: $('.invoice').text()};
console.log(JSON.stringify(paymentRequest));

bKash.init({
    paymentMode: 'checkout',
    paymentRequest: paymentRequest,
    createRequest: function (request) {
        console.log('=> createRequest (request) :: ');
        console.log(request);

        $.ajax({
            url: paymentConfig.createCheckoutURL + "?amount=" + paymentRequest.amount + "&invoice=" + paymentRequest.invoice,
            type: 'GET',
            contentType: 'application/json',
            success: function (data) {
                console.log('got data from create  ..');
                console.log('data ::=>');
                console.log(JSON.stringify(data));

                var obj = JSON.parse(data);

                if (data && obj.paymentID != null) {
                    paymentID = obj.paymentID;
                    bKash.create().onSuccess(obj);
                }
                else {
                    console.log('error');
                    bKash.create().onError();
                }
            },
            error: function () {
                console.log('error');
                bKash.create().onError();
            }
        });
    },

    executeRequestOnAuthorization: function () {
        console.log('=> executeRequestOnAuthorization');
        $.ajax({
            url: paymentConfig.executeCheckoutURL + "?paymentID=" + paymentID,
            type: 'GET',
            contentType: 'application/json',
            success: function (data) {
                console.log('got data from execute  ..');
                console.log('data ::=>');
                console.log(JSON.stringify(data));

                data = JSON.parse(data);
                if (data && data.paymentID != null) {
                    alert('[SUCCESS] data : ' + JSON.stringify(data));
                    window.location.href = "{!! route('order') !!}";
                }
                else {
                    bKash.execute().onError();
                }
            },
            error: function () {
                bKash.execute().onError();
            }
        });
    }
});

console.log("Right after init ");
});

function callReconfigure(val) {
bKash.reconfigure(val);
}

function clickPayButton() {
$("#bKash_button").trigger('click');
}
</script>
</body>
</html>
