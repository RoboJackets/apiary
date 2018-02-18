<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts/head')
</head>

<body>
@inject('request', 'Illuminate\Http\Request')
<div class="container" id="app">
    <div class="row">
        <div class="col-12" style="text-align:center;padding-top: 15px">
            <h1>Welcome to the Shop!</h1>
        </div>
    </div>
    <attendance-kiosk></attendance-kiosk>
</div>

</body>
<script src="{{ mix('/js/app.js') }}"></script>
@if (Session::has('sweet_alert.alert'))
    <script>
        swal({!! Session::pull('sweet_alert.alert') !!});
    </script>
@endif
</html>