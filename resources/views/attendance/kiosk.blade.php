<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts/head')
    <style type="text/css">
        /* Vertically center all the things - http://bit.ly/2Fm2Gpe */
        html,
        body {
            width: 100%;
            height: 100%;
        }
        html {
            display: table;
        }
        body {
            display: table-cell;
            vertical-align: middle;
        }
    </style>
</head>

<body>
@inject('request', 'Illuminate\Http\Request')
<div class="container" id="app" style="margin-top: -5%">
    <div class="row">
        <div class="col-12" style="text-align:center">
            <h1 style="font-size: 4rem">Welcome to the Shop!</h1>
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