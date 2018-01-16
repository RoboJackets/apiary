@extends('layouts/app')

@section('title')
    Error | {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Whoops!
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            Something went wrong. Please try your request again later. If you continue to receive this error,
            please contact #it-helpdesk on <a href="https://robojackets.slack.com">Slack</a>.
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-4">
            <h4>Error Details:</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <b>Code:</b> {{ $error_code }}
        </div>
        <div class="col-md-6">
            <b>Message:</b> {{ $error_message }}
        </div>
    </div>
    <div class="row">
        @if(cas()->checkAuthentication())
        <div class="col-md-3">
            <b>User:</b> {{ cas()->user() }}
        </div>
        @endif
        <div class="col-md-3">
            <b>Time:</b> {{ time() }}
        </div>
    </div>


@endsection