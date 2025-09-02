<!DOCTYPE html>
<html lang="en">
<head>
    <title>Event RSVP | MyRoboJackets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <style type="text/css">
        b {
            font-weight: bold !important;
        }
    </style>
    @include('favicon')
</head>
<body id="confirmation">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <img class="mt-2 px-5 img-fluid" src="{{ asset('img/recruiting_form_header.svg') }}">
            <hr class="my-4">
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-8" style="text-align:center">
            <h1 class="display-4">This event has ended!</h1>

            <p class="lead">
                We would have loved to see you at <b>{{ $event->name }}</b>, but unfortunately, that event has already ended.
            </p>

            <p>Click <a href="https://robojackets.org/calendar/">here</a> to see our upcoming events.</p>
        </div>
    </div>
</div>
</body>
</html>

