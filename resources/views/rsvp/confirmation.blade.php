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
            <h1 class="display-4">You're all set!</h1>

            <p class="lead">
                We can't wait to see you at <b>{{ $event->name }}</b>.<br/>
                @isset($event['location'])
                    <b>Location: </b> {{ $event->location }}<br/>
                @endisset
                @isset($event['start_time'])
                    <b>Date: </b> {{ date("l, F jS, Y \a\\t h:i A" ,strtotime($event->start_time)) }}<br/>
                @endisset
            </p>
        </div>
    </div>
</div>
</body>
</html>

